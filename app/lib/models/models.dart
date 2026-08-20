// ── Dashboard Stats ──────────────────────────────────────
class DashboardStats {
  final int customers, suppliers, products, invoices, productions;
  final double totalRevenue, paid, unpaid;
  final List<RecentInvoice> recentInvoices;

  DashboardStats({
    required this.customers, required this.suppliers,
    required this.products, required this.invoices,
    required this.productions, required this.totalRevenue,
    required this.paid, required this.unpaid,
    required this.recentInvoices,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> j) {
    final s = j['stats'] as Map<String, dynamic>? ?? {};
    final ri = (j['recent_invoices'] as List? ?? [])
        .map((e) => RecentInvoice.fromJson(e)).toList();
    return DashboardStats(
      customers: s['customers'] ?? 0,
      suppliers: s['suppliers'] ?? 0,
      products:  s['products'] ?? 0,
      invoices:  s['invoices'] ?? 0,
      productions: s['production_runs'] ?? 0,
      totalRevenue: (s['total_revenue'] ?? 0).toDouble(),
      paid:   (s['paid'] ?? 0).toDouble(),
      unpaid: (s['unpaid'] ?? 0).toDouble(),
      recentInvoices: ri,
    );
  }
}

class RecentInvoice {
  final int id;
  final String invoiceNumber, customerName, status, invoiceDate;
  final double grandTotal, pendingAmount;

  RecentInvoice({
    required this.id, required this.invoiceNumber,
    required this.customerName, required this.status,
    required this.invoiceDate, required this.grandTotal,
    required this.pendingAmount,
  });

  factory RecentInvoice.fromJson(Map<String, dynamic> j) => RecentInvoice(
    id: j['id'] ?? 0,
    invoiceNumber: j['invoice_number'] ?? '',
    customerName: j['customer_name'] ?? '',
    status: j['status'] ?? '',
    invoiceDate: j['invoice_date'] ?? '',
    grandTotal: (j['grand_total'] ?? 0).toDouble(),
    pendingAmount: (j['pending_amount'] ?? 0).toDouble(),
  );
}

// ── Customer ─────────────────────────────────────────────
class Customer {
  final int id;
  final String name;
  final String? phone, email, address, gstin, state;

  Customer({
    required this.id, required this.name,
    this.phone, this.email, this.address, this.gstin, this.state,
  });

  factory Customer.fromJson(Map<String, dynamic> j) => Customer(
    id: j['id'] ?? 0,
    name: j['name'] ?? '',
    phone: j['phone'], email: j['email'],
    address: j['address'], gstin: j['gstin'], state: j['state'],
  );

  Map<String, dynamic> toJson() => {
    'name': name, 'phone': phone, 'email': email,
    'address': address, 'gstin': gstin, 'state': state,
  };
}

// ── Supplier ─────────────────────────────────────────────
class Supplier {
  final int id;
  final String name;
  final String? phone, email, gstin, address;

  Supplier({
    required this.id, required this.name,
    this.phone, this.email, this.gstin, this.address,
  });

  factory Supplier.fromJson(Map<String, dynamic> j) => Supplier(
    id: j['id'] ?? 0, name: j['name'] ?? '',
    phone: j['phone'], email: j['email'],
    gstin: j['gstin'], address: j['address'],
  );

  Map<String, dynamic> toJson() => {
    'name': name, 'phone': phone, 'email': email,
    'gstin': gstin, 'address': address,
  };
}

// ── Product ──────────────────────────────────────────────
class Product {
  final int id;
  final String name;
  final double price, gstRate;
  final String? description, hsnCode;

  Product({
    required this.id, required this.name,
    required this.price, required this.gstRate,
    this.description, this.hsnCode,
  });

  factory Product.fromJson(Map<String, dynamic> j) => Product(
    id: j['id'] ?? 0, name: j['name'] ?? '',
    price: (j['price'] ?? 0).toDouble(),
    gstRate: (j['gst_rate'] ?? 18).toDouble(),
    description: j['description'], hsnCode: j['hsn_code'],
  );
}

// ── Material ─────────────────────────────────────────────
class Material {
  final int id;
  final String type, name;
  final String? unit, secondaryUnit, gradeVariation, temp, size, imageUrl;
  final double stockQuantity, stockKg, stockPcs;
  final double? kgPerPcs;

  Material({
    required this.id, required this.type, required this.name,
    this.unit, this.secondaryUnit, this.gradeVariation,
    this.temp, this.size, this.imageUrl,
    required this.stockQuantity, required this.stockKg,
    required this.stockPcs, this.kgPerPcs,
  });

  bool get hasDualUnit => (kgPerPcs ?? 0) > 0;

  factory Material.fromJson(Map<String, dynamic> j) => Material(
    id: j['id'] ?? 0, type: j['type'] ?? '', name: j['name'] ?? '',
    unit: j['unit'], secondaryUnit: j['secondary_unit'],
    gradeVariation: j['grade_variation'],
    temp: j['temp'], size: j['size'],
    imageUrl: j['image_url'],
    stockQuantity: (j['stock_quantity'] ?? 0).toDouble(),
    stockKg:  (j['stock_kg']  ?? j['stock_quantity'] ?? 0).toDouble(),
    stockPcs: (j['stock_pcs'] ?? 0).toDouble(),
    kgPerPcs: j['kg_per_pcs'] != null ? (j['kg_per_pcs']).toDouble() : null,
  );
}

// ── Transporter ──────────────────────────────────────────
class Transporter {
  final int id;
  final String name;
  final String? vehicleNo, phone;

  Transporter({required this.id, required this.name, this.vehicleNo, this.phone});

  factory Transporter.fromJson(Map<String, dynamic> j) => Transporter(
    id: j['id'] ?? 0, name: j['name'] ?? '',
    vehicleNo: j['vehicle_no'], phone: j['phone'],
  );

  Map<String, dynamic> toJson() => {'name': name, 'vehicle_no': vehicleNo, 'phone': phone};
}

// ── Invoice ──────────────────────────────────────────────
class Invoice {
  final int id;
  final String invoiceNumber, status, invoiceDate;
  final String? customerName, paymentMode, lrNumber, notes;
  final int customerId;
  final double subtotal, cgst, sgst, igst, grandTotal, paidAmount, pendingAmount;
  final List<InvoiceItem> items;
  final List<Payment> payments;

  Invoice({
    required this.id, required this.invoiceNumber,
    required this.status, required this.invoiceDate,
    required this.customerId, this.customerName,
    this.paymentMode, this.lrNumber, this.notes,
    required this.subtotal, required this.cgst, required this.sgst,
    required this.igst, required this.grandTotal,
    required this.paidAmount, required this.pendingAmount,
    this.items = const [], this.payments = const [],
  });

  factory Invoice.fromJson(Map<String, dynamic> j) => Invoice(
    id: j['id'] ?? 0,
    invoiceNumber: j['invoice_number'] ?? '',
    status: j['status'] ?? 'Unpaid',
    invoiceDate: j['invoice_date'] ?? '',
    customerId: j['customer_id'] ?? 0,
    customerName: j['customer_name'],
    paymentMode: j['payment_mode'],
    lrNumber: j['lr_number'],
    notes: j['notes'],
    subtotal: (j['subtotal'] ?? 0).toDouble(),
    cgst: (j['cgst'] ?? 0).toDouble(),
    sgst: (j['sgst'] ?? 0).toDouble(),
    igst: (j['igst'] ?? 0).toDouble(),
    grandTotal: (j['grand_total'] ?? 0).toDouble(),
    paidAmount: (j['paid_amount'] ?? 0).toDouble(),
    pendingAmount: (j['pending_amount'] ?? 0).toDouble(),
    items: (j['items'] as List? ?? []).map((e) => InvoiceItem.fromJson(e)).toList(),
    payments: (j['payments'] as List? ?? []).map((e) => Payment.fromJson(e)).toList(),
  );
}

class InvoiceItem {
  final int id, quantity, productId;
  final String? productName, hsnCode;
  final double unitPrice, totalPrice, gstRate;

  InvoiceItem({
    required this.id, required this.quantity, required this.productId,
    this.productName, this.hsnCode,
    required this.unitPrice, required this.totalPrice, required this.gstRate,
  });

  factory InvoiceItem.fromJson(Map<String, dynamic> j) => InvoiceItem(
    id: j['id'] ?? 0, quantity: (j['quantity'] ?? 1).toInt(),
    productId: j['product_id'] ?? 0,
    productName: j['product_name'], hsnCode: j['hsn_code'],
    unitPrice: (j['unit_price'] ?? 0).toDouble(),
    totalPrice: (j['total_price'] ?? 0).toDouble(),
    gstRate: (j['gst_rate'] ?? 0).toDouble(),
  );
}

// ── Payment ──────────────────────────────────────────────
class Payment {
  final int id;
  final double amount;
  final String paymentDate, paymentMode;
  final String? referenceNo, remarks;

  Payment({
    required this.id, required this.amount,
    required this.paymentDate, required this.paymentMode,
    this.referenceNo, this.remarks,
  });

  factory Payment.fromJson(Map<String, dynamic> j) => Payment(
    id: j['id'] ?? 0,
    amount: (j['amount'] ?? 0).toDouble(),
    paymentDate: j['payment_date'] ?? '',
    paymentMode: j['payment_mode'] ?? 'Cash',
    referenceNo: j['reference_no'],
    remarks: j['remarks'],
  );
}

// ── Ledger ───────────────────────────────────────────────
class LedgerEntry {
  final int id;
  final String entityType, type, transactionDate;
  final String? entityName, description, hsnCode;
  final double amount;

  LedgerEntry({
    required this.id, required this.entityType,
    required this.type, required this.transactionDate,
    required this.amount, this.entityName,
    this.description, this.hsnCode,
  });

  factory LedgerEntry.fromJson(Map<String, dynamic> j) => LedgerEntry(
    id: j['id'] ?? 0,
    entityType: j['entity_type'] ?? '',
    type: j['type'] ?? '',
    transactionDate: j['transaction_date'] ?? '',
    amount: (j['amount'] ?? 0).toDouble(),
    entityName: j['entity_name'],
    description: j['description'],
    hsnCode: j['hsn_code'],
  );
}

// ── Production Log ───────────────────────────────────────
class ProductionLog {
  final int id, finalProductQtyPcs;
  final String date;
  final String? rawMaterial, additive, finalProduct, notes;
  final double rawMaterialUsedKg, salvageQtyKg;
  final double? additiveUsedKg;

  ProductionLog({
    required this.id, required this.date,
    this.rawMaterial, this.additive, this.finalProduct, this.notes,
    required this.rawMaterialUsedKg, this.additiveUsedKg,
    required this.finalProductQtyPcs, required this.salvageQtyKg,
  });

  factory ProductionLog.fromJson(Map<String, dynamic> j) => ProductionLog(
    id: j['id'] ?? 0,
    date: j['date'] ?? '',
    rawMaterial: j['raw_material'],
    additive: j['additive'],
    finalProduct: j['final_product'],
    notes: j['notes'],
    rawMaterialUsedKg: (j['raw_material_used_kg'] ?? 0).toDouble(),
    additiveUsedKg: j['additive_used_kg'] != null ? (j['additive_used_kg']).toDouble() : null,
    finalProductQtyPcs: (j['final_product_qty_pcs'] ?? 0).toInt(),
    salvageQtyKg: (j['salvage_qty_kg'] ?? 0).toDouble(),
  );
}
