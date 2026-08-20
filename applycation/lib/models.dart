// ─── All ERP Models ──────────────────────────────────────────────────────────

class DashboardStats {
  final int customers, suppliers, products, invoices, productions;
  final double totalRevenue, paid, unpaid;
  final List<RecentInvoice> recentInvoices;
  DashboardStats({required this.customers, required this.suppliers,
    required this.products, required this.invoices,
    required this.productions, required this.totalRevenue,
    required this.paid, required this.unpaid, required this.recentInvoices});
  factory DashboardStats.fromJson(Map<String, dynamic> j) {
    final s = j['stats'] as Map<String, dynamic>? ?? {};
    return DashboardStats(
      customers: s['customers'] ?? 0, suppliers: s['suppliers'] ?? 0,
      products: s['products'] ?? 0, invoices: s['invoices'] ?? 0,
      productions: s['production_runs'] ?? 0,
      totalRevenue: (s['total_revenue'] ?? 0).toDouble(),
      paid: (s['paid'] ?? 0).toDouble(), unpaid: (s['unpaid'] ?? 0).toDouble(),
      recentInvoices: (j['recent_invoices'] as List? ?? [])
          .map((e) => RecentInvoice.fromJson(e)).toList(),
    );
  }
}

class RecentInvoice {
  final int id;
  final String invoiceNumber, customerName, status, invoiceDate;
  final double grandTotal, pendingAmount;
  RecentInvoice({required this.id, required this.invoiceNumber,
    required this.customerName, required this.status,
    required this.invoiceDate, required this.grandTotal, required this.pendingAmount});
  factory RecentInvoice.fromJson(Map<String, dynamic> j) => RecentInvoice(
    id: j['id'] ?? 0, invoiceNumber: j['invoice_number'] ?? '',
    customerName: j['customer_name'] ?? '', status: j['status'] ?? '',
    invoiceDate: j['invoice_date'] ?? '',
    grandTotal: (j['grand_total'] ?? 0).toDouble(),
    pendingAmount: (j['pending_amount'] ?? 0).toDouble(),
  );
}

class Customer {
  final int id; final String name;
  final String? phone, email, address, gstin, state;
  Customer({required this.id, required this.name,
    this.phone, this.email, this.address, this.gstin, this.state});
  factory Customer.fromJson(Map<String, dynamic> j) => Customer(
    id: j['id'] ?? 0, name: j['name'] ?? '', phone: j['phone'],
    email: j['email'], address: j['address'], gstin: j['gstin'], state: j['state']);
  Map<String, dynamic> toJson() =>
    {'name': name, 'phone': phone, 'email': email,
     'address': address, 'gstin': gstin, 'state': state};
}

class Supplier {
  final int id; final String name;
  final String? phone, email, gstin, address;
  Supplier({required this.id, required this.name,
    this.phone, this.email, this.gstin, this.address});
  factory Supplier.fromJson(Map<String, dynamic> j) => Supplier(
    id: j['id'] ?? 0, name: j['name'] ?? '', phone: j['phone'],
    email: j['email'], gstin: j['gstin'], address: j['address']);
  Map<String, dynamic> toJson() =>
    {'name': name, 'phone': phone, 'email': email, 'gstin': gstin, 'address': address};
}

class Product {
  final int id; final String name;
  final double price, gstRate;
  final String? description, hsnCode;
  Product({required this.id, required this.name, required this.price,
    required this.gstRate, this.description, this.hsnCode});
  factory Product.fromJson(Map<String, dynamic> j) => Product(
    id: j['id'] ?? 0, name: j['name'] ?? '',
    price: (j['price'] ?? 0).toDouble(), gstRate: (j['gst_rate'] ?? 18).toDouble(),
    description: j['description'], hsnCode: j['hsn_code']);
  Map<String, dynamic> toJson() =>
    {'name': name, 'description': description, 'price': price,
     'hsn_code': hsnCode, 'gst_rate': gstRate};
}

class Material {
  final int id; final String type, name;
  final String? unit, secondaryUnit;
  final double stockKg, stockPcs, stockQuantity;
  final double? kgPerPcs;
  Material({required this.id, required this.type, required this.name,
    this.unit, this.secondaryUnit, required this.stockKg,
    required this.stockPcs, required this.stockQuantity, this.kgPerPcs});
  bool get hasDualUnit => (kgPerPcs ?? 0) > 0;
  factory Material.fromJson(Map<String, dynamic> j) => Material(
    id: j['id'] ?? 0, type: j['type'] ?? '', name: j['name'] ?? '',
    unit: j['unit'], secondaryUnit: j['secondary_unit'],
    stockKg:  (j['stock_kg']  ?? j['stock_quantity'] ?? 0).toDouble(),
    stockPcs: (j['stock_pcs'] ?? 0).toDouble(),
    stockQuantity: (j['stock_quantity'] ?? 0).toDouble(),
    kgPerPcs: j['kg_per_pcs'] != null ? (j['kg_per_pcs']).toDouble() : null);
  Map<String, dynamic> toJson() =>
    {'type': type, 'name': name, 'unit': unit, 'secondary_unit': secondaryUnit,
     'stock_kg': stockKg, 'stock_pcs': stockPcs, 'kg_per_pcs': kgPerPcs};
}

class Transporter {
  final int id; final String name;
  final String? vehicleNo, phone;
  Transporter({required this.id, required this.name, this.vehicleNo, this.phone});
  factory Transporter.fromJson(Map<String, dynamic> j) => Transporter(
    id: j['id'] ?? 0, name: j['name'] ?? '',
    vehicleNo: j['vehicle_no'], phone: j['phone']);
  Map<String, dynamic> toJson() =>
    {'name': name, 'vehicle_no': vehicleNo, 'phone': phone};
}

class Invoice {
  final int id, customerId;
  final String invoiceNumber, status, invoiceDate;
  final String? customerName, lrNumber, notes;
  final double subtotal, cgst, sgst, igst, grandTotal, paidAmount, pendingAmount;
  final List<InvoiceItem> items;
  final List<InvoicePayment> payments;
  Invoice({required this.id, required this.customerId,
    required this.invoiceNumber, required this.status,
    required this.invoiceDate, this.customerName,
    this.lrNumber, this.notes,
    required this.subtotal, required this.cgst, required this.sgst,
    required this.igst, required this.grandTotal,
    required this.paidAmount, required this.pendingAmount,
    this.items = const [], this.payments = const []});
  factory Invoice.fromJson(Map<String, dynamic> j) => Invoice(
    id: j['id'] ?? 0, customerId: j['customer_id'] ?? 0,
    invoiceNumber: j['invoice_number'] ?? '', status: j['status'] ?? 'Unpaid',
    invoiceDate: j['invoice_date'] ?? '', customerName: j['customer_name'],
    lrNumber: j['lr_number'], notes: j['notes'],
    subtotal: (j['subtotal'] ?? 0).toDouble(), cgst: (j['cgst'] ?? 0).toDouble(),
    sgst: (j['sgst'] ?? 0).toDouble(), igst: (j['igst'] ?? 0).toDouble(),
    grandTotal: (j['grand_total'] ?? 0).toDouble(),
    paidAmount: (j['paid_amount'] ?? 0).toDouble(),
    pendingAmount: (j['pending_amount'] ?? 0).toDouble(),
    items: (j['items'] as List? ?? []).map((e) => InvoiceItem.fromJson(e)).toList(),
    payments: (j['payments'] as List? ?? []).map((e) => InvoicePayment.fromJson(e)).toList());
}

class InvoiceItem {
  final int id, quantity; final String? productName, hsnCode;
  final double unitPrice, totalPrice, gstRate;
  InvoiceItem({required this.id, required this.quantity, this.productName,
    this.hsnCode, required this.unitPrice, required this.totalPrice, required this.gstRate});
  factory InvoiceItem.fromJson(Map<String, dynamic> j) => InvoiceItem(
    id: j['id'] ?? 0, quantity: (j['quantity'] ?? 1).toInt(),
    productName: j['product_name'], hsnCode: j['hsn_code'],
    unitPrice: (j['unit_price'] ?? 0).toDouble(),
    totalPrice: (j['total_price'] ?? 0).toDouble(),
    gstRate: (j['gst_rate'] ?? 0).toDouble());
}

class InvoicePayment {
  final int id; final double amount;
  final String paymentDate, paymentMode;
  final String? referenceNo;
  InvoicePayment({required this.id, required this.amount,
    required this.paymentDate, required this.paymentMode, this.referenceNo});
  factory InvoicePayment.fromJson(Map<String, dynamic> j) => InvoicePayment(
    id: j['id'] ?? 0, amount: (j['amount'] ?? 0).toDouble(),
    paymentDate: j['payment_date'] ?? '', paymentMode: j['payment_mode'] ?? 'Cash',
    referenceNo: j['reference_no']);
}

class LedgerEntry {
  final int id; final String entityType, type, transactionDate;
  final String? entityName, description;
  final double amount;
  LedgerEntry({required this.id, required this.entityType,
    required this.type, required this.transactionDate,
    required this.amount, this.entityName, this.description});
  factory LedgerEntry.fromJson(Map<String, dynamic> j) => LedgerEntry(
    id: j['id'] ?? 0, entityType: j['entity_type'] ?? '',
    type: j['type'] ?? '', transactionDate: j['transaction_date'] ?? '',
    amount: (j['amount'] ?? 0).toDouble(),
    entityName: j['entity_name'], description: j['description']);
}

class MatTxn {
  final int id; final String type, unitType, txnDate;
  final String? materialName, supplierName, referenceNo, vehicleNo;
  final double quantity; final double? quantityKg, quantityPcs, rate, totalAmount;
  MatTxn({required this.id, required this.type, required this.unitType,
    required this.txnDate, required this.quantity,
    this.materialName, this.supplierName, this.referenceNo,
    this.vehicleNo, this.quantityKg, this.quantityPcs,
    this.rate, this.totalAmount});
  factory MatTxn.fromJson(Map<String, dynamic> j) => MatTxn(
    id: j['id'] ?? 0, type: j['type'] ?? 'IN',
    unitType: j['unit_type'] ?? 'Kg', txnDate: j['transaction_date'] ?? '',
    quantity: (j['quantity'] ?? 0).toDouble(),
    materialName: j['material_name'], supplierName: j['supplier_name'],
    referenceNo: j['reference_no'], vehicleNo: j['vehicle_no'],
    quantityKg: j['quantity_kg'] != null ? (j['quantity_kg']).toDouble() : null,
    quantityPcs: j['quantity_pcs'] != null ? (j['quantity_pcs']).toDouble() : null,
    rate: j['rate'] != null ? (j['rate']).toDouble() : null,
    totalAmount: j['total_amount'] != null ? (j['total_amount']).toDouble() : null);
}
