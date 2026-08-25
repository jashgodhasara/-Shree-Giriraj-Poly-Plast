import AsyncStorage from '@react-native-async-storage/async-storage';

export const DEFAULT_API_BASE_URL = 'https://shreegiriraj-erp.onrender.com/api';
const STORAGE_KEY_BASE_URL = 'erp_custom_base_url';

export async function getApiBaseUrl(): Promise<string> {
  try {
    const custom = await AsyncStorage.getItem(STORAGE_KEY_BASE_URL);
    if (custom && custom.trim().length > 0) {
      const trimmed = custom.trim().replace(/\/+$/, '');
      // Ignore obsolete local dev IPs so the mobile app always works on mobile data (4G/5G) anywhere in the world
      if (
        trimmed.includes('192.168.') ||
        trimmed.includes('10.0.2.2') ||
        trimmed.includes('127.0.0.1') ||
        trimmed.includes('localhost')
      ) {
        await AsyncStorage.removeItem(STORAGE_KEY_BASE_URL);
        return DEFAULT_API_BASE_URL;
      }
      return trimmed;
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
  PLASTIC_PRICES: '/plastic-prices',
  INVENTORY_DASHBOARD: '/inventory/dashboard',
  INVENTORY_LEDGER:    '/inventory/ledger',
  INVENTORY_LOW_STOCK: '/inventory/low-stock',
  INVENTORY_VALUATION: '/inventory/valuation',
} as const;
