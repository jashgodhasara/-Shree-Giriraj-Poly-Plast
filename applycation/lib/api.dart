import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'models.dart';

class Api {
  static Future<Map<String, dynamic>> _get(String path) async {
    final r = await http.get(Uri.parse('$kBaseUrl$path'), headers: kHeaders)
        .timeout(const Duration(seconds: 15));
    if (r.statusCode == 200) return json.decode(r.body);
    throw Exception('GET $path failed: ${r.statusCode}');
  }

  static Future<List<dynamic>> _getList(String path) async {
    final r = await http.get(Uri.parse('$kBaseUrl$path'), headers: kHeaders)
        .timeout(const Duration(seconds: 15));
    if (r.statusCode == 200) return json.decode(r.body);
    throw Exception('GET $path failed: ${r.statusCode}');
  }

  static Future<Map<String, dynamic>> _post(String path, Map body) async {
    final r = await http.post(Uri.parse('$kBaseUrl$path'),
        headers: kHeaders, body: json.encode(body))
        .timeout(const Duration(seconds: 15));
    return json.decode(r.body);
  }

  static Future<Map<String, dynamic>> _put(String path, Map body) async {
    final r = await http.put(Uri.parse('$kBaseUrl$path'),
        headers: kHeaders, body: json.encode(body))
        .timeout(const Duration(seconds: 15));
    return json.decode(r.body);
  }

  static Future<Map<String, dynamic>> _delete(String path) async {
    final r = await http.delete(Uri.parse('$kBaseUrl$path'), headers: kHeaders)
        .timeout(const Duration(seconds: 15));
    return json.decode(r.body);
  }

  // Dashboard
  static Future<DashboardStats> getDashboard() async =>
      DashboardStats.fromJson(await _get('/dashboard'));

  // Customers
  static Future<List<Customer>> getCustomers() async =>
      (await _getList('/customers')).map((e) => Customer.fromJson(e)).toList();
  static Future<Map> saveCustomer(Map data, {int? id}) async =>
      id == null ? _post('/customers', data) : _put('/customers/$id', data);
  static Future<Map> deleteCustomer(int id) => _delete('/customers/$id');

  // Suppliers
  static Future<List<Supplier>> getSuppliers() async =>
      (await _getList('/suppliers')).map((e) => Supplier.fromJson(e)).toList();
  static Future<Map> saveSupplier(Map data, {int? id}) async =>
      id == null ? _post('/suppliers', data) : _put('/suppliers/$id', data);
  static Future<Map> deleteSupplier(int id) => _delete('/suppliers/$id');

  // Products
  static Future<List<Product>> getProducts() async =>
      (await _getList('/products')).map((e) => Product.fromJson(e)).toList();
  static Future<Map> saveProduct(Map data, {int? id}) async =>
      id == null ? _post('/products', data) : _put('/products/$id', data);
  static Future<Map> deleteProduct(int id) => _delete('/products/$id');

  // Materials
  static Future<List<Material>> getMaterials() async =>
      (await _getList('/materials')).map((e) => Material.fromJson(e)).toList();
  static Future<Map> saveMaterial(Map data, {int? id}) async =>
      id == null ? _post('/materials', data) : _put('/materials/$id', data);
  static Future<Map> deleteMaterial(int id) => _delete('/materials/$id');

  // Transporters
  static Future<List<Transporter>> getTransporters() async =>
      (await _getList('/transporters')).map((e) => Transporter.fromJson(e)).toList();
  static Future<Map> saveTransporter(Map data, {int? id}) async =>
      id == null ? _post('/transporters', data) : _put('/transporters/$id', data);
  static Future<Map> deleteTransporter(int id) => _delete('/transporters/$id');

  // Invoices
  static Future<List<Invoice>> getInvoices() async =>
      (await _getList('/invoices')).map((e) => Invoice.fromJson(e)).toList();
  static Future<Invoice> getInvoice(int id) async =>
      Invoice.fromJson(await _get('/invoices/$id'));
  static Future<Map> createInvoice(Map data) => _post('/invoices', data);
  static Future<Map> deleteInvoice(int id) => _delete('/invoices/$id');

  // Payments
  static Future<Map> addPayment(Map data) => _post('/payments', data);

  // Ledger
  static Future<List<LedgerEntry>> getLedger() async =>
      (await _getList('/ledger')).map((e) => LedgerEntry.fromJson(e)).toList();
  static Future<Map> addLedger(Map data) => _post('/ledger', data);
  static Future<Map> deleteLedger(int id) => _delete('/ledger/$id');

  // Material Transactions
  static Future<List<MatTxn>> getMatTxns() async =>
      (await _getList('/material-transactions')).map((e) => MatTxn.fromJson(e)).toList();
  static Future<Map> addMatTxn(Map data) => _post('/material-transactions', data);
  static Future<Map> deleteMatTxn(int id) => _delete('/material-transactions/$id');

  // Production
  static Future<List<dynamic>> getProduction() => _getList('/production');
  static Future<Map> addProduction(Map data) => _post('/production', data);
}
