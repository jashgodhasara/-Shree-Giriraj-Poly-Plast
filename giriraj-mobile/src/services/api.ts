import AsyncStorage from '@react-native-async-storage/async-storage';
import { getApiBaseUrl } from '../config/api';

const STORAGE_KEYS = {
  TOKEN: 'erp_token',
  USER:  'erp_user',
  CACHE_PREFIX: 'erp_cache_',
};

// ─── Token & User Storage ──────────────────────────────────────────────────
export async function getToken(): Promise<string | null> {
  try {
    return await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
  } catch {
    return null;
  }
}

export async function saveToken(token: string): Promise<void> {
  await AsyncStorage.setItem(STORAGE_KEYS.TOKEN, token);
}

export async function removeToken(): Promise<void> {
  await AsyncStorage.removeItem(STORAGE_KEYS.TOKEN);
  await AsyncStorage.removeItem(STORAGE_KEYS.USER);
}

export async function saveUser(user: any): Promise<void> {
  await AsyncStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(user));
}

export async function getUser(): Promise<any | null> {
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEYS.USER);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

// ─── Cache helpers for offline support ─────────────────────────────────────
export async function setCachedData(key: string, data: any): Promise<void> {
  try {
    await AsyncStorage.setItem(STORAGE_KEYS.CACHE_PREFIX + key, JSON.stringify(data));
  } catch (e) {
    // ignore
  }
}

export async function getCachedData<T = any>(key: string): Promise<T | null> {
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEYS.CACHE_PREFIX + key);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

// ─── Core HTTP Client ──────────────────────────────────────────────────────
async function request<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = await getToken();
  const baseUrl = await getApiBaseUrl();

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...((options.headers as Record<string, string>) || {}),
  };

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 15000); // 15s timeout

  try {
    const response = await fetch(`${baseUrl}${endpoint}`, {
      ...options,
      headers,
      signal: controller.signal,
    });
    clearTimeout(timeoutId);

    if (response.status === 401) {
      // Clear token on 401 unauthorized
      await removeToken();
      throw new Error('Session expired. Please login again.');
    }

    const data = await response.json();

    if (!response.ok) {
      const msg = data?.message || data?.error || `Request failed (${response.status})`;
      throw new Error(msg);
    }

    // Cache successful GET responses for offline mode
    if (!options.method || options.method === 'GET') {
      setCachedData(endpoint, data);
    }

    return data as T;
  } catch (error: any) {
    clearTimeout(timeoutId);
    // If network error and it's a GET request, check cache
    if ((!options.method || options.method === 'GET') && error?.message !== 'Session expired. Please login again.') {
      const cached = await getCachedData<T>(endpoint);
      if (cached) {
        return cached;
      }
    }
    throw error;
  }
}

// ─── HTTP Methods ──────────────────────────────────────────────────────────
export const api = {
  get:    <T = any>(endpoint: string) =>
    request<T>(endpoint, { method: 'GET' }),

  post:   <T = any>(endpoint: string, body: any) =>
    request<T>(endpoint, { method: 'POST', body: JSON.stringify(body) }),

  put:    <T = any>(endpoint: string, body: any) =>
    request<T>(endpoint, { method: 'PUT', body: JSON.stringify(body) }),

  delete: <T = any>(endpoint: string) =>
    request<T>(endpoint, { method: 'DELETE' }),
};
