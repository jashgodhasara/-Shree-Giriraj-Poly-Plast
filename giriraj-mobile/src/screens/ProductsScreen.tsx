import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { api } from '../services/api';
import { ENDPOINTS } from '../config/api';
import { Product, Material } from '../types';
import { Colors, Shadows } from '../components/Theme';

export const ProductsScreen: React.FC = () => {
  const [tab, setTab] = useState<'PRODUCTS' | 'MATERIALS'>('PRODUCTS');
  const [products, setProducts] = useState<Product[]>([]);
  const [materials, setMaterials] = useState<Material[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');

  const fetchData = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const [pRes, mRes] = await Promise.all([
        api.get<Product[]>(ENDPOINTS.PRODUCTS),
        api.get<Material[]>(ENDPOINTS.MATERIALS),
      ]);
      setProducts(pRes || []);
      setMaterials(mRes || []);
    } catch (e) {
      console.error('Failed to load products/materials:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchData();
    const timer = setInterval(() => {
      fetchData();
    }, 5000);
    return () => clearInterval(timer);
  }, [fetchData]);

  const filteredProducts = products.filter(
    (p) =>
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      (p.hsn_code && p.hsn_code.includes(search))
  );

  const filteredMaterials = materials.filter(
    (m) =>
      m.name.toLowerCase().includes(search.toLowerCase()) ||
      m.type.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <View style={styles.container}>
      {/* Search Bar */}
      <View style={styles.topBar}>
        <TextInput
          style={styles.searchInput}
          placeholder={tab === 'PRODUCTS' ? 'Search finished products...' : 'Search raw materials & stock...'}
          placeholderTextColor={Colors.textMuted}
          value={search}
          onChangeText={setSearch}
        />
      </View>

      {/* Tabs */}
      <View style={styles.tabRow}>
        <TouchableOpacity
          style={[styles.tabBtn, tab === 'PRODUCTS' && styles.tabBtnActive]}
          onPress={() => setTab('PRODUCTS')}
        >
          <Text style={[styles.tabBtnText, tab === 'PRODUCTS' && styles.tabBtnTextActive]}>
            📦 Finished Products ({products.length})
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tabBtn, tab === 'MATERIALS' && styles.tabBtnActive]}
          onPress={() => setTab('MATERIALS')}
        >
          <Text style={[styles.tabBtnText, tab === 'MATERIALS' && styles.tabBtnTextActive]}>
            🧱 Raw Material Stock ({materials.length})
          </Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 40 }} />
      ) : tab === 'PRODUCTS' ? (
        <FlatList
          data={filteredProducts}
          keyExtractor={(item) => item.id.toString()}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => fetchData(true)}
              colors={[Colors.primary]}
            />
          }
          contentContainerStyle={{ padding: 14, paddingBottom: 50 }}
          ListEmptyComponent={
            <View style={styles.emptyBox}>
              <Text style={styles.emptyText}>No products found.</Text>
            </View>
          }
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.cardHeader}>
                <Text style={styles.cardTitle}>{item.name}</Text>
                <Text style={styles.cardPrice}>₹{item.price.toLocaleString('en-IN')}</Text>
              </View>

              {item.description ? (
                <Text style={styles.cardDesc}>{item.description}</Text>
              ) : null}

              <View style={styles.badgeRow}>
                {item.hsn_code ? (
                  <View style={styles.badge}>
                    <Text style={styles.badgeText}>HSN: {item.hsn_code}</Text>
                  </View>
                ) : null}
                <View style={[styles.badge, { backgroundColor: Colors.primaryLight }]}>
                  <Text style={[styles.badgeText, { color: Colors.primary }]}>
                    GST: {item.gst_rate}%
                  </Text>
                </View>
              </View>
            </View>
          )}
        />
      ) : (
        <FlatList
          data={filteredMaterials}
          keyExtractor={(item) => item.id.toString()}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => fetchData(true)}
              colors={[Colors.primary]}
            />
          }
          contentContainerStyle={{ padding: 14, paddingBottom: 50 }}
          ListEmptyComponent={
            <View style={styles.emptyBox}>
              <Text style={styles.emptyText}>No raw materials found.</Text>
            </View>
          }
          renderItem={({ item }) => {
            const isLowStock = item.stock_quantity < 10;
            return (
              <View style={styles.card}>
                <View style={styles.cardHeader}>
                  <Text style={styles.cardTitle}>{item.name}</Text>
                  <View
                    style={[
                      styles.stockBadge,
                      isLowStock ? styles.stockLow : styles.stockNormal,
                    ]}
                  >
                    <Text
                      style={[
                        styles.stockBadgeText,
                        isLowStock ? { color: '#991B1B' } : { color: '#065F46' },
                      ]}
                    >
                      {item.stock_quantity} {item.unit || 'units'}
                    </Text>
                  </View>
                </View>

                <View style={styles.badgeRow}>
                  <View style={styles.badge}>
                    <Text style={styles.badgeText}>🏷️ {item.type}</Text>
                  </View>
                  {item.grade_variation ? (
                    <View style={styles.badge}>
                      <Text style={styles.badgeText}>Grade: {item.grade_variation}</Text>
                    </View>
                  ) : null}
                  {item.size ? (
                    <View style={styles.badge}>
                      <Text style={styles.badgeText}>Size: {item.size}</Text>
                    </View>
                  ) : null}
                </View>
              </View>
            );
          }}
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
  topBar: {
    padding: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  searchInput: {
    backgroundColor: Colors.bg,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    fontSize: 13,
    color: Colors.text,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  tabRow: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    paddingHorizontal: 12,
    paddingVertical: 6,
    gap: 8,
  },
  tabBtn: {
    flex: 1,
    paddingVertical: 8,
    alignItems: 'center',
    borderRadius: 8,
    backgroundColor: Colors.bg,
  },
  tabBtnActive: {
    backgroundColor: Colors.primaryLight,
  },
  tabBtnText: {
    fontSize: 11.5,
    fontWeight: '700',
    color: Colors.textSecondary,
  },
  tabBtnTextActive: {
    color: Colors.primary,
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
  },
  cardTitle: {
    fontSize: 14.5,
    fontWeight: '800',
    color: Colors.text,
    flex: 1,
  },
  cardPrice: {
    fontSize: 15,
    fontWeight: '800',
    color: Colors.primaryDark,
    marginLeft: 8,
  },
  cardDesc: {
    fontSize: 12,
    color: Colors.textSecondary,
    marginTop: 4,
  },
  badgeRow: {
    flexDirection: 'row',
    gap: 6,
    marginTop: 10,
    flexWrap: 'wrap',
  },
  badge: {
    backgroundColor: '#F1F5F9',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  badgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: Colors.textSecondary,
  },
  stockBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  stockNormal: {
    backgroundColor: '#D1FAE5',
  },
  stockLow: {
    backgroundColor: '#FEE2E2',
  },
  stockBadgeText: {
    fontSize: 11.5,
    fontWeight: '800',
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
