@extends('layouts.app')

@section('title', 'Reports & Analytics - Shree Giriraj Poly Plast')
@section('page-title', 'Reports & Analytics')

@section('content')
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;">ERP Reports &amp; Financial Summary</h2>
        <p class="text-muted" style="font-size: 13px;">Real-time business performance, sales revenue, procurement, and stock health</p>
    </div>
</div>

{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('reports.index')])

<div class="stats-grid mb-4">
    <a href="{{ route('invoices.index') }}" class="stat-card s-indigo" title="Click to view all Invoices">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-chart-line"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Total Sales Revenue</div>
        <div class="stat-value">₹{{ number_format($totalSales, 2) }}</div>
        <small class="text-muted">{{ $invoiceCount }} Invoices Generated</small>
    </a>

    <a href="{{ route('purchase-orders.index') }}" class="stat-card s-emerald" title="Click to view Purchase Orders">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-cart-flatbed"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Total Procurement Cost</div>
        <div class="stat-value">₹{{ number_format($totalPurchases, 2) }}</div>
        <small class="text-muted">{{ $poCount }} Purchase Orders</small>
    </a>

    <a href="{{ route('invoices.index') }}?status=Unpaid" class="stat-card s-amber" title="Click to view Customer Outstanding">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-hand-holding-dollar"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Accounts Receivable (AR)</div>
        <div class="stat-value">₹{{ number_format($totalAr, 2) }}</div>
        <small class="text-muted">Customer Outstanding Balance</small>
    </a>

    <a href="{{ route('purchase-orders.index') }}" class="stat-card s-rose" title="Click to view Vendor Payables">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-file-invoice-dollar"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Accounts Payable (AP)</div>
        <div class="stat-value">₹{{ number_format($totalAp, 2) }}</div>
        <small class="text-muted">Vendor Payable Balance</small>
    </a>
</div>

<div class="form-row cols-2 mb-4">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-receipt"></i> Recent Sales Invoices</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentInvoices as $inv)
                    <tr>
                        <td><strong>{{ $inv->invoice_number }}</strong></td>
                        <td>{{ $inv->customer->name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}</td>
                        <td style="color: var(--primary); font-weight: 700;">₹{{ number_format($inv->grand_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">No recent invoices</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-boxes-packing"></i> Recent Purchase Orders</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPos as $po)
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
                        <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                        <td>
                            @if($po->status === 'Received')
                                <span class="badge badge-green">Received</span>
                            @else
                                <span class="badge badge-orange">Pending</span>
                            @endif
                        </td>
                        <td style="font-weight: 700;">₹{{ number_format($po->grand_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center">No recent purchase orders</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-triangle-exclamation"></i> Low Stock Alert Materials</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Material Name</th>
                    <th>Type</th>
                    <th>Unit</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockMaterials as $mat)
                <tr>
                    <td><strong>{{ $mat->name }}</strong></td>
                    <td>{{ $mat->type }}</td>
                    <td>{{ $mat->unit }}</td>
                    <td style="color: var(--danger); font-weight: 800;">{{ number_format($mat->stock_quantity, 2) }} {{ $mat->unit }}</td>
                    <td><span class="badge badge-red"><i class="fa fa-arrow-down"></i> Reorder Recommended</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-success">All material stocks are healthy!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
