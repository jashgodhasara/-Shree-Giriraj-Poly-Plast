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
import { Payment, Invoice } from '../types';
import { Colors, Shadows } from '../components/Theme';

export const PaymentsScreen: React.FC = () => {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Modal
  const [showModal, setShowModal] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<number | null>(null);
  const [amount, setAmount] = useState('');
  const [paymentMode, setPaymentMode] = useState('UPI');
  const [referenceNo, setReferenceNo] = useState('');
  const [remarks, setRemarks] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const fetchData = useCallback(async (isRefresh = false) => {
    if (isRefresh) setRefreshing(true);
    try {
      const [payRes, invRes] = await Promise.all([
        api.get<Payment[]>(ENDPOINTS.PAYMENTS),
        api.get<Invoice[]>(ENDPOINTS.INVOICES),
      ]);
      setPayments(payRes || []);
      setInvoices((invRes || []).filter((i) => i.status !== 'Paid'));
    } catch (e) {
      console.error('Failed to load payments data:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchData();
    const timer = setInterval(() => {
      fetchData();
    }, 15000);
    return () => clearInterval(timer);
  }, [fetchData]);

  const handleRecordPayment = async () => {
    if (!selectedInvoice) {
      Alert.alert('Validation', 'Please select an invoice.');
      return;
    }
    const num = parseFloat(amount);
    if (isNaN(num) || num <= 0) {
      Alert.alert('Validation', 'Please enter a valid payment amount.');
      return;
    }

    setSubmitting(true);
    try {
      await api.post(ENDPOINTS.PAYMENTS, {
        invoice_id: selectedInvoice,
        amount: num,
        payment_date: new Date().toISOString().split('T')[0],
        payment_mode: paymentMode,
        reference_no: referenceNo.trim() || undefined,
        remarks: remarks.trim() || undefined,
      });

      Alert.alert('Success', 'Payment recorded successfully!');
      setShowModal(false);
      setSelectedInvoice(null);
      setAmount('');
      setReferenceNo('');
      setRemarks('');
      fetchData();
    } catch (e: any) {
      Alert.alert('Payment Failed', e.message || 'Could not record payment.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.topBar}>
        <Text style={styles.screenTitle}>Payment Receipts</Text>
        <TouchableOpacity style={styles.addBtn} onPress={() => setShowModal(true)}>
          <Text style={styles.addBtnText}>+ Record Payment</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          data={payments}
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
              <Text style={styles.emptyText}>No payments recorded yet.</Text>
            </View>
          }
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.cardHeader}>
                <View>
                  <Text style={styles.invoiceNo}>
                    {item.invoice_number ? `Invoice: ${item.invoice_number}` : `Payment #${item.id}`}
                  </Text>
                  <Text style={styles.dateText}>📅 {item.payment_date}</Text>
                </View>
                <Text style={styles.amountText}>
                  ₹{(item.amount || 0).toLocaleString('en-IN')}
                </Text>
              </View>

              <View style={styles.badgeRow}>
                <View style={styles.modeBadge}>
                  <Text style={styles.modeText}>💳 {item.payment_mode}</Text>
                </View>
                {item.reference_no ? (
                  <Text style={styles.refText}>Ref: {item.reference_no}</Text>
                ) : null}
              </View>

              {item.remarks ? (
                <Text style={styles.remarksText}>Note: {item.remarks}</Text>
              ) : null}
            </View>
          )}
        />
      )}

      {/* Record Payment Modal */}
      <Modal visible={showModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalBox}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Record Payment</Text>
              <TouchableOpacity onPress={() => setShowModal(false)}>
                <Text style={styles.closeText}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={{ maxHeight: 420 }} keyboardShouldPersistTaps="handled">
              <Text style={styles.inputLabel}>Select Pending Invoice *</Text>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 12 }}>
                {invoices.map((inv) => (
                  <TouchableOpacity
                    key={inv.id}
                    style={[
                      styles.invChip,
                      selectedInvoice === inv.id && styles.invChipActive,
                    ]}
                    onPress={() => {
                      setSelectedInvoice(inv.id);
                      setAmount((inv.pending_amount || inv.grand_total).toString());
                    }}
                  >
                    <Text
                      style={[
                        styles.invChipText,
                        selectedInvoice === inv.id && styles.invChipTextActive,
                      ]}
                    >
                      {inv.invoice_number} (₹{inv.grand_total})
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>

              <Text style={styles.inputLabel}>Amount (₹) *</Text>
              <TextInput
                style={styles.input}
                placeholder="e.g. 5000"
                placeholderTextColor={Colors.textMuted}
                keyboardType="numeric"
                value={amount}
                onChangeText={setAmount}
              />

              <Text style={styles.inputLabel}>Payment Mode *</Text>
              <View style={styles.modeGrid}>
                {['UPI', 'Cash', 'NEFT', 'RTGS', 'Cheque'].map((m) => (
                  <TouchableOpacity
                    key={m}
                    style={[
                      styles.modeSelectBtn,
                      paymentMode === m && styles.modeSelectBtnActive,
                    ]}
                    onPress={() => setPaymentMode(m)}
                  >
                    <Text
                      style={[
                        styles.modeSelectText,
                        paymentMode === m && styles.modeSelectTextActive,
                      ]}
                    >
                      {m}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>

              <Text style={styles.inputLabel}>Reference / Transaction No.</Text>
              <TextInput
                style={styles.input}
                placeholder="e.g. UPI / Cheque / UTR #"
                placeholderTextColor={Colors.textMuted}
                value={referenceNo}
                onChangeText={setReferenceNo}
              />

              <Text style={styles.inputLabel}>Remarks</Text>
              <TextInput
                style={styles.input}
                placeholder="Optional notes"
                placeholderTextColor={Colors.textMuted}
                value={remarks}
                onChangeText={setRemarks}
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
                onPress={handleRecordPayment}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#FFF" />
                ) : (
                  <Text style={styles.submitBtnText}>Save Payment</Text>
                )}
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
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 14,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  screenTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: Colors.text,
  },
  addBtn: {
    backgroundColor: Colors.accent,
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 8,
  },
  addBtnText: {
    color: '#FFFFFF',
    fontSize: 12.5,
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
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  invoiceNo: {
    fontSize: 14,
    fontWeight: '800',
    color: Colors.text,
  },
  dateText: {
    fontSize: 11.5,
    color: Colors.textMuted,
    marginTop: 2,
  },
  amountText: {
    fontSize: 16,
    fontWeight: '800',
    color: Colors.accent,
  },
  badgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 10,
  },
  modeBadge: {
    backgroundColor: Colors.primaryLight,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  modeText: {
    fontSize: 11,
    fontWeight: '700',
    color: Colors.primary,
  },
  refText: {
    fontSize: 11.5,
    color: Colors.textMuted,
  },
  remarksText: {
    fontSize: 12,
    color: Colors.textSecondary,
    marginTop: 6,
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
  invChip: {
    backgroundColor: Colors.bg,
    borderWidth: 1,
    borderColor: Colors.border,
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 8,
    marginRight: 6,
  },
  invChipActive: {
    backgroundColor: Colors.primaryLight,
    borderColor: Colors.primary,
  },
  invChipText: {
    fontSize: 12,
    color: Colors.textSecondary,
    fontWeight: '600',
  },
  invChipTextActive: {
    color: Colors.primary,
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
  modeGrid: {
    flexDirection: 'row',
    gap: 6,
    flexWrap: 'wrap',
    marginBottom: 4,
  },
  modeSelectBtn: {
    backgroundColor: Colors.bg,
    borderWidth: 1,
    borderColor: Colors.border,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  modeSelectBtnActive: {
    backgroundColor: Colors.primary,
    borderColor: Colors.primary,
  },
  modeSelectText: {
    fontSize: 12,
    fontWeight: '600',
    color: Colors.textSecondary,
  },
  modeSelectTextActive: {
    color: '#FFFFFF',
    fontWeight: '700',
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
    backgroundColor: Colors.accent,
  },
  submitBtnText: {
    fontSize: 13,
    color: '#FFFFFF',
    fontWeight: '700',
  },
});
