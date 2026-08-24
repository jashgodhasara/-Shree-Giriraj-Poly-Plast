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
  Linking,
} from 'react-native';
import { api, getCachedData } from '../services/api';
import { ENDPOINTS } from '../config/api';
import { Customer } from '../types';
import { Colors, Shadows } from '../components/Theme';

export const CustomersScreen: React.FC = () => {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');

  // Detail Modal
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
  const [customerDetail, setCustomerDetail] = useState<any | null>(null);
  const [loadingDetail, setLoadingDetail] = useState(false);
  const [showDetailModal, setShowDetailModal] = useState(false);

  // Add Customer Modal
  const [showModal, setShowModal] = useState(false);
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [address, setAddress] = useState('');
  const [gstin, setGstin] = useState('');
  const [state, setState] = useState('Gujarat');
  const [submitting, setSubmitting] = useState(false);

  const openCustomerDetail = async (c: Customer) => {
    setSelectedCustomer(c);
    setCustomerDetail(null);
    setShowDetailModal(true);
    setLoadingDetail(true);
    try {
      const res = await api.get<any>(`${ENDPOINTS.CUSTOMERS}/${c.id}`);
      setCustomerDetail(res);
    } catch (e) {
      console.error('Failed to load customer detail:', e);
    } finally {
      setLoadingDetail(false);
    }
  };

  const fetchCustomers = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const res = await api.get<Customer[]>(ENDPOINTS.CUSTOMERS);
      setCustomers(res || []);
    } catch (e) {
      console.error('Failed to load customers:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    getCachedData<Customer[]>(ENDPOINTS.CUSTOMERS).then((cached) => {
      if (cached && cached.length > 0) {
        setCustomers(cached);
        setLoading(false);
      }
    });
    fetchCustomers();

    const interval = setInterval(() => {
      fetchCustomers();
    }, 6000);
    return () => clearInterval(interval);
  }, [fetchCustomers]);

  const handleCreateCustomer = async () => {
    if (!name.trim()) {
      Alert.alert('Validation', 'Customer Name is required.');
      return;
    }
    setSubmitting(true);
    try {
      await api.post(ENDPOINTS.CUSTOMERS, {
        name: name.trim(),
        phone: phone.trim() || undefined,
        email: email.trim() || undefined,
        address: address.trim() || undefined,
        gstin: gstin.trim() || undefined,
        state: state.trim() || undefined,
      });
      Alert.alert('Success', 'Customer added successfully!');
      setShowModal(false);
      setName('');
      setPhone('');
      setEmail('');
      setAddress('');
      setGstin('');
      fetchCustomers();
    } catch (e: any) {
      Alert.alert('Error', e.message || 'Failed to add customer.');
    } finally {
      setSubmitting(false);
    }
  };

  const filtered = React.useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return customers;
    return customers.filter(
      (c) =>
        c.name.toLowerCase().includes(q) ||
        (c.phone && c.phone.includes(q)) ||
        (c.gstin && c.gstin.toLowerCase().includes(q))
    );
  }, [customers, search]);

  return (
    <View style={styles.container}>
      {/* Top Bar */}
      <View style={styles.topBar}>
        <TextInput
          style={styles.searchInput}
          placeholder="Search by customer, phone, GSTIN..."
          placeholderTextColor={Colors.textMuted}
          value={search}
          onChangeText={setSearch}
        />
        <TouchableOpacity style={styles.addBtn} onPress={() => setShowModal(true)}>
          <Text style={styles.addBtnText}>+ Add</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          data={filtered}
          keyExtractor={(item) => item.id.toString()}
          initialNumToRender={10}
          maxToRenderPerBatch={10}
          windowSize={5}
          removeClippedSubviews={true}
          keyboardShouldPersistTaps="handled"
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => fetchCustomers(true)}
              colors={[Colors.primary]}
            />
          }
          contentContainerStyle={{ padding: 14, paddingBottom: 50 }}
          ListEmptyComponent={
            <View style={styles.emptyBox}>
              <Text style={styles.emptyText}>No customers found.</Text>
            </View>
          }
          renderItem={({ item }) => (
            <TouchableOpacity
              style={styles.card}
              activeOpacity={0.7}
              onPress={() => openCustomerDetail(item)}
            >
              <View style={styles.cardHeader}>
                <View style={styles.avatar}>
                  <Text style={styles.avatarText}>{item.name.charAt(0).toUpperCase()}</Text>
                </View>
                <View style={{ flex: 1, marginLeft: 12 }}>
                  <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
                    <Text style={styles.customerName}>{item.name}</Text>
                    <Text style={{ fontSize: 11, color: Colors.primary, fontWeight: '700' }}>View Profile →</Text>
                  </View>
                  {item.gstin ? (
                    <Text style={styles.gstinText}>GSTIN: {item.gstin}</Text>
                  ) : null}
                </View>
              </View>

              <View style={styles.detailsRow}>
                {item.phone ? (
                  <TouchableOpacity
                    style={styles.contactChip}
                    onPress={() => Linking.openURL(`tel:${item.phone}`)}
                  >
                    <Text style={styles.contactChipText}>📞 {item.phone}</Text>
                  </TouchableOpacity>
                ) : null}
                {item.state ? (
                  <View style={[styles.contactChip, { backgroundColor: '#F1F5F9' }]}>
                    <Text style={[styles.contactChipText, { color: Colors.textSecondary }]}>
                      📍 {item.state}
                    </Text>
                  </View>
                ) : null}
              </View>

              {item.address ? (
                <Text style={styles.addressText} numberOfLines={2}>🏠 {item.address}</Text>
              ) : null}
            </TouchableOpacity>
          )}
        />
      )}

      {/* Add Customer Modal */}
      <Modal visible={showModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalBox}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Add New Customer</Text>
              <TouchableOpacity onPress={() => setShowModal(false)}>
                <Text style={styles.closeText}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={{ maxHeight: 420 }} keyboardShouldPersistTaps="handled">
              <Text style={styles.inputLabel}>Company / Customer Name *</Text>
              <TextInput
                style={styles.input}
                placeholder="e.g. Acme Polymers Ltd."
                placeholderTextColor={Colors.textMuted}
                value={name}
                onChangeText={setName}
              />

              <Text style={styles.inputLabel}>Phone Number</Text>
              <TextInput
                style={styles.input}
                placeholder="e.g. 9876543210"
                placeholderTextColor={Colors.textMuted}
                keyboardType="phone-pad"
                value={phone}
                onChangeText={setPhone}
              />

              <Text style={styles.inputLabel}>Email Address</Text>
              <TextInput
                style={styles.input}
                placeholder="contact@company.com"
                placeholderTextColor={Colors.textMuted}
                keyboardType="email-address"
                autoCapitalize="none"
                value={email}
                onChangeText={setEmail}
              />

              <Text style={styles.inputLabel}>GSTIN</Text>
              <TextInput
                style={styles.input}
                placeholder="24AAAAA0000A1Z5"
                placeholderTextColor={Colors.textMuted}
                autoCapitalize="characters"
                value={gstin}
                onChangeText={setGstin}
              />

              <Text style={styles.inputLabel}>State</Text>
              <TextInput
                style={styles.input}
                placeholder="Gujarat"
                placeholderTextColor={Colors.textMuted}
                value={state}
                onChangeText={setState}
              />

              <Text style={styles.inputLabel}>Full Address</Text>
              <TextInput
                style={[styles.input, { height: 60 }]}
                placeholder="Factory address / billing location"
                placeholderTextColor={Colors.textMuted}
                multiline
                value={address}
                onChangeText={setAddress}
              />
            </ScrollView>

            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowModal(false)}
              >
                <Text style={styles.cancelBtnText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.submitBtn, submitting && { opacity: 0.6 }]}
                onPress={handleCreateCustomer}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#FFF" />
                ) : (
                  <Text style={styles.submitBtnText}>Save Customer</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Customer 360° Detail Modal */}
      <Modal visible={showDetailModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalBox, { maxHeight: '90%', padding: 0 }]}>
            {/* Modal Header Banner */}
            <View style={styles.detailHeaderBanner}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 12, flex: 1 }}>
                <View style={styles.detailAvatar}>
                  <Text style={styles.detailAvatarText}>
                    {selectedCustomer?.name?.charAt(0).toUpperCase() || 'C'}
                  </Text>
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={styles.detailNameText} numberOfLines={1}>
                    {selectedCustomer?.name}
                  </Text>
                  <Text style={styles.detailSubText}>
                    {selectedCustomer?.gstin ? `GST: ${selectedCustomer.gstin}` : 'Customer Profile'}
                  </Text>
                </View>
              </View>
              <TouchableOpacity
                style={styles.detailCloseBtn}
                onPress={() => setShowDetailModal(false)}
              >
                <Text style={{ color: '#FFF', fontSize: 16, fontWeight: '800' }}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={{ padding: 16 }} showsVerticalScrollIndicator={false}>
              {loadingDetail ? (
                <View style={{ padding: 30, alignItems: 'center' }}>
                  <ActivityIndicator size="large" color={Colors.primary} />
                  <Text style={{ marginTop: 10, color: Colors.textMuted, fontSize: 13 }}>Loading customer sales & bills...</Text>
                </View>
              ) : (
                <>
                  {/* KPI Summary Cards */}
                  <View style={styles.detailStatsRow}>
                    <View style={[styles.detailStatBox, { backgroundColor: '#EEF2FF' }]}>
                      <Text style={styles.detailStatLabel}>Lifetime Sales</Text>
                      <Text style={[styles.detailStatVal, { color: Colors.primary }]}>
                        ₹{Number(customerDetail?.total_sales || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 })}
                      </Text>
                      <Text style={styles.detailStatSub}>{customerDetail?.invoice_count || 0} Bills</Text>
                    </View>

                    <View style={[styles.detailStatBox, { backgroundColor: '#ECFDF5' }]}>
                      <Text style={styles.detailStatLabel}>Total Paid</Text>
                      <Text style={[styles.detailStatVal, { color: '#059669' }]}>
                        ₹{Number(customerDetail?.total_paid || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 })}
                      </Text>
                      <Text style={styles.detailStatSub}>Received</Text>
                    </View>

                    <View style={[styles.detailStatBox, { backgroundColor: (customerDetail?.total_pending || 0) > 0 ? '#FEF2F2' : '#F0FDF4' }]}>
                      <Text style={styles.detailStatLabel}>Balance Due</Text>
                      <Text style={[styles.detailStatVal, { color: (customerDetail?.total_pending || 0) > 0 ? '#DC2626' : '#16A34A' }]}>
                        ₹{Number(customerDetail?.total_pending || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 })}
                      </Text>
                      <Text style={styles.detailStatSub}>{(customerDetail?.total_pending || 0) > 0 ? 'Pending' : 'Cleared'}</Text>
                    </View>
                  </View>

                  {/* Contact & Address Bar */}
                  <View style={styles.detailContactCard}>
                    {selectedCustomer?.phone ? (
                      <TouchableOpacity
                        style={styles.detailActionBtn}
                        onPress={() => Linking.openURL(`tel:${selectedCustomer.phone}`)}
                      >
                        <Text style={styles.detailActionBtnText}>📞 Call {selectedCustomer.phone}</Text>
                      </TouchableOpacity>
                    ) : null}
                    {selectedCustomer?.email ? (
                      <TouchableOpacity
                        style={[styles.detailActionBtn, { backgroundColor: '#F1F5F9' }]}
                        onPress={() => Linking.openURL(`mailto:${selectedCustomer.email}`)}
                      >
                        <Text style={[styles.detailActionBtnText, { color: Colors.text }]}>✉️ Email</Text>
                      </TouchableOpacity>
                    ) : null}
                  </View>

                  {selectedCustomer?.address ? (
                    <View style={styles.detailAddressBox}>
                      <Text style={{ fontSize: 11, fontWeight: '700', color: Colors.textMuted, textTransform: 'uppercase' }}>Billing Address</Text>
                      <Text style={{ fontSize: 13, color: Colors.text, marginTop: 3 }}>
                        {selectedCustomer.address}
                        {selectedCustomer.state ? `, ${selectedCustomer.state}` : ''}
                      </Text>
                    </View>
                  ) : null}

                  {/* Invoices & Bills List */}
                  <View style={{ marginTop: 16, marginBottom: 8 }}>
                    <Text style={{ fontSize: 14, fontWeight: '800', color: Colors.text, marginBottom: 8 }}>
                      📋 All Invoices & Bills ({customerDetail?.invoices?.length || 0})
                    </Text>

                    {(!customerDetail?.invoices || customerDetail.invoices.length === 0) ? (
                      <View style={{ padding: 20, alignItems: 'center' }}>
                        <Text style={{ color: Colors.textMuted, fontSize: 13 }}>No invoices found for this customer.</Text>
                      </View>
                    ) : (
                      customerDetail.invoices.map((inv: any) => {
                        const isPaid = inv.status === 'Paid';
                        const isPartial = inv.status === 'Partial';
                        return (
                          <View key={inv.id} style={styles.invoiceItemCard}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                              <Text style={styles.invoiceNumText}>{inv.invoice_number}</Text>
                              <View style={[
                                styles.statusBadge,
                                isPaid ? { backgroundColor: '#DCFCE7' } : isPartial ? { backgroundColor: '#FEF9C3' } : { backgroundColor: '#FEE2E2' }
                              ]}>
                                <Text style={[
                                  styles.statusBadgeText,
                                  isPaid ? { color: '#166534' } : isPartial ? { color: '#854D0E' } : { color: '#991B1B' }
                                ]}>
                                  {inv.status || 'Unpaid'}
                                </Text>
                              </View>
                            </View>

                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginTop: 6, alignItems: 'center' }}>
                              <Text style={{ fontSize: 11.5, color: Colors.textMuted }}>
                                📅 {inv.invoice_date ? new Date(inv.invoice_date).toLocaleDateString() : '—'}
                              </Text>
                              <Text style={styles.invoiceAmountText}>
                                ₹{Number(inv.grand_total || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                              </Text>
                            </View>

                            {inv.items && inv.items.length > 0 ? (
                              <Text style={{ fontSize: 11.5, color: Colors.textSecondary, marginTop: 4 }} numberOfLines={1}>
                                📦 {inv.items.map((it: any) => `${it.product?.name || 'Item'} (${it.quantity})`).join(', ')}
                              </Text>
                            ) : null}
                          </View>
                        );
                      })
                    )}
                  </View>
                </>
              )}
            </ScrollView>

            <View style={{ padding: 12, borderTopWidth: 1, borderTopColor: Colors.border, alignItems: 'flex-end' }}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setShowDetailModal(false)}
              >
                <Text style={styles.cancelBtnText}>Close</Text>
              </TouchableOpacity>
            </View>
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
    alignItems: 'center',
  },
  avatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: Colors.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 16,
    fontWeight: '800',
    color: Colors.primary,
  },
  customerName: {
    fontSize: 14.5,
    fontWeight: '700',
    color: Colors.text,
  },
  gstinText: {
    fontSize: 11.5,
    color: Colors.textMuted,
    marginTop: 2,
  },
  detailsRow: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 10,
  },
  contactChip: {
    backgroundColor: Colors.accentLight,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 6,
  },
  contactChipText: {
    fontSize: 11.5,
    fontWeight: '600',
    color: '#065F46',
  },
  addressText: {
    fontSize: 12,
    color: Colors.textSecondary,
    marginTop: 8,
  },
  emptyBox: {
    alignItems: 'center',
    marginTop: 50,
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
  closeText: {
    fontSize: 18,
    color: Colors.textMuted,
    fontWeight: '700',
  },
  inputLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: Colors.text,
    marginTop: 10,
    marginBottom: 4,
  },
  input: {
    backgroundColor: Colors.bg,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    fontSize: 13,
    color: Colors.text,
  },
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 8,
    marginTop: 16,
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
  // Detail Modal Styles
  detailHeaderBanner: {
    backgroundColor: '#0F172A',
    padding: 16,
    borderTopLeftRadius: 18,
    borderTopRightRadius: 18,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  detailAvatar: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: Colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
  },
  detailAvatarText: {
    fontSize: 18,
    fontWeight: '800',
    color: '#FFF',
  },
  detailNameText: {
    fontSize: 16,
    fontWeight: '800',
    color: '#FFF',
  },
  detailSubText: {
    fontSize: 11.5,
    color: '#94A3B8',
    marginTop: 2,
  },
  detailCloseBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(255,255,255,0.15)',
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 8,
  },
  detailStatsRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 14,
  },
  detailStatBox: {
    flex: 1,
    padding: 10,
    borderRadius: 10,
    alignItems: 'center',
  },
  detailStatLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: Colors.textMuted,
    textTransform: 'uppercase',
  },
  detailStatVal: {
    fontSize: 14,
    fontWeight: '800',
    marginTop: 2,
  },
  detailStatSub: {
    fontSize: 10,
    color: Colors.textMuted,
    marginTop: 1,
  },
  detailContactCard: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 10,
  },
  detailActionBtn: {
    flex: 1,
    backgroundColor: '#EEF2FF',
    paddingVertical: 9,
    paddingHorizontal: 12,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  detailActionBtnText: {
    fontSize: 12.5,
    fontWeight: '700',
    color: Colors.primary,
  },
  detailAddressBox: {
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 8,
    padding: 10,
    marginBottom: 12,
  },
  invoiceItemCard: {
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 10,
    padding: 12,
    marginBottom: 8,
  },
  invoiceNumText: {
    fontSize: 13,
    fontWeight: '800',
    color: Colors.primary,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
  },
  statusBadgeText: {
    fontSize: 10.5,
    fontWeight: '700',
  },
  invoiceAmountText: {
    fontSize: 13.5,
    fontWeight: '800',
    color: Colors.text,
  },
});
