import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  Alert,
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import { Colors, Shadows } from '../components/Theme';

import { DashboardScreen } from './DashboardScreen';
import { InvoicesScreen } from './InvoicesScreen';
import { CustomersScreen } from './CustomersScreen';
import { ProductsScreen } from './ProductsScreen';
import { PurchasesScreen } from './PurchasesScreen';
import { PaymentsScreen } from './PaymentsScreen';
import { ReportsScreen } from './ReportsScreen';

type Tab = 'dashboard' | 'invoices' | 'customers' | 'products' | 'purchases' | 'payments' | 'reports';

export const MainApp: React.FC = () => {
  const { user, logout } = useAuth();
  const [currentTab, setCurrentTab] = useState<Tab>('dashboard');

  const handleLogout = () => {
    Alert.alert('Sign Out', 'Are you sure you want to log out of the ERP?', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Log Out', style: 'destructive', onPress: logout },
    ]);
  };

  const renderScreen = () => {
    switch (currentTab) {
      case 'dashboard':
        return <DashboardScreen onNavigate={(t) => setCurrentTab(t as Tab)} />;
      case 'invoices':
        return <InvoicesScreen />;
      case 'customers':
        return <CustomersScreen />;
      case 'products':
        return <ProductsScreen />;
      case 'purchases':
        return <PurchasesScreen />;
      case 'payments':
        return <PaymentsScreen />;
      case 'reports':
        return <ReportsScreen />;
      default:
        return <DashboardScreen onNavigate={(t) => setCurrentTab(t as Tab)} />;
    }
  };

  const getTitle = () => {
    switch (currentTab) {
      case 'dashboard': return 'Dashboard';
      case 'invoices': return 'Sales & Invoices';
      case 'customers': return 'Customer Directory';
      case 'products': return 'Products & Stock';
      case 'purchases': return 'Purchase Orders';
      case 'payments': return 'Payments';
      case 'reports': return 'Business Analytics';
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

      {/* Top Header */}
      <View style={styles.header}>
        <View style={styles.headerLeft}>
          <Text style={styles.brandTitle}>SHREE GIRIRAJ</Text>
          <Text style={styles.headerSub}>{getTitle()}</Text>
        </View>

        <View style={styles.headerRight}>
          <View style={styles.userChip}>
            <View style={styles.userDot} />
            <Text style={styles.userName}>{user?.name || 'User'}</Text>
          </View>

          <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
            <Text style={styles.logoutText}>🚪</Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Screen Body */}
      <View style={styles.body}>{renderScreen()}</View>

      {/* Bottom Tab Bar */}
      <View style={styles.tabBar}>
        <TouchableOpacity
          style={styles.tabItem}
          onPress={() => setCurrentTab('dashboard')}
        >
          <Text style={styles.tabIcon}>📊</Text>
          <Text
            style={[
              styles.tabLabel,
              currentTab === 'dashboard' && styles.tabLabelActive,
            ]}
          >
            Dashboard
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.tabItem}
          onPress={() => setCurrentTab('invoices')}
        >
          <Text style={styles.tabIcon}>📄</Text>
          <Text
            style={[
              styles.tabLabel,
              currentTab === 'invoices' && styles.tabLabelActive,
            ]}
          >
            Sales
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.tabItem}
          onPress={() => setCurrentTab('customers')}
        >
          <Text style={styles.tabIcon}>👥</Text>
          <Text
            style={[
              styles.tabLabel,
              currentTab === 'customers' && styles.tabLabelActive,
            ]}
          >
            Customers
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.tabItem}
          onPress={() => setCurrentTab('products')}
        >
          <Text style={styles.tabIcon}>📦</Text>
          <Text
            style={[
              styles.tabLabel,
              currentTab === 'products' && styles.tabLabelActive,
            ]}
          >
            Stock
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.tabItem}
          onPress={() => setCurrentTab('purchases')}
        >
          <Text style={styles.tabIcon}>🛒</Text>
          <Text
            style={[
              styles.tabLabel,
              currentTab === 'purchases' && styles.tabLabelActive,
            ]}
          >
            PO
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.tabItem}
          onPress={() => setCurrentTab('reports')}
        >
          <Text style={styles.tabIcon}>📈</Text>
          <Text
            style={[
              styles.tabLabel,
              currentTab === 'reports' && styles.tabLabelActive,
            ]}
          >
            Reports
          </Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    ...Shadows.sm,
  },
  headerLeft: {
    flex: 1,
  },
  brandTitle: {
    fontSize: 14,
    fontWeight: '900',
    color: Colors.primary,
    letterSpacing: 0.8,
  },
  headerSub: {
    fontSize: 12,
    color: Colors.textSecondary,
    fontWeight: '600',
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  userChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.bg,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  userDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: Colors.accent,
    marginRight: 6,
  },
  userName: {
    fontSize: 11.5,
    fontWeight: '700',
    color: Colors.text,
  },
  logoutBtn: {
    padding: 6,
    borderRadius: 8,
    backgroundColor: Colors.bg,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  logoutText: {
    fontSize: 14,
  },
  body: {
    flex: 1,
    backgroundColor: Colors.bg,
  },
  tabBar: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingVertical: 8,
    paddingHorizontal: 4,
    ...Shadows.md,
  },
  tabItem: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  tabIcon: {
    fontSize: 18,
  },
  tabLabel: {
    fontSize: 10,
    fontWeight: '600',
    color: Colors.textMuted,
    marginTop: 2,
  },
  tabLabelActive: {
    color: Colors.primary,
    fontWeight: '800',
  },
});
