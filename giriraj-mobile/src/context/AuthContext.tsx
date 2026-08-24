import React, { createContext, useState, useEffect, useContext } from 'react';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { User } from '../types';
import { getToken, getUser, saveToken, saveUser, removeToken, api, registerUnauthorizedHandler } from '../services/api';
import { ENDPOINTS, DEFAULT_API_BASE_URL, getApiBaseUrl, setApiBaseUrl } from '../config/api';

interface AuthContextType {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  serverUrl: string;
  login: (email: string, pass: string) => Promise<{ success: boolean; message?: string }>;
  logout: () => Promise<void>;
  updateServerUrl: (url: string) => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType>({} as AuthContextType);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [serverUrl, setServerUrlState] = useState<string>(DEFAULT_API_BASE_URL);

  useEffect(() => {
    registerUnauthorizedHandler(() => {
      setToken(null);
      setUser(null);
    });
    bootstrapAuth();
  }, []);

  const bootstrapAuth = async () => {
    try {
      const url = await getApiBaseUrl();
      setServerUrlState(url);

      const savedToken = await getToken();
      const savedUser = await getUser();

      if (savedToken && savedUser) {
        setToken(savedToken);
        setUser(savedUser);
      }
    } catch (e) {
      console.error('Failed to load stored auth state:', e);
    } finally {
      setIsLoading(false);
    }
  };

  const getDeviceId = async (): Promise<string> => {
    try {
      let id = await AsyncStorage.getItem('erp_device_id');
      if (!id) {
        id = Math.random().toString(36).substring(2, 8);
        await AsyncStorage.setItem('erp_device_id', id);
      }
      return id;
    } catch {
      return 'mobile';
    }
  };

  const login = async (email: string, pass: string): Promise<{ success: boolean; message?: string }> => {
    try {
      const deviceId = await getDeviceId();
      const deviceLabel = Platform.OS === 'ios' ? `iOS Device (${deviceId})` : `Android Phone (${deviceId})`;

      const response = await api.post(ENDPOINTS.LOGIN, {
        email: email.trim(),
        password: pass,
        device_name: deviceLabel,
      });

      if (response.success && response.token) {
        await saveToken(response.token);
        await saveUser(response.user);
        setToken(response.token);
        setUser(response.user);
        return { success: true };
      }
      return { success: false, message: response.message || 'Login failed' };
    } catch (error: any) {
      return { success: false, message: error.message || 'Network error connecting to server' };
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await api.post(ENDPOINTS.LOGOUT, {});
      }
    } catch (e) {
      // ignore network errors on logout
    } finally {
      await removeToken();
      setToken(null);
      setUser(null);
    }
  };

  const updateServerUrl = async (url: string) => {
    await setApiBaseUrl(url);
    setServerUrlState(url);
  };

  const refreshUser = async () => {
    if (!token) return;
    try {
      const u = await api.get(ENDPOINTS.ME);
      if (u) {
        setUser(u);
        await saveUser(u);
      }
    } catch {
      // silent
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isLoading,
        serverUrl,
        login,
        logout,
        updateServerUrl,
        refreshUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
