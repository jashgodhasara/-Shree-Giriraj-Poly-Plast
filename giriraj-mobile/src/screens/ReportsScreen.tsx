import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { api } from '../services/api';
import { ENDPOINTS } from '../config/api';
import { Colors, Shadows } from '../components/Theme';

export const ReportsScreen: React.FC = () => {
  const [period, setPeriod] = useState<'today' | 'week' | 'month' | 'year' | 'all'>('month');
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchReports = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const res = await api.get(`${ENDPOINTS.REPORTS}?period=${period}`);
      setData(res);
    } catch (e) {
      console.error('Failed to load reports:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [period]);

  useEffect(() => {
    fetchReports();
  }, [fetchReports]);

  const sales = data?.sales;
  const purchases = data?.purchases;

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.content}
      refreshControl={
        <RefreshControl
          refreshing={refreshing}
          onRefresh={() => fetchReports(true)}
          colors={[Colors.primary]}
        />
      }
    >
      {/* Period Filter */}
      <View style={styles.periodRow}>
        {[
          { key: 'today', label: 'Today' },
          { key: 'week', label: 'This Week' },
          { key: 'month', label: 'This Month' },
          { key: 'year', label: 'This Year' },
          { key: 'all', label: 'All Time' },
        ].map((p) => (
          <TouchableOpacity
            key={p.key}
            style={[styles.periodBtn, period === p.key && styles.periodBtnActive]}
            onPress={() => setPeriod(p.key as any)}
          >
            <Text
              style={[
                styles.periodText,
                period === p.key && styles.periodTextActive,
              ]}
            >
              {p.label}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {loading ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 40 }} />
      ) : (
        <>
          {/* Sales Summary Card */}
          <View style={styles.card}>
            <Text style={styles.cardHeader}>📊 Sales Summary ({period.toUpperCase()})</Text>

            <View style={styles.metricRow}>
              <View style={styles.metricItem}>
                <Text style={styles.metricLabel}>Total Revenue</Text>
                <Text style={[styles.metricVal, { color: Colors.primaryDark }]}>
                  ₹{(sales?.total_revenue || 0).toLocaleString('en-IN')}
                </Text>
              </View>

              <View style={styles.metricItem}>
                <Text style={styles.metricLabel}>Collected</Text>
                <Text style={[styles.metricVal, { color: Colors.accent }]}>
                  ₹{(sales?.total_collected || 0).toLocaleString('en-IN')}
                </Text>
              </View>
            </View>

            <View style={styles.metricRow}>
              <View style={styles.metricItem}>
                <Text style={styles.metricLabel}>Pending Balance</Text>
                <Text style={[styles.metricVal, { color: Colors.danger }]}>
                  ₹{(sales?.total_pending || 0).toLocaleString('en-IN')}
                </Text>
              </View>

              <View style={styles.metricItem}>
                <Text style={styles.metricLabel}>Invoices Count</Text>
                <Text style={[styles.metricVal, { color: Colors.purple }]}>
                  {sales?.total_invoices || 0}
                </Text>
              </View>
            </View>
          </View>

          {/* Purchases Summary Card */}
          <View style={styles.card}>
            <Text style={styles.cardHeader}>📦 Purchases Summary</Text>
            <View style={styles.metricRow}>
              <View style={styles.metricItem}>
                <Text style={styles.metricLabel}>Total Orders</Text>
                <Text style={[styles.metricVal, { color: Colors.text }]}>
                  {purchases?.total_orders || 0}
                </Text>
              </View>
              <View style={styles.metricItem}>
                <Text style={styles.metricLabel}>Total PO Value</Text>
                <Text style={[styles.metricVal, { color: Colors.warning }]}>
                  ₹{(purchases?.total_value || 0).toLocaleString('en-IN')}
                </Text>
              </View>
            </View>
          </View>

          {/* Top Customers */}
          {data?.top_customers && data.top_customers.length > 0 && (
            <View style={styles.card}>
              <Text style={styles.cardHeader}>👑 Top Customers by Sales</Text>
              {data.top_customers.map((c: any, i: number) => (
                <View key={i} style={styles.rankRow}>
                  <Text style={styles.rankIndex}>#{i + 1}</Text>
                  <Text style={styles.rankName}>{c.customer_name || 'Customer'}</Text>
                  <Text style={styles.rankVal}>₹{(c.total || 0).toLocaleString('en-IN')}</Text>
                </View>
              ))}
            </View>
          )}

          {/* Payment Modes */}
          {data?.payment_modes && data.payment_modes.length > 0 && (
            <View style={styles.card}>
              <Text style={styles.cardHeader}>💳 Payment Mode Distribution</Text>
              {data.payment_modes.map((pm: any, i: number) => (
                <View key={i} style={styles.rankRow}>
                  <Text style={styles.rankName}>🏷️ {pm.mode}</Text>
                  <Text style={styles.rankSub}>{pm.count} txns</Text>
                  <Text style={styles.rankVal}>₹{(pm.total || 0).toLocaleString('en-IN')}</Text>
                </View>
              ))}
            </View>
          )}
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
    padding: 14,
    paddingBottom: 40,
  },
  periodRow: {
    flexDirection: 'row',
    gap: 6,
    marginBottom: 14,
    flexWrap: 'wrap',
  },
  periodBtn: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: Colors.border,
  },
  periodBtnActive: {
    backgroundColor: Colors.primary,
    borderColor: Colors.primary,
  },
  periodText: {
    fontSize: 11.5,
    fontWeight: '700',
    color: Colors.textSecondary,
  },
  periodTextActive: {
    color: '#FFFFFF',
  },
  card: {
    backgroundColor: Colors.cardBg,
    borderRadius: 14,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadows.sm,
  },
  cardHeader: {
    fontSize: 14.5,
    fontWeight: '800',
    color: Colors.text,
    marginBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    paddingBottom: 8,
  },
  metricRow: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 12,
  },
  metricItem: {
    flex: 1,
    backgroundColor: Colors.bg,
    padding: 12,
    borderRadius: 10,
  },
  metricLabel: {
    fontSize: 11,
    fontWeight: '600',
    color: Colors.textMuted,
    textTransform: 'uppercase',
  },
  metricVal: {
    fontSize: 16,
    fontWeight: '800',
    marginTop: 4,
  },
  rankRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  rankIndex: {
    fontSize: 12,
    fontWeight: '800',
    color: Colors.primary,
    width: 24,
  },
  rankName: {
    fontSize: 13,
    fontWeight: '600',
    color: Colors.text,
    flex: 1,
  },
  rankSub: {
    fontSize: 11.5,
    color: Colors.textMuted,
    marginRight: 10,
  },
  rankVal: {
    fontSize: 13.5,
    fontWeight: '800',
    color: Colors.primaryDark,
  },
});
