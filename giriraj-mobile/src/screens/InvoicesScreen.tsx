import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  TextInput,
  Modal,
  ActivityIndicator,
  Alert,
  ScrollView,
  RefreshControl,
} from 'react-native';
import { api } from '../services/api';
import { ENDPOINTS } from '../config/api';
import { Invoice, Customer, Product } from '../types';
import { Colors, Shadows } from '../components/Theme';

interface Props {
  onBack?: () => void;
}

export const InvoicesScreen: React.FC<Props> = () => {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [filterStatus, setFilterStatus] = useState<string>('ALL');

  // Create Modal
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [selectedCustomer, setSelectedCustomer] = useState<number | null>(null);
  const [invoiceItems, setInvoiceItems] = useState<
    Array<{ product_id: number; quantity: string }>
  >([{ product_id: 0, quantity: '1' }]);
  const [submitting, setSubmitting] = useState(false);

  // Detail Modal
  const [detailInvoice, setDetailInvoice] = useState<Invoice | null>(null);
  const [loadingDetail, setLoadingDetail] = useState(false);

  const loadData = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const [invRes, custRes, prodRes] = await Promise.all([
        api.get<Invoice[]>(ENDPOINTS.INVOICES),
        api.get<Customer[]>(ENDPOINTS.CUSTOMERS),
        api.get<Product[]>(ENDPOINTS.PRODUCTS),
      ]);
      setInvoices(invRes || []);
      setCustomers(custRes || []);
      setProducts(prodRes || []);
    } catch (e: any) {
      console.error('Failed to load invoices:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadData();
    const timer = setInterval(() => {
      loadData();
    }, 5000);
    return () => clearInterval(timer);
  }, [loadData]);

  const handleOpenDetail = async (id: number) => {
    setLoadingDetail(true);
    try {
      const res = await api.get<Invoice>(`${ENDPOINTS.INVOICES}/${id}`);
      setDetailInvoice(res);
    } catch (e: any) {
      Alert.alert('Error', e.message || 'Could not load invoice details.');
    } finally {
      setLoadingDetail(false);
    }
  };

  const handleAddItemRow = () => {
    setInvoiceItems([...invoiceItems, { product_id: 0, quantity: '1' }]);
  };

  const handleRemoveItemRow = (index: number) => {
    if (invoiceItems.length === 1) return;
    setInvoiceItems(invoiceItems.filter((_, i) => i !== index));
  };

  const handleCreateInvoice = async () => {
    if (!selectedCustomer) {
      Alert.alert('Validation', 'Please select a customer.');
      return;
    }

    const validItems = invoiceItems.filter(
      (item) => item.product_id > 0 && parseInt(item.quantity, 10) > 0
    );

    if (validItems.length === 0) {
      Alert.alert('Validation', 'Please add at least one product with quantity.');
      return;
    }

    setSubmitting(true);
    try {
      const payload = {
        customer_id: selectedCustomer,
        items: validItems.map((it) => ({
          product_id: it.product_id,
          quantity: parseInt(it.quantity, 10),
        })),
      };

      const res = await api.post(ENDPOINTS.INVOICES, payload);
      if (res.success || res.invoice_id || res.id) {
        Alert.alert('Success', `Invoice ${res.invoice_number || ''} created successfully!`);
        setShowCreateModal(false);
        setInvoiceItems([{ product_id: 0, quantity: '1' }]);
        setSelectedCustomer(null);
        loadData();
      }
    } catch (error: any) {
      Alert.alert('Creation Failed', error.message || 'Could not create invoice.');
    } finally {
      setSubmitting(false);
    }
  };

  // Filtered List
  const filtered = invoices.filter((inv) => {
    const matchesSearch =
      inv.invoice_number.toLowerCase().includes(search.toLowerCase()) ||
      (inv.customer_name || '').toLowerCase().includes(search.toLowerCase()) ||
      (inv.customer?.name || '').toLowerCase().includes(search.toLowerCase());

    if (filterStatus === 'ALL') return matchesSearch;
    return matchesSearch && inv.status?.toUpperCase() === filterStatus;
  });

  return (
    <View style={styles.container}>
      {/* Top Controls */}
      <View style={styles.topBar}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search by invoice # or customer..."
          placeholderTextColor={Colors.textMuted}
          value={search}
          onChangeText={setSearch}
        />
        <TouchableOpacity
          style={styles.addBtn}
          onPress={() => setShowCreateModal(true)}
        >
          <Text style={styles.addBtnText}>+ New Sale</Text>
        </TouchableOpacity>
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterRow}>
        {['ALL', 'UNPAID', 'PARTIAL', 'PAID'].map((st) => (
          <TouchableOpacity
            key={st}
            style={[styles.filterTab, filterStatus === st && styles.filterTabActive]}
            onPress={() => setFilterStatus(st)}
          >
            <Text
              style={[
                styles.filterTabText,
                filterStatus === st && styles.filterTabTextActive,
              ]}
            >
              {st}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* List */}
      {loading ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          data={filtered}
          keyExtractor={(item) => item.id.toString()}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => loadData(true)}
              colors={[Colors.primary]}
            />
          }
          contentContainerStyle={{ padding: 14, paddingBottom: 50 }}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No invoices found matching criteria.</Text>
            </View>
          }
          renderItem={({ item }) => (
            <TouchableOpacity
              style={styles.card}
              onPress={() => handleOpenDetail(item.id)}
            >
              <View style={styles.cardHeader}>
                <Text style={styles.cardInvoiceNo}>{item.invoice_number}</Text>
                <View
                  style={[
                    styles.badge,
                    item.status === 'Paid'
                      ? styles.badgePaid
                      : item.status === 'Partial'
                      ? styles.badgePartial
                      : styles.badgeUnpaid,
                  ]}
                >
                  <Text
                    style={[
                      styles.badgeText,
                      item.status === 'Paid'
                        ? { color: '#065F46' }
                        : item.status === 'Partial'
                        ? { color: '#92400E' }
                        : { color: '#991B1B' },
                    ]}
                  >
                    {item.status}
                  </Text>
                </View>
              </View>

              <Text style={styles.cardCustomer}>
                👤 {item.customer_name || item.customer?.name || 'Customer'}
              </Text>

              <View style={styles.cardFooter}>
                <Text style={styles.cardDate}>📅 {item.invoice_date}</Text>
                <Text style={styles.cardTotal}>
                  ₹{(item.grand_total || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 })}
                </Text>
              </View>
            </TouchableOpacity>
          )}
        />
      )}

      {/* Create Invoice Modal */}
      <Modal visible={showCreateModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalBox}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Create New Sale Invoice</Text>
              <TouchableOpacity onPress={() => setShowCreateModal(false)}>
                <Text style={styles.closeBtnText}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={{ maxHeight: 420 }} keyboardShouldPersistTaps="handled">
              {/* Customer Selector */}
              <Text style={styles.formLabel}>Customer *</Text>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.customerScroll}>
                {customers.map((c) => (
                  <TouchableOpacity
                    key={c.id}
                    style={[
                      styles.customerChip,
                      selectedCustomer === c.id && styles.customerChipActive,
                    ]}
                    onPress={() => setSelectedCustomer(c.id)}
                  >
                    <Text
                      style={[
                        styles.customerChipText,
                        selectedCustomer === c.id && styles.customerChipTextActive,
                      ]}
                    >
                      {c.name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>

              {/* Items Section */}
              <View style={styles.itemsHeader}>
                <Text style={styles.formLabel}>Invoice Items</Text>
                <TouchableOpacity onPress={handleAddItemRow}>
                  <Text style={styles.addItemText}>+ Add Item</Text>
                </TouchableOpacity>
              </View>

              {invoiceItems.map((item, idx) => (
                <View key={idx} style={styles.itemRow}>
                  <View style={{ flex: 2, marginRight: 8 }}>
                    <Text style={styles.itemIndex}>Item #{idx + 1} Product</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                      {products.map((p) => (
                        <TouchableOpacity
                          key={p.id}
                          style={[
                            styles.prodChip,
                            item.product_id === p.id && styles.prodChipActive,
                          ]}
                          onPress={() => {
                            const copy = [...invoiceItems];
                            copy[idx].product_id = p.id;
                            setInvoiceItems(copy);
                          }}
                        >
                          <Text
                            style={[
                              styles.prodChipText,
                              item.product_id === p.id && styles.prodChipTextActive,
                            ]}
                          >
                            {p.name} (₹{p.price})
                          </Text>
                        </TouchableOpacity>
                      ))}
                    </ScrollView>
                  </View>

                  <View style={{ width: 65, marginRight: 6 }}>
                    <Text style={styles.itemIndex}>Qty</Text>
                    <TextInput
                      style={styles.qtyInput}
                      keyboardType="numeric"
                      value={item.quantity}
                      onChangeText={(val) => {
                        const copy = [...invoiceItems];
                        copy[idx].quantity = val;
                        setInvoiceItems(copy);
                      }}
                    />
                  </View>

                  {invoiceItems.length > 1 && (
                    <TouchableOpacity
                      onPress={() => handleRemoveItemRow(idx)}
                      style={styles.deleteRowBtn}
                    >
                      <Text style={styles.deleteRowText}>🗑️</Text>
                    </TouchableOpacity>
                  )}
                </View>
              ))}
            </ScrollView>

            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowCreateModal(false)}
              >
                <Text style={styles.cancelBtnText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.submitBtn, submitting && { opacity: 0.6 }]}
                onPress={handleCreateInvoice}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#FFF" />
                ) : (
                  <Text style={styles.submitBtnText}>Generate Invoice</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Invoice Detail Modal */}
      <Modal visible={!!detailInvoice} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalBox}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Invoice {detailInvoice?.invoice_number}</Text>
              <TouchableOpacity onPress={() => setDetailInvoice(null)}>
                <Text style={styles.closeBtnText}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={{ maxHeight: 420 }}>
              <View style={styles.detailCard}>
                <Text style={styles.detailLabel}>Customer:</Text>
                <Text style={styles.detailVal}>{detailInvoice?.customer?.name}</Text>

                <Text style={styles.detailLabel}>Date:</Text>
                <Text style={styles.detailVal}>{detailInvoice?.invoice_date}</Text>

                <Text style={styles.detailLabel}>Status:</Text>
                <Text style={styles.detailVal}>{detailInvoice?.status}</Text>

                <Text style={styles.detailLabel}>Total Amount:</Text>
                <Text style={[styles.detailVal, { color: Colors.accent, fontWeight: '800' }]}>
                  ₹{(detailInvoice?.grand_total || 0).toLocaleString('en-IN')}
                </Text>

                <Text style={styles.detailLabel}>Paid Amount:</Text>
                <Text style={styles.detailVal}>
                  ₹{(detailInvoice?.paid_amount || 0).toLocaleString('en-IN')}
                </Text>

                <Text style={styles.detailLabel}>Pending Balance:</Text>
                <Text style={[styles.detailVal, { color: Colors.danger }]}>
                  ₹{(detailInvoice?.pending_amount || 0).toLocaleString('en-IN')}
                </Text>
              </View>

              <Text style={[styles.formLabel, { marginTop: 14 }]}>Line Items</Text>
              {detailInvoice?.items?.map((it, idx) => (
                <View key={idx} style={styles.detailItemRow}>
                  <Text style={{ fontWeight: '600', color: Colors.text }}>
                    {it.product_name || 'Product'} (x{it.quantity})
                  </Text>
                  <Text style={{ fontWeight: '700', color: Colors.textSecondary }}>
                    ₹{it.total_price?.toLocaleString('en-IN')}
                  </Text>
                </View>
              ))}
            </ScrollView>

            <TouchableOpacity
              style={[styles.submitBtn, { marginTop: 14 }]}
              onPress={() => setDetailInvoice(null)}
            >
              <Text style={styles.submitBtnText}>Close</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.bg,
  },
  topBar: {
    flexDirection: 'row',
    padding: 12,
    gap: 8,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  searchInput: {
    flex: 1,
    backgroundColor: Colors.bg,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    fontSize: 13,
    color: Colors.text,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  addBtn: {
    backgroundColor: Colors.primary,
    paddingHorizontal: 14,
    borderRadius: 8,
    justifyContent: 'center',
  },
  addBtnText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '700',
  },
  filterRow: {
    flexDirection: 'row',
    paddingHorizontal: 12,
    paddingVertical: 8,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    gap: 6,
  },
  filterTab: {
    paddingVertical: 5,
    paddingHorizontal: 12,
    borderRadius: 16,
    backgroundColor: Colors.bg,
  },
  filterTabActive: {
    backgroundColor: Colors.primary,
  },
  filterTabText: {
    fontSize: 11,
    fontWeight: '700',
    color: Colors.textSecondary,
  },
  filterTabTextActive: {
    color: '#FFFFFF',
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
  cardInvoiceNo: {
    fontSize: 14.5,
    fontWeight: '800',
    color: Colors.text,
  },
  cardCustomer: {
    fontSize: 13,
    color: Colors.textSecondary,
    marginBottom: 10,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingTop: 8,
  },
  cardDate: {
    fontSize: 11.5,
    color: Colors.textMuted,
  },
  cardTotal: {
    fontSize: 15,
    fontWeight: '800',
    color: Colors.primaryDark,
  },
  badge: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 10,
  },
  badgePaid: { backgroundColor: '#D1FAE5' },
  badgePartial: { backgroundColor: '#FEF3C7' },
  badgeUnpaid: { backgroundColor: '#FEE2E2' },
  badgeText: { fontSize: 10.5, fontWeight: '700' },
  emptyContainer: {
    alignItems: 'center',
    marginTop: 60,
  },
  emptyText: {
    color: Colors.textMuted,
    fontSize: 13,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.55)',
    justifyContent: 'center',
    padding: 16,
  },
  modalBox: {
    backgroundColor: '#FFFFFF',
    borderRadius: 18,
    padding: 18,
    ...Shadows.lg,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    paddingBottom: 10,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: Colors.text,
  },
  closeBtnText: {
    fontSize: 18,
    color: Colors.textMuted,
    fontWeight: '700',
  },
  formLabel: {
    fontSize: 12.5,
    fontWeight: '700',
    color: Colors.text,
    marginBottom: 6,
  },
  customerScroll: {
    flexDirection: 'row',
    marginBottom: 14,
  },
  customerChip: {
    backgroundColor: Colors.bg,
    borderWidth: 1,
    borderColor: Colors.border,
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 8,
    marginRight: 6,
  },
  customerChipActive: {
    backgroundColor: Colors.primaryLight,
    borderColor: Colors.primary,
  },
  customerChipText: {
    fontSize: 12,
    color: Colors.textSecondary,
    fontWeight: '600',
  },
  customerChipTextActive: {
    color: Colors.primary,
  },
  itemsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 6,
    marginBottom: 6,
  },
  addItemText: {
    fontSize: 12,
    fontWeight: '700',
    color: Colors.primary,
  },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.bg,
    padding: 8,
    borderRadius: 8,
    marginBottom: 8,
  },
  itemIndex: {
    fontSize: 10,
    color: Colors.textMuted,
    fontWeight: '700',
    marginBottom: 4,
  },
  prodChip: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: Colors.border,
    paddingHorizontal: 8,
    paddingVertical: 5,
    borderRadius: 6,
    marginRight: 4,
  },
  prodChipActive: {
    backgroundColor: Colors.primary,
    borderColor: Colors.primary,
  },
  prodChipText: {
    fontSize: 11,
    color: Colors.text,
  },
  prodChipTextActive: {
    color: '#FFFFFF',
    fontWeight: '700',
  },
  qtyInput: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 6,
    paddingVertical: 5,
    paddingHorizontal: 6,
    fontSize: 12,
    textAlign: 'center',
  },
  deleteRowBtn: {
    padding: 6,
  },
  deleteRowText: {
    fontSize: 14,
  },
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 8,
    marginTop: 14,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingTop: 12,
  },
  cancelBtn: {
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: Colors.bg,
  },
  cancelBtnText: {
    fontSize: 13,
    color: Colors.textSecondary,
    fontWeight: '600',
  },
  submitBtn: {
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 8,
    backgroundColor: Colors.primary,
  },
  submitBtnText: {
    fontSize: 13,
    color: '#FFFFFF',
    fontWeight: '700',
  },
  detailCard: {
    backgroundColor: Colors.bg,
    borderRadius: 10,
    padding: 12,
  },
  detailLabel: {
    fontSize: 11,
    color: Colors.textMuted,
    fontWeight: '600',
    marginTop: 4,
  },
  detailVal: {
    fontSize: 13.5,
    color: Colors.text,
    fontWeight: '600',
  },
  detailItemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
});
