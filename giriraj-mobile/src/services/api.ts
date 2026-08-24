import AsyncStorage from '@react-native-async-storage/async-storage';
import { getApiBaseUrl } from '../config/api';

const STORAGE_KEYS = {
  TOKEN: 'erp_token',
  USER:  'erp_user',
  CACHE_PREFIX: 'erp_cache_',
};

// In-memory memory caches to avoid repetitive disk I/O on every request
let inMemoryToken: string | null = null;
let inMemoryUser: any = null;
const inMemoryCache: Record<string, { data: any; timestamp: number }> = {};

// ─── Token & User Storage ──────────────────────────────────────────────────
export async function getToken(): Promise<string | null> {
  if (inMemoryToken !== null) return inMemoryToken;
  try {
    inMemoryToken = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
    return inMemoryToken;
  } catch {
    return null;
  }
}

export async function saveToken(token: string): Promise<void> {
  inMemoryToken = token;
  await AsyncStorage.setItem(STORAGE_KEYS.TOKEN, token);
}

export async function removeToken(): Promise<void> {
  inMemoryToken = null;
  inMemoryUser = null;
  await Promise.all([
    AsyncStorage.removeItem(STORAGE_KEYS.TOKEN),
    AsyncStorage.removeItem(STORAGE_KEYS.USER),
  ]);
}

export async function saveUser(user: any): Promise<void> {
  inMemoryUser = user;
  await AsyncStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(user));
}

export async function getUser(): Promise<any | null> {
  if (inMemoryUser !== null) return inMemoryUser;
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEYS.USER);
    inMemoryUser = raw ? JSON.parse(raw) : null;
    return inMemoryUser;
  } catch {
    return null;
  }
}

// ─── Cache helpers for offline support ─────────────────────────────────────
export async function setCachedData(key: string, data: any): Promise<void> {
  inMemoryCache[key] = { data, timestamp: Date.now() };
  // Non-blocking disk persist
  AsyncStorage.setItem(STORAGE_KEYS.CACHE_PREFIX + key, JSON.stringify(data)).catch(() => {});
}

export async function getCachedData<T = any>(key: string): Promise<T | null> {
  if (inMemoryCache[key]) {
    return inMemoryCache[key].data as T;
  }
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEYS.CACHE_PREFIX + key);
    if (raw) {
      const parsed = JSON.parse(raw);
      inMemoryCache[key] = { data: parsed, timestamp: Date.now() };
      return parsed as T;
    }
  } catch (e) {
    // ignore
  }
  return null;
}

let unauthorizedHandler: (() => void) | null = null;

export function registerUnauthorizedHandler(callback: () => void) {
  unauthorizedHandler = callback;
}

// ─── Core HTTP Client ──────────────────────────────────────────────────────
async function request<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const [token, baseUrl] = await Promise.all([getToken(), getApiBaseUrl()]);

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...((options.headers as Record<string, string>) || {}),
  };

  const timeoutMs = options.method && options.method !== 'GET' ? 45000 : 25000;
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

  try {
    const response = await fetch(`${baseUrl}${endpoint}`, {
      ...options,
      headers,
      signal: controller.signal,
    });
    clearTimeout(timeoutId);

    if (response.status === 401) {
      await removeToken();
      if (unauthorizedHandler) {
        unauthorizedHandler();
      }
      throw new Error('Session expired. Please login again.');
    }

    let data: any = null;
    try {
      data = await response.json();
    } catch {
      // response is not JSON
    }

    if (!response.ok) {
      let msg = data?.message || data?.error;
      if (!msg && data?.errors && typeof data.errors === 'object') {
        msg = Object.values(data.errors).flat().join('\n');
      }
      msg = msg || `Request failed (${response.status})`;
      throw new Error(msg);
    }

    // Cache successful GET responses
    if (!options.method || options.method === 'GET') {
      setCachedData(endpoint, data);
    }

    return data as T;
  } catch (error: any) {
    clearTimeout(timeoutId);

    if (error?.name === 'AbortError' || error?.message?.toLowerCase().includes('abort')) {
      throw new Error('Server connection timed out. Please check your internet and try again.');
    }

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
