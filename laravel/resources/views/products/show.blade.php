@extends('layouts.app')
@section('title', $product->name . ' — Product & Stock Overview')
@section('page-title', 'Product Stock Detail')

@section('content')
<style>
    .prod-header-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
        justify-content: space-between;
    }
    .prod-avatar {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
        border: 1.5px solid var(--border);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .prod-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        border: 1.5px dashed var(--border);
    }
    .tab-header {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid var(--border);
        margin-bottom: 20px;
    }
    .tab-btn {
        padding: 10px 18px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }
    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .info-list-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .info-item {
        background: #fafbff;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    .info-item label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 4px;
    }
    .info-item span {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
    }
</style>

<!-- Top Header Navigation -->
<div class="prod-header-card">
    <div class="d-flex align-center gap-4">
        @if($product->image)
            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="prod-avatar">
        @else
            <div class="prod-avatar-placeholder"><i class="fa fa-cube"></i></div>
        @endif
        <div>
            <div class="d-flex align-center gap-2" style="margin-bottom:4px;">
                <h2 style="font-size:20px; font-weight:800; color:var(--text);">{{ $product->name }}</h2>
                <span class="badge badge-purple">{{ $product->sku }}</span>
                @if($product->category)
                    <span class="badge badge-indigo">{{ $product->category->name }}</span>
                @endif
            </div>
            <p style="font-size:13px; color:var(--text-muted); margin:0;">
                {{ $product->product_type }} · Unit: <strong>{{ $product->unit ?: 'PCS' }}</strong> · Barcode: {{ $product->barcode ?: '—' }} · Warehouse: {{ $product->warehouse->name ?? 'Main Plant' }}
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm"><i class="fa fa-arrow-left"></i> Back to Products</a>
        <a href="{{ route('inventory.ledger', ['product_id' => $product->id]) }}" class="btn btn-primary btn-sm"><i class="fa fa-book"></i> Stock Ledger</a>
    </div>
</div>

<!-- Stock & Valuation KPI Cards -->
<div class="stats-grid">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-boxes-stacked"></i></div>
            <span class="badge {{ $product->stock_status == 'In Stock' ? 'badge-success' : ($product->stock_status == 'Low Stock' ? 'badge-warning' : 'badge-danger') }}">
                {{ $product->stock_status }}
            </span>
        </div>
        <div class="stat-label">Current Physical Stock</div>
        <div class="stat-value">{{ number_format($product->stock_quantity, 2) }} <small>{{ $product->unit ?: 'PCS' }}</small></div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-coins"></i></div>
            <span class="badge badge-success">Valuation</span>
        </div>
        <div class="stat-label">Total Inventory Value</div>
        <div class="stat-value">₹{{ number_format($product->inventory_value, 2) }}</div>
    </div>
    <div class="stat-card s-cyan">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-calculator"></i></div>
            <span class="badge badge-indigo">Unit Cost</span>
        </div>
        <div class="stat-label">Weighted Average Cost</div>
        <div class="stat-value">₹{{ number_format($product->average_cost > 0 ? $product->average_cost : $product->purchase_rate, 2) }}</div>
    </div>
    <div class="stat-card s-teal">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-cart-shopping"></i></div>
            <span class="badge badge-purple">Sales Total</span>
        </div>
        <div class="stat-label">Total Quantity Sold</div>
        <div class="stat-value">{{ number_format($totalSalesQty, 2) }} <small>{{ $product->unit ?: 'PCS' }}</small></div>
    </div>
</div>

<!-- Tabs Container -->
<div class="card">
    <div class="card-body">
        <div class="tab-header">
            <button class="tab-btn active" onclick="switchTab('tab-overview', this)"><i class="fa fa-circle-info"></i> Product Info</button>
            <button class="tab-btn" onclick="switchTab('tab-ledger', this)"><i class="fa fa-list-check"></i> Stock Ledger History ({{ $stockLedgers->total() }})</button>
            <button class="tab-btn" onclick="switchTab('tab-sales', this)"><i class="fa fa-file-invoice-dollar"></i> Recent Sales ({{ $salesHistory->count() }})</button>
        </div>

        <!-- Tab 1: Product Info & Pricing Specs -->
        <div id="tab-overview" class="tab-content active">
            <h4 style="font-size:14px; font-weight:700; margin-bottom:14px; color:var(--text);">Product &amp; Pricing Specifications</h4>
            <div class="info-list-grid">
                <div class="info-item"><label>Sales Rate (Price)</label><span>₹{{ number_format($product->price, 2) }}</span></div>
                <div class="info-item"><label>Purchase Rate</label><span>₹{{ number_format($product->purchase_rate, 2) }}</span></div>
                <div class="info-item"><label>Wholesale Rate</label><span>₹{{ number_format($product->wholesale_rate, 2) }}</span></div>
                <div class="info-item"><label>MRP</label><span>₹{{ number_format($product->mrp, 2) }}</span></div>
                <div class="info-item"><label>GST Rate</label><span>{{ number_format($product->gst_rate) }}%</span></div>
                <div class="info-item"><label>HSN Code</label><span>{{ $product->hsn_code ?: '3923' }}</span></div>
                <div class="info-item"><label>Reorder Level</label><span>{{ number_format($product->reorder_level, 2) }} {{ $product->unit ?: 'PCS' }}</span></div>
                <div class="info-item"><label>Minimum Stock</label><span>{{ number_format($product->minimum_stock, 2) }} {{ $product->unit ?: 'PCS' }}</span></div>
                <div class="info-item"><label>Piece Weight</label><span>{{ $product->weight_per_piece ? $product->weight_per_piece . ' ' . $product->weight_unit : '—' }}</span></div>
                <div class="info-item"><label>Wastage Allowance</label><span>{{ $product->wastage_percentage ? $product->wastage_percentage . '%' : '2%' }}</span></div>
                <div class="info-item"><label>Job Work Ready</label><span>{{ $product->job_work_applicable ? '✅ Yes' : 'No' }}</span></div>
                <div class="info-item"><label>Created Date</label><span>{{ $product->created_at ? $product->created_at->format('d M Y') : '—' }}</span></div>
            </div>

            @if($product->description)
            <div style="margin-top:20px; padding:14px; background:#f8fafc; border-radius:8px; border:1px solid var(--border);">
                <label style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Description &amp; Mold Notes</label>
                <p style="font-size:13.5px; color:var(--text); margin-top:4px; line-height:1.5;">{{ $product->description }}</p>
            </div>
            @endif
        </div>

        <!-- Tab 2: Stock Ledger History -->
        <div id="tab-ledger" class="tab-content">
            @if($stockLedgers->isEmpty())
                <div class="empty-state">
                    <p>No stock ledger movements recorded yet.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ref #</th>
                                <th>Movement Type</th>
                                <th>Quantity IN</th>
                                <th>Quantity OUT</th>
                                <th>Rate</th>
                                <th>Amount</th>
                                <th>Running Balance</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($stockLedgers as $l)
                        <tr>
                            <td>{{ $l->transaction_date ? $l->transaction_date->format('d-m-Y') : '—' }}</td>
                            <td><code>{{ $l->reference_number ?: '—' }}</code></td>
                            <td>
                                <span class="badge {{ $l->quantity_in > 0 ? 'badge-success' : 'badge-danger' }}" style="font-size:10.5px;">
                                    {{ $l->transaction_type }}
                                </span>
                            </td>
                            <td class="text-success fw-bold">{{ $l->quantity_in > 0 ? '+' . number_format($l->quantity_in, 2) : '—' }}</td>
                            <td class="text-danger fw-bold">{{ $l->quantity_out > 0 ? '-' . number_format($l->quantity_out, 2) : '—' }}</td>
                            <td>₹{{ number_format($l->rate, 2) }}</td>
                            <td>₹{{ number_format($l->amount, 2) }}</td>
                            <td class="fw-bold" style="color:var(--primary);">{{ number_format($l->new_stock, 2) }} {{ $l->unit }}</td>
                            <td style="font-size:12px; color:var(--text-muted);">{{ $l->remarks ?: '—' }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding-top:16px;">
                    {{ $stockLedgers->links() }}
                </div>
            @endif
        </div>

        <!-- Tab 3: Sales History -->
        <div id="tab-sales" class="tab-content">
            @if($salesHistory->isEmpty())
                <div class="empty-state">
                    <p>No sales invoices generated for this product yet.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($salesHistory as $sh)
                        <tr>
                            <td><code>{{ $sh->invoice->invoice_number ?? '—' }}</code></td>
                            <td>{{ $sh->invoice && $sh->invoice->invoice_date ? \Carbon\Carbon::parse($sh->invoice->invoice_date)->format('d-m-Y') : '—' }}</td>
                            <td>{{ $sh->invoice->customer->name ?? '—' }}</td>
                            <td class="fw-bold">{{ number_format($sh->quantity, 2) }}</td>
                            <td>₹{{ number_format($sh->unit_price, 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($sh->total_price, 2) }}</td>
                            <td>
                                @if($sh->invoice)
                                    <a href="{{ route('invoices.show', $sh->invoice) }}" class="btn btn-outline btn-sm btn-icon" title="View Invoice">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}
</script>
@endsection
