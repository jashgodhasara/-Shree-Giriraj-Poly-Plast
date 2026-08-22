import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import { api, getCachedData } from '../services/api';
import { ENDPOINTS } from '../config/api';
import { DashboardStats, PlasticPricesResponse } from '../types';
import { Colors, Shadows } from '../components/Theme';

interface Props {
  onNavigate: (screen: string) => void;
}

export const DashboardScreen: React.FC<Props> = ({ onNavigate }) => {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardStats | null>(null);
  const [polymerPrices, setPolymerPrices] = useState<PlasticPricesResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [lastSync, setLastSync] = useState<string>('Just now');

  const fetchPlasticRates = useCallback(async (isRefresh = false) => {
    try {
      const res = await api.get<PlasticPricesResponse>(ENDPOINTS.PLASTIC_PRICES + (isRefresh ? '?refresh=1' : ''));
      if (res && res.items) {
        setPolymerPrices(res);
      }
    } catch (e) {
      console.warn('Failed to load polymer prices in mobile:', e);
    }
  }, []);

  const fetchDashboard = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const [res] = await Promise.all([
        api.get<DashboardStats>(ENDPOINTS.DASHBOARD),
        fetchPlasticRates(isRefresh),
      ]);
      if (res) {
        setData(res);
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        setLastSync(time);
      }
    } catch (e) {
      console.error('Error loading dashboard:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [fetchPlasticRates]);

  // Instant Cache-First load + real-time 6s background auto-sync
  useEffect(() => {
    getCachedData<DashboardStats>(ENDPOINTS.DASHBOARD).then((cached) => {
      if (cached) {
        setData(cached);
        setLoading(false);
      }
    });
    fetchDashboard();

    const interval = setInterval(() => {
      fetchDashboard();
    }, 15000);
    return () => clearInterval(interval);
  }, [fetchDashboard]);

  const stats = data?.stats || {
    total_revenue: 0,
    invoices: 0,
    customers: 0,
    products: 0,
    suppliers: 0,
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.content}
      refreshControl={
        <RefreshControl
          refreshing={refreshing}
          onRefresh={() => fetchDashboard(true)}
          colors={[Colors.primary]}
        />
      }
    >
      {/* Live Sync Banner */}
      <View style={styles.syncBanner}>
        <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1 }}>
          <View style={styles.liveDot} />
          <Text style={styles.syncText} numberOfLines={1}>
            Synced: <Text style={{ fontWeight: '700' }}>{lastSync}</Text>
          </Text>
        </View>
        <TouchableOpacity onPress={() => fetchDashboard(true)} style={styles.refreshBtn}>
          <Text style={styles.refreshBtnText}>↻ Refresh</Text>
        </TouchableOpacity>
      </View>

      {/* User Welcome Card */}
      <View style={styles.welcomeCard}>
        <View style={styles.welcomeInfo}>
          <Text style={styles.welcomeGreeting}>Welcome back,</Text>
          <Text style={styles.welcomeName}>{user?.name || 'ERP User'}</Text>
          <View style={styles.roleBadge}>
            <Text style={styles.roleBadgeText}>
              {user?.role === 'admin' ? '🛡️ Administrator' : '👔 Staff Member'}
            </Text>
          </View>
        </View>
        <View style={styles.avatarCircle}>
          <Text style={styles.avatarText}>
            {(user?.name || 'U').charAt(0).toUpperCase()}
          </Text>
        </View>
      </View>

      {/* Quick Action Shortcuts */}
      <Text style={styles.sectionHeader}>Quick Actions</Text>
      <View style={styles.quickGrid}>
        <TouchableOpacity
          style={[styles.quickCard, { backgroundColor: '#EEF2FF' }]}
          onPress={() => onNavigate('invoices')}
        >
          <Text style={styles.quickIcon}>📄</Text>
          <Text style={[styles.quickTitle, { color: Colors.primaryDark }]}>New Sale</Text>
          <Text style={styles.quickSub}>Create Invoice</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.quickCard, { backgroundColor: '#ECFDF5' }]}
          onPress={() => onNavigate('customers')}
        >
          <Text style={styles.quickIcon}>👥</Text>
          <Text style={[styles.quickTitle, { color: '#065F46' }]}>Customers</Text>
          <Text style={styles.quickSub}>Add & Manage</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.quickCard, { backgroundColor: '#FEF3C7' }]}
          onPress={() => onNavigate('purchases')}
        >
          <Text style={styles.quickIcon}>📦</Text>
          <Text style={[styles.quickTitle, { color: '#92400E' }]}>Purchases</Text>
          <Text style={styles.quickSub}>Orders & PO</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.quickCard, { backgroundColor: '#F5F3FF' }]}
          onPress={() => onNavigate('payments')}
        >
          <Text style={styles.quickIcon}>💳</Text>
          <Text style={[styles.quickTitle, { color: '#5B21B6' }]}>Payments</Text>
          <Text style={styles.quickSub}>Record Entry</Text>
        </TouchableOpacity>
      </View>

      {/* Primary KPI Stats */}
      <Text style={styles.sectionHeader}>Overview Metrics</Text>
      {loading && !data ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginVertical: 20 }} />
      ) : (
        <View style={styles.statsGrid}>
          <View style={styles.statBox}>
            <Text style={styles.statLabel}>Total Revenue</Text>
            <Text style={[styles.statValue, { color: Colors.accent }]}>
              ₹{(stats?.total_revenue || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 })}
            </Text>
            <Text style={styles.statSub}>From {stats?.invoices || 0} invoices</Text>
          </View>

          <View style={styles.statBox}>
            <Text style={styles.statLabel}>Invoices</Text>
            <Text style={[styles.statValue, { color: Colors.primary }]}>
              {stats?.invoices || 0}
            </Text>
            <Text style={styles.statSub}>Sales recorded</Text>
          </View>

          <View style={styles.statBox}>
            <Text style={styles.statLabel}>Customers</Text>
            <Text style={[styles.statValue, { color: Colors.purple }]}>
              {stats?.customers || 0}
            </Text>
            <Text style={styles.statSub}>Active accounts</Text>
          </View>

          <View style={styles.statBox}>
            <Text style={styles.statLabel}>Products / Materials</Text>
            <Text style={[styles.statValue, { color: Colors.warning }]}>
              {(stats?.products || 0) + (stats?.suppliers || 0)}
            </Text>
            <Text style={styles.statSub}>In master catalog</Text>
          </View>
        </View>
      )}

      {/* Live Polymer Market Rates (3MinAPI) */}
      <View style={styles.sectionTitleRow}>
        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
          <Text style={styles.sectionHeader}>Live Polymer Rates</Text>
          <View style={styles.livePulseBadge}>
            <View style={styles.livePulseDot} />
            <Text style={styles.livePulseText}>LIVE</Text>
          </View>
        </View>
        <TouchableOpacity onPress={() => fetchPlasticRates(true)}>
          <Text style={styles.viewAllText}>↻ Refresh</Text>
        </TouchableOpacity>
      </View>

      {polymerPrices?.items && polymerPrices.items.length > 0 ? (
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.polymerScroll}
          contentContainerStyle={styles.polymerContainer}
        >
          {polymerPrices.items.map((item) => {
            const isUp = item.trend === 'up' || (item.change && item.change.startsWith('+') && item.change !== '+0.00');
            const isDown = item.trend === 'down' || (item.change && item.change.startsWith('-'));
            const trendColor = isUp ? '#059669' : isDown ? '#DC2626' : Colors.textMuted;
            const trendBg = isUp ? '#D1FAE5' : isDown ? '#FEE2E2' : '#F1F5F9';

            return (
              <View key={item.id} style={styles.polymerCard}>
                <View style={styles.polymerTopRow}>
                  <Text style={styles.polymerCategory} numberOfLines={1}>
                    {item.category || 'Polymer'}
                  </Text>
                  {item.change ? (
                    <View style={[styles.trendBadge, { backgroundColor: trendBg }]}>
                      <Text style={[styles.trendText, { color: trendColor }]}>
                        {item.change}
                      </Text>
                    </View>
                  ) : null}
                </View>

                <Text style={styles.polymerName} numberOfLines={2}>
                  {item.material_name}
                </Text>

                <View style={styles.polymerPriceRow}>
                  <Text style={styles.polymerCurrency}>₹</Text>
                  <Text style={styles.polymerPrice}>
                    {Number(item.current_price).toFixed(2)}
                  </Text>
                  <Text style={styles.polymerUnit}>/ {item.unit || 'Kg'}</Text>
                </View>
              </View>
            );
          })}
        </ScrollView>
      ) : null}

      {/* Recent Invoices */}
      <View style={styles.sectionTitleRow}>
        <Text style={styles.sectionHeader}>Recent Invoices</Text>
        <TouchableOpacity onPress={() => onNavigate('invoices')}>
          <Text style={styles.viewAllText}>View All →</Text>
        </TouchableOpacity>
      </View>

      {data?.recent_invoices && data.recent_invoices.length > 0 ? (
        <View style={styles.invoicesCard}>
          {data.recent_invoices.map((inv, idx) => (
            <TouchableOpacity
              key={inv.id}
              style={[
                styles.invoiceRow,
                idx === data.recent_invoices.length - 1 && { borderBottomWidth: 0 },
              ]}
              onPress={() => onNavigate('invoices')}
            >
              <View style={{ flex: 1 }}>
                <Text style={styles.invNumber}>{inv.invoice_number}</Text>
                <Text style={styles.invCustomer}>{inv.customer_name}</Text>
                <Text style={styles.invDate}>{inv.invoice_date}</Text>
              </View>
              <View style={{ alignItems: 'flex-end' }}>
                <Text style={styles.invAmount}>
                  ₹{(inv.grand_total || 0).toLocaleString('en-IN')}
                </Text>
                <View
                  style={[
                    styles.statusBadge,
                    inv.status === 'Paid'
                      ? styles.statusPaid
                      : inv.status === 'Partial'
                      ? styles.statusPartial
                      : styles.statusUnpaid,
                  ]}
                >
                  <Text
                    style={[
                      styles.statusBadgeText,
                      inv.status === 'Paid'
                        ? { color: '#065F46' }
                        : inv.status === 'Partial'
                        ? { color: '#92400E' }
                        : { color: '#991B1B' },
                    ]}
                  >
                    {inv.status}
                  </Text>
                </View>
              </View>
            </TouchableOpacity>
          ))}
        </View>
      ) : (
        <View style={styles.emptyCard}>
          <Text style={styles.emptyText}>No recent invoices recorded yet.</Text>
        </View>
      )}

      {/* Low Stock Alerts */}
      {data?.low_stock && data.low_stock.length > 0 && (
        <>
          <Text style={[styles.sectionHeader, { marginTop: 20 }]}>⚠️ Low Stock Alerts</Text>
          <View style={styles.invoicesCard}>
            {data.low_stock.map((item, i) => (
              <View
                key={item.id}
                style={[
                  styles.invoiceRow,
                  i === data.low_stock.length - 1 && { borderBottomWidth: 0 },
                ]}
              >
                <View style={{ flex: 1 }}>
                  <Text style={styles.invNumber}>{item.name}</Text>
                  <Text style={styles.invCustomer}>{item.type}</Text>
                </View>
                <View style={[styles.statusBadge, styles.statusUnpaid]}>
                  <Text style={[styles.statusBadgeText, { color: '#DC2626' }]}>
                    {item.stock_quantity} {item.unit || 'units'} left
                  </Text>
                </View>
              </View>
            ))}
          </View>
        </>
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.bg,
  },
  content: {
    padding: 16,
    paddingBottom: 40,
  },
  syncBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: Colors.border,
    marginBottom: 14,
    ...Shadows.sm,
  },
  liveDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: Colors.accent,
    marginRight: 8,
  },
  syncText: {
    fontSize: 11.5,
    color: Colors.textSecondary,
    flex: 1,
  },
  refreshBtn: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 6,
    backgroundColor: Colors.bg,
  },
  refreshBtnText: {
    fontSize: 11,
    color: Colors.primary,
    fontWeight: '600',
  },
  welcomeCard: {
    backgroundColor: Colors.cardBg,
    borderRadius: 16,
    padding: 18,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 1,
    borderColor: Colors.border,
    marginBottom: 20,
    ...Shadows.sm,
  },
  welcomeInfo: {
    flex: 1,
  },
  welcomeGreeting: {
    fontSize: 12,
    color: Colors.textMuted,
    fontWeight: '500',
  },
  welcomeName: {
    fontSize: 18,
    fontWeight: '800',
    color: Colors.text,
    marginTop: 2,
  },
  roleBadge: {
    backgroundColor: Colors.primaryLight,
    alignSelf: 'flex-start',
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 6,
    marginTop: 6,
  },
  roleBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: Colors.primary,
  },
  avatarCircle: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: Colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    ...Shadows.sm,
  },
  avatarText: {
    fontSize: 20,
    fontWeight: '800',
    color: '#FFFFFF',
  },
  sectionHeader: {
    fontSize: 15,
    fontWeight: '700',
    color: Colors.text,
    marginBottom: 12,
  },
  sectionTitleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 10,
  },
  viewAllText: {
    fontSize: 12.5,
    fontWeight: '600',
    color: Colors.primary,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 20,
  },
  quickCard: {
    flex: 1,
    minWidth: '47%',
    padding: 14,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.04)',
    ...Shadows.sm,
  },
  quickIcon: {
    fontSize: 22,
    marginBottom: 6,
  },
  quickTitle: {
    fontSize: 14,
    fontWeight: '700',
  },
  quickSub: {
    fontSize: 11,
    color: Colors.textMuted,
    marginTop: 2,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 20,
  },
  statBox: {
    flex: 1,
    minWidth: '47%',
    backgroundColor: Colors.cardBg,
    borderRadius: 14,
    padding: 14,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadows.sm,
  },
  statLabel: {
    fontSize: 11.5,
    color: Colors.textMuted,
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  statValue: {
    fontSize: 18,
    fontWeight: '800',
    marginTop: 4,
  },
  statSub: {
    fontSize: 11,
    color: Colors.textMuted,
    marginTop: 2,
  },
  invoicesCard: {
    backgroundColor: Colors.cardBg,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: Colors.border,
    overflow: 'hidden',
    ...Shadows.sm,
  },
  invoiceRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 14,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  invNumber: {
    fontSize: 13.5,
    fontWeight: '700',
    color: Colors.text,
  },
  invCustomer: {
    fontSize: 12.5,
    color: Colors.textSecondary,
    marginTop: 2,
  },
  invDate: {
    fontSize: 11,
    color: Colors.textMuted,
    marginTop: 2,
  },
  invAmount: {
    fontSize: 14,
    fontWeight: '700',
    color: Colors.text,
  },
  statusBadge: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 12,
    marginTop: 4,
    alignSelf: 'flex-end',
  },
  statusPaid: {
    backgroundColor: '#D1FAE5',
  },
  statusPartial: {
    backgroundColor: '#FEF3C7',
  },
  statusUnpaid: {
    backgroundColor: '#FEE2E2',
  },
  statusBadgeText: {
    fontSize: 10.5,
    fontWeight: '700',
  },
  emptyCard: {
    backgroundColor: Colors.cardBg,
    borderRadius: 14,
    padding: 24,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: Colors.border,
  },
  emptyText: {
    fontSize: 13,
    color: Colors.textMuted,
  },
  livePulseBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(16,185,129,0.12)',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 10,
    gap: 4,
    borderWidth: 1,
    borderColor: 'rgba(16,185,129,0.25)',
  },
  livePulseDot: {
    width: 5,
    height: 5,
    borderRadius: 3,
    backgroundColor: '#10B981',
  },
  livePulseText: {
    fontSize: 9.5,
    fontWeight: '800',
    color: '#059669',
    letterSpacing: 0.5,
  },
  polymerScroll: {
    marginBottom: 20,
    marginHorizontal: -16,
  },
  polymerContainer: {
    paddingHorizontal: 16,
    gap: 12,
  },
  polymerCard: {
    backgroundColor: '#0F172A',
    borderRadius: 14,
    padding: 12,
    width: 170,
    borderWidth: 1,
    borderColor: 'rgba(59,130,246,0.3)',
    ...Shadows.md,
  },
  polymerTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  polymerCategory: {
    fontSize: 10,
    fontWeight: '700',
    color: '#93C5FD',
    textTransform: 'uppercase',
    letterSpacing: 0.4,
    flex: 1,
  },
  trendBadge: {
    paddingHorizontal: 5,
    paddingVertical: 1,
    borderRadius: 4,
  },
  trendText: {
    fontSize: 9.5,
    fontWeight: '700',
  },
  polymerName: {
    fontSize: 12,
    fontWeight: '700',
    color: '#F8FAFC',
    marginBottom: 8,
    minHeight: 32,
  },
  polymerPriceRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
    gap: 2,
  },
  polymerCurrency: {
    fontSize: 11,
    fontWeight: '700',
    color: '#94A3B8',
  },
  polymerPrice: {
    fontSize: 16,
    fontWeight: '800',
    color: '#FFFFFF',
    letterSpacing: -0.3,
  },
  polymerUnit: {
    fontSize: 10,
    color: '#94A3B8',
    fontWeight: '500',
    marginLeft: 2,
  },
});
