export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'staff' | string;
  is_active?: boolean;
}

export interface Customer {
  id: number;
  name: string;
  phone?: string;
  email?: string;
  address?: string;
  gstin?: string;
  state?: string;
  created_at?: string;
}

export interface Product {
  id: number;
  name: string;
  description?: string;
  price: number;
  hsn_code?: string;
  gst_rate: number;
}

export interface Material {
  id: number;
  type: 'Raw Material' | 'Additive' | 'Final Product';
  name: string;
  unit?: string;
  grade_variation?: string;
  temp?: string;
  size?: string;
  stock_quantity: number;
}

export interface Supplier {
  id: number;
  name: string;
  phone?: string;
  email?: string;
  address?: string;
  gstin?: string;
}

export interface InvoiceItem {
  id?: number;
  product_id: number;
  product_name?: string;
  hsn_code?: string;
  quantity: number;
  unit_price: number;
  total_price: number;
  gst_rate?: number;
}

export interface Invoice {
  id: number;
  invoice_number: string;
  invoice_date: string;
  customer_id: number;
  customer_name?: string;
  customer?: Customer;
  transporter_id?: number;
  lr_number?: string;
  subtotal: number;
  cgst: number;
  sgst: number;
  igst: number;
  grand_total: number;
  paid_amount: number;
  pending_amount?: number;
  status: 'Paid' | 'Unpaid' | 'Partial' | string;
  payment_mode?: string;
  payment_terms?: string;
  po_number?: string;
  notes?: string;
  items?: InvoiceItem[];
  payments?: Payment[];
}

export interface Payment {
  id: number;
  invoice_id: number;
  invoice_number?: string;
  amount: number;
  payment_date: string;
  payment_mode: string;
  reference_no?: string;
  remarks?: string;
}

export interface PurchaseOrder {
  id: number;
  po_number: string;
  po_date: string;
  expected_delivery_date?: string;
  supplier_id: number;
  supplier_name?: string;
  supplier?: Supplier;
  grand_total: number;
  status: string;
  payment_terms?: string;
  delivery_address?: string;
  notes?: string;
  items?: Array<{
    id?: number;
    material_id: number;
    material_name?: string;
    unit?: string;
    quantity: number;
    unit_price: number;
    total_price: number;
    received_qty?: number;
  }>;
}

export interface DashboardStats {
  stats: {
    customers: number;
    products: number;
    suppliers: number;
    invoices: number;
    total_revenue: number;
    production_runs: number;
    low_stock_items: number;
  };
  recent_invoices: Array<{
    id: number;
    invoice_number: string;
    customer_name: string;
    grand_total: number;
    status: string;
    invoice_date: string;
  }>;
  low_stock: Array<{
    id: number;
    name: string;
    type: string;
    stock_quantity: number;
    unit: string;
  }>;
}
