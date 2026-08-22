import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  RefreshControl,
} from 'react-native';
import { api, getCachedData } from '../services/api';
import { ENDPOINTS } from '../config/api';
import { PurchaseOrder } from '../types';
import { Colors, Shadows } from '../components/Theme';

export const PurchasesScreen: React.FC = () => {
  const [orders, setOrders] = useState<PurchaseOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchPurchases = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const res = await api.get<PurchaseOrder[]>(ENDPOINTS.PURCHASES);
      setOrders(res || []);
    } catch (e) {
      console.error('Failed to load purchase orders:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    getCachedData<PurchaseOrder[]>(ENDPOINTS.PURCHASES).then((cached) => {
      if (cached && cached.length > 0) {
        setOrders(cached);
        setLoading(false);
      }
    });
    fetchPurchases();

    const interval = setInterval(() => {
      fetchPurchases();
    }, 6000);
    return () => clearInterval(interval);
  }, [fetchPurchases]);

  const handleMarkReceived = async (po: PurchaseOrder) => {
    Alert.alert(
      'Confirm Receipt',
      `Mark Purchase Order ${po.po_number} as Received?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Mark Received',
          onPress: async () => {
            try {
              await api.post(`${ENDPOINTS.PURCHASES}/${po.id}/receive`, {});
              Alert.alert('Success', 'Status updated to Received.');
              fetchPurchases();
            } catch (e: any) {
              Alert.alert('Error', e.message || 'Failed to update status.');
            }
          },
        },
      ]
    );
  };

  return (
    <View style={styles.container}>
      {loading ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          data={orders}
          keyExtractor={(item) => item.id.toString()}
          initialNumToRender={10}
          maxToRenderPerBatch={10}
          windowSize={5}
          removeClippedSubviews={true}
          keyboardShouldPersistTaps="handled"
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => fetchPurchases(true)}
              colors={[Colors.primary]}
            />
          }
          contentContainerStyle={{ padding: 14, paddingBottom: 50 }}
          ListEmptyComponent={
            <View style={styles.emptyBox}>
              <Text style={styles.emptyText}>No purchase orders found.</Text>
            </View>
          }
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.cardHeader}>
                <Text style={styles.cardTitle}>{item.po_number}</Text>
                <View
                  style={[
                    styles.badge,
                    item.status === 'Received' ? styles.badgeReceived : styles.badgePending,
                  ]}
                >
                  <Text
                    style={[
                      styles.badgeText,
                      item.status === 'Received' ? { color: '#065F46' } : { color: '#92400E' },
                    ]}
                  >
                    {item.status || 'Pending'}
                  </Text>
                </View>
              </View>

              <Text style={styles.supplierText}>
                🏢 Supplier: {item.supplier_name || item.supplier?.name || 'N/A'}
              </Text>

              <View style={styles.footerRow}>
                <Text style={styles.dateText}>📅 {item.po_date}</Text>
                <Text style={styles.totalText}>
                  ₹{(item.grand_total || 0).toLocaleString('en-IN')}
                </Text>
              </View>

              {item.status !== 'Received' && (
                <TouchableOpacity
                  style={styles.receiveBtn}
                  onPress={() => handleMarkReceived(item)}
                >
                  <Text style={styles.receiveBtnText}>✓ Mark as Received</Text>
                </TouchableOpacity>
              )}
            </View>
          )}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.bg,
  },
  card: {
    backgroundColor: Colors.cardBg,
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadows.sm,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  cardTitle: {
    fontSize: 14.5,
    fontWeight: '800',
    color: Colors.text,
  },
  supplierText: {
    fontSize: 13,
    color: Colors.textSecondary,
    marginBottom: 10,
  },
  footerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingTop: 8,
  },
  dateText: {
    fontSize: 11.5,
    color: Colors.textMuted,
  },
  totalText: {
    fontSize: 15,
    fontWeight: '800',
    color: Colors.primaryDark,
  },
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 10,
  },
  badgeReceived: {
    backgroundColor: '#D1FAE5',
  },
  badgePending: {
    backgroundColor: '#FEF3C7',
  },
  badgeText: {
    fontSize: 10.5,
    fontWeight: '700',
  },
  receiveBtn: {
    marginTop: 10,
    backgroundColor: Colors.accentLight,
    borderWidth: 1,
    borderColor: '#A7F3D0',
    paddingVertical: 7,
    borderRadius: 8,
    alignItems: 'center',
  },
  receiveBtnText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#065F46',
  },
  emptyBox: {
    alignItems: 'center',
    marginTop: 50,
  },
  emptyText: {
    color: Colors.textMuted,
    fontSize: 13,
  },
});
