import AsyncStorage from '@react-native-async-storage/async-storage';

export const DEFAULT_API_BASE_URL = 'http://192.168.1.13:8000/api';
const STORAGE_KEY_BASE_URL = 'erp_custom_base_url';

export async function getApiBaseUrl(): Promise<string> {
  try {
    const custom = await AsyncStorage.getItem(STORAGE_KEY_BASE_URL);
    if (custom && custom.trim().length > 0) {
      return custom.trim().replace(/\/+$/, '');
    }
  } catch (e) {
    // fallback
  }
  return DEFAULT_API_BASE_URL;
}

export async function setApiBaseUrl(url: string): Promise<void> {
  const formatted = url.trim().replace(/\/+$/, '');
  await AsyncStorage.setItem(STORAGE_KEY_BASE_URL, formatted);
}

export const ENDPOINTS = {
  // Auth
  LOGIN:    '/auth/login',
  LOGOUT:   '/auth/logout',
  ME:       '/auth/me',

  // Data
  DASHBOARD:   '/dashboard',
  REPORTS:     '/reports',
  CUSTOMERS:   '/customers',
  PRODUCTS:    '/products',
  SUPPLIERS:   '/suppliers',
  MATERIALS:   '/materials',
  INVOICES:    '/invoices',
  PAYMENTS:    '/payments',
  PURCHASES:   '/purchase-orders',
  PRODUCTION:  '/production',
  TRANSACTIONS:'/material-transactions',
  LEDGER:      '/ledger',
  INVESTORS:   '/investors',
  JOB_WORKS:   '/job-works',
} as const;
