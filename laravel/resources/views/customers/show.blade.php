@extends('layouts.app')
@section('title', $customer->name . ' - Customer Details & Sales')
@section('page-title', 'Customer 360° Profile')

@section('content')
<style>
.profile-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);
    border-radius: var(--radius);
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}
.profile-banner::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(0,0,0,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.profile-header-wrap {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}
.profile-main-info {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}
.profile-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 18px;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    cursor: pointer;
    background: #1e293b;
}
.profile-initial-large {
    width: 80px;
    height: 80px;
    border-radius: 18px;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: #fff;
    font-size: 32px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
}
.profile-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px 24px;
    margin-top: 18px;
    padding-top: 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    position: relative;
    z-index: 1;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #e2e8f0;
    font-size: 13.5px;
}
.meta-item i {
    color: #818cf8;
    font-size: 15px;
    width: 18px;
    text-align: center;
}
.meta-item a {
    color: #e2e8f0;
    text-decoration: none;
    transition: color 0.15s;
}
.meta-item a:hover {
    color: #a5b4fc;
    text-decoration: underline;
}
.gst-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(99, 102, 241, 0.25);
    border: 1px solid rgba(129, 140, 248, 0.4);
    padding: 3px 10px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 13px;
    color: #c7d2fe;
    font-weight: 600;
}

/* Stat KPI Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}
.stat-card-custom {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
}
.stat-card-custom:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}
.stat-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.stat-icon-sales {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(79, 70, 229, 0.25));
    color: #4f46e5;
}
.stat-icon-bills {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.25));
    color: #2563eb;
}
.stat-icon-paid {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.25));
    color: #059669;
}
.stat-icon-pending {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.25));
    color: #dc2626;
}
.stat-val-text {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.2;
}
.stat-lbl-text {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

/* Tabs Styling */
.nav-tabs-custom {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
    padding-bottom: 2px;
    overflow-x: auto;
}
.tab-btn-custom {
    padding: 10px 18px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-weight: 600;
    font-size: 13.5px;
    color: var(--text-muted);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px 6px 0 0;
    transition: all 0.2s;
    white-space: nowrap;
}
.tab-btn-custom:hover {
    color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}
.tab-btn-custom.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: rgba(99, 102, 241, 0.08);
}
.tab-badge {
    padding: 2px 7px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    background: #e2e8f0;
    color: #475569;
}
.tab-btn-custom.active .tab-badge {
    background: var(--primary);
    color: #fff;
}
.tab-pane {
    display: none;
}
.tab-pane.active {
    display: block;
    animation: fadeIn 0.25s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Invoices list filter */
.filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
}
.status-pill.paid { background: #dcfce7; color: #166534; }
.status-pill.partial { background: #fef9c3; color: #854d0e; }
.status-pill.unpaid { background: #fee2e2; color: #991b1b; }

@media (max-width: 768px) {
    .profile-banner { padding: 20px; }
    .profile-header-wrap { flex-direction: column; }
    .profile-meta-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Top Breadcrumbs & Back Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
    <div style="font-size:13px; color:var(--text-muted); display:flex; align-items:center; gap:8px;">
        <a href="{{ route('customers.index') }}" style="color:var(--primary); text-decoration:none; font-weight:600;">
            <i class="fa fa-arrow-left"></i> Customers List
        </a>
        <span>/</span>
        <span style="font-weight:600; color:var(--text)">{{ $customer->name }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus-circle"></i> Create New Bill / Invoice
        </a>
        <button class="btn btn-outline btn-sm" onclick="openRecordPaymentModal()">
            <i class="fa fa-money-bill-wave"></i> Record Payment
        </button>
        <button class="btn btn-outline btn-sm" onclick="editCustomer({{ $customer->id }}, {{ json_encode($customer->name) }}, {{ json_encode($customer->phone) }}, {{ json_encode($customer->email) }}, {{ json_encode($customer->address) }}, {{ json_encode($customer->gstin) }}, {{ json_encode($customer->state) }}, {{ json_encode($customer->image ? asset($customer->image) : null) }})">
            <i class="fa fa-pen"></i> Edit Profile
        </button>
        <button class="btn btn-outline btn-sm" onclick="window.print()">
            <i class="fa fa-print"></i> Print Statement
        </button>
    </div>
</div>

<!-- Customer Profile Banner -->
<div class="profile-banner">
    <div class="profile-header-wrap">
        <div class="profile-main-info">
            @if($customer->image)
                <img src="{{ asset($customer->image) }}" alt="{{ $customer->name }}" class="profile-avatar-large" onclick="viewPhoto('{{ asset($customer->image) }}', '{{ e($customer->name) }}')">
            @else
                <div class="profile-initial-large">{{ strtoupper(substr($customer->name, 0, 1)) }}</div>
            @endif
            <div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:4px;">
                    <h2 style="font-size:24px; font-weight:800; color:#fff; margin:0;">{{ $customer->name }}</h2>
                    <span style="background:rgba(16, 185, 129, 0.2); border:1px solid rgba(16, 185, 129, 0.4); color:#6ee7b7; font-size:11.5px; font-weight:700; padding:2px 8px; border-radius:12px;">
                        <i class="fa fa-circle-check"></i> Active Customer
                    </span>
                    <span style="font-size:12px; color:#94a3b8;">Customer ID: #{{ $customer->id }}</span>
                </div>
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    @if($customer->gstin)
                        <div class="gst-tag">
                            <i class="fa fa-receipt" style="color:#818cf8;"></i> GSTIN: {{ $customer->gstin }}
                        </div>
                    @endif
                    @if($customer->state)
                        <span style="color:#cbd5e1; font-size:13px; font-weight:500;">
                            <i class="fa fa-location-dot" style="color:#f59e0b;"></i> {{ $customer->state }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div style="text-align:right;">
            <div style="font-size:12px; color:#94a3b8; margin-bottom:4px;">LIFETIME SALES REVENUE</div>
            <div style="font-size:26px; font-weight:800; color:#38bdf8;">₹{{ number_format($totalSales, 2) }}</div>
            <div style="font-size:12px; color:#a5b4fc;">{{ $invoiceCount }} Bills Generated</div>
        </div>
    </div>

    <!-- Meta Details Row -->
    <div class="profile-meta-grid">
        <div class="meta-item">
            <i class="fa fa-phone"></i>
            <div>
                <div style="font-size:11px; color:#94a3b8;">Phone Number</div>
                @if($customer->phone)
                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                @else
                    <span style="color:#64748b;">Not provided</span>
                @endif
            </div>
        </div>
        <div class="meta-item">
            <i class="fa fa-envelope"></i>
            <div>
                <div style="font-size:11px; color:#94a3b8;">Email Address</div>
                @if($customer->email)
                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                @else
                    <span style="color:#64748b;">Not provided</span>
                @endif
            </div>
        </div>
        <div class="meta-item" style="grid-column: span 2;">
            <i class="fa fa-map-marker-alt"></i>
            <div>
                <div style="font-size:11px; color:#94a3b8;">Billing / Registered Address</div>
                <div style="font-size:13px;">{{ $customer->address ?: 'No address specified' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- 4 Key Stat / KPI Cards -->
<div class="stats-grid">
    <div class="stat-card-custom">
        <div class="stat-icon-wrap stat-icon-sales">
            <i class="fa fa-chart-line"></i>
        </div>
        <div>
            <div class="stat-lbl-text">આજ સુધીનું કુલ વેચાણ (Total Sales)</div>
            <div class="stat-val-text" style="color:var(--primary)">₹{{ number_format($totalSales, 2) }}</div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Across {{ $invoiceCount }} Invoices</div>
        </div>
    </div>

    <div class="stat-card-custom">
        <div class="stat-icon-wrap stat-icon-bills">
            <i class="fa fa-file-invoice"></i>
        </div>
        <div>
            <div class="stat-lbl-text">કુલ બિલ (Invoices Count)</div>
            <div class="stat-val-text">{{ $invoiceCount }} <span style="font-size:14px; font-weight:500; color:var(--text-muted);">Bills</span></div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                <span style="color:#16a34a; font-weight:600;">{{ $paidCount }} Paid</span> • 
                <span style="color:#ca8a04; font-weight:600;">{{ $partialCount }} Part</span> • 
                <span style="color:#dc2626; font-weight:600;">{{ $unpaidCount }} Due</span>
            </div>
        </div>
    </div>

    <div class="stat-card-custom">
        <div class="stat-icon-wrap stat-icon-paid">
            <i class="fa fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-lbl-text">કુલ મળેલ રકમ (Total Paid)</div>
            <div class="stat-val-text" style="color:#059669">₹{{ number_format($totalPaid, 2) }}</div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Received Payments</div>
        </div>
    </div>

    <div class="stat-card-custom" style="{{ $totalPending > 0 ? 'border-color:rgba(239,68,68,0.4); background:#fff5f5;' : '' }}">
        <div class="stat-icon-wrap stat-icon-pending">
            <i class="fa fa-hand-holding-dollar"></i>
        </div>
        <div>
            <div class="stat-lbl-text">કુલ બાકી રકમ (Balance Due)</div>
            <div class="stat-val-text" style="{{ $totalPending > 0 ? 'color:#dc2626;' : 'color:#16a34a;' }}">
                ₹{{ number_format($totalPending, 2) }}
            </div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                {{ $totalPending > 0 ? 'Outstanding Payment' : 'All Clear / No Dues' }}
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="nav-tabs-custom">
    <button class="tab-btn-custom active" onclick="switchTab('tab-invoices', this)">
        <i class="fa fa-file-invoice-dollar"></i> બધા બિલ (All Invoices &amp; Bills)
        <span class="tab-badge">{{ $invoices->count() }}</span>
    </button>
    <button class="tab-btn-custom" onclick="switchTab('tab-products', this)">
        <i class="fa fa-boxes-stacked"></i> ઓર્ડર્સ અને પ્રોડક્ટ્સ હિસ્ટ્રી (Purchased Items)
        <span class="tab-badge">{{ $productPurchases->count() }}</span>
    </button>
    <button class="tab-btn-custom" onclick="switchTab('tab-payments', this)">
        <i class="fa fa-money-bill-transfer"></i> ચુકવણી ઇતિહાસ (Payment History)
        <span class="tab-badge">{{ $payments->count() }}</span>
    </button>
    <button class="tab-btn-custom" onclick="switchTab('tab-ledger', this)">
        <i class="fa fa-book-bookmark"></i> ખાતાવહી (Account Ledger)
        <span class="tab-badge">{{ $ledgers->count() }}</span>
    </button>
</div>

<!-- TAB 1: ALL INVOICES & BILLS -->
<div id="tab-invoices" class="tab-pane active">
    <div class="card">
        <div class="card-header" style="flex-wrap:wrap; gap:12px;">
            <div class="d-flex align-items-center gap-2">
                <h3 style="margin:0;"><i class="fa fa-file-lines" style="color:var(--primary)"></i> Bills &amp; Invoices for {{ $customer->name }}</h3>
            </div>
            <div class="d-flex gap-2 align-items-center" style="flex-wrap:wrap;">
                <input type="text" id="invoiceSearchInput" placeholder="Search invoice #, items..." onkeyup="filterInvoices()" style="padding:6px 12px; font-size:13px; border:1px solid var(--border); border-radius:6px; width:220px;">
                <select id="invoiceStatusFilter" onchange="filterInvoices()" style="padding:6px 10px; font-size:13px; border:1px solid var(--border); border-radius:6px;">
                    <option value="">All Statuses</option>
                    <option value="Paid">Paid</option>
                    <option value="Partial">Partial</option>
                    <option value="Unpaid">Unpaid</option>
                </select>
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> New Bill
                </a>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($invoices->isEmpty())
                <div class="empty-state" style="padding:40px;">
                    <i class="fa fa-file-invoice" style="font-size:42px; color:var(--text-muted); margin-bottom:12px;"></i>
                    <h4>No Bills Created Yet</h4>
                    <p style="color:var(--text-muted); margin-bottom:16px;">This customer does not have any sales invoices yet.</p>
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create First Bill for {{ $customer->name }}
                    </a>
                </div>
            @else
                <div class="table-wrap">
                    <table id="customerInvoicesTable">
                        <thead>
                            <tr>
                                <th># Invoice No</th>
                                <th>Date</th>
                                <th>Items Ordered</th>
                                <th>Subtotal</th>
                                <th>GST (Tax)</th>
                                <th>Grand Total</th>
                                <th>Paid</th>
                                <th>Balance Due</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                            @php
                                $invPending = max(0, $inv->grand_total - $inv->paid_amount);
                                $totalGst = ($inv->cgst ?? 0) + ($inv->sgst ?? 0) + ($inv->igst ?? 0);
                            @endphp
                            <tr data-status="{{ $inv->status }}" data-search="{{ strtolower($inv->invoice_number . ' ' . $inv->items->pluck('product.name')->join(' ')) }}">
                                <td>
                                    <a href="{{ route('invoices.show', $inv) }}" class="fw-bold" style="color:var(--primary); text-decoration:none; font-family:monospace; font-size:13.5px;">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : '—' }}</div>
                                    <small style="color:var(--text-muted); font-size:11px;">{{ $inv->invoice_date ? $inv->invoice_date->diffForHumans() : '' }}</small>
                                </td>
                                <td>
                                    @if($inv->items->count() > 0)
                                        <div style="font-size:12.5px; font-weight:600; color:var(--text);">
                                            {{ $inv->items->first()->product?->name ?? 'Item' }}
                                            @if($inv->items->count() > 1)
                                                <span class="badge" style="background:#e2e8f0; color:#334155; font-size:11px; padding:1px 6px;">+{{ $inv->items->count() - 1 }} more</span>
                                            @endif
                                        </div>
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            {{ $inv->items->sum('quantity') }} total units
                                        </div>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td>₹{{ number_format($inv->subtotal, 2) }}</td>
                                <td>
                                    @if($totalGst > 0)
                                        <span style="font-size:12px; color:var(--text-muted);">₹{{ number_format($totalGst, 2) }}</span>
                                    @else
                                        <span style="color:var(--text-muted);">₹0.00</span>
                                    @endif
                                </td>
                                <td class="fw-bold" style="font-size:14px; color:var(--primary);">
                                    ₹{{ number_format($inv->grand_total, 2) }}
                                </td>
                                <td style="color:#16a34a; font-weight:600;">
                                    ₹{{ number_format($inv->paid_amount, 2) }}
                                </td>
                                <td style="font-weight:700; color:{{ $invPending > 0 ? '#dc2626' : '#16a34a' }};">
                                    ₹{{ number_format($invPending, 2) }}
                                </td>
                                <td>
                                    @if($inv->status === 'Paid')
                                        <span class="status-pill paid"><i class="fa fa-check-circle"></i> Paid</span>
                                    @elseif($inv->status === 'Partial')
                                        <span class="status-pill partial"><i class="fa fa-clock"></i> Partial</span>
                                    @else
                                        <span class="status-pill unpaid"><i class="fa fa-circle-exclamation"></i> Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline btn-sm btn-icon" title="View Invoice">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.print', $inv) }}" class="btn btn-outline btn-sm btn-icon" title="Print Invoice" target="_blank">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <a href="{{ route('invoices.challan', $inv) }}" class="btn btn-outline btn-sm btn-icon" title="Print Delivery Challan" target="_blank">
                                            <i class="fa fa-truck"></i>
                                        </a>
                                        @if($invPending > 0)
                                            <button class="btn btn-outline btn-sm btn-icon" title="Add Payment" style="color:#059669; border-color:rgba(16,185,129,0.4);" onclick="openQuickPayModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $invPending }})">
                                                <i class="fa fa-wallet"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:700; border-top:2px solid var(--border);">
                                <td colspan="3" style="text-align:right;">Total (બધા બિલનું કુલ સરવાળો):</td>
                                <td>₹{{ number_format($invoices->sum('subtotal'), 2) }}</td>
                                <td>₹{{ number_format($invoices->sum('cgst') + $invoices->sum('sgst') + $invoices->sum('igst'), 2) }}</td>
                                <td style="color:var(--primary); font-size:15px;">₹{{ number_format($totalSales, 2) }}</td>
                                <td style="color:#16a34a; font-size:15px;">₹{{ number_format($totalPaid, 2) }}</td>
                                <td style="color:{{ $totalPending > 0 ? '#dc2626' : '#16a34a' }}; font-size:15px;">₹{{ number_format($totalPending, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- TAB 2: PURCHASED PRODUCTS & ORDERS SUMMARY -->
<div id="tab-products" class="tab-pane">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-boxes-stacked" style="color:var(--primary)"></i> Products &amp; Items Purchased by {{ $customer->name }}</h3>
        </div>
        <div class="card-body" style="padding:0;">
            @if($productPurchases->isEmpty())
                <div class="empty-state" style="padding:40px;">
                    <i class="fa fa-box-open" style="font-size:36px; color:var(--text-muted); margin-bottom:10px;"></i>
                    <p>No products purchased yet.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>HSN Code</th>
                                <th>Total Quantity Purchased</th>
                                <th>Total Sales Value</th>
                                <th>Order Frequency</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productPurchases as $idx => $prod)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="fw-bold" style="font-size:14px; color:var(--text);">{{ $prod->product_name }}</td>
                                <td><code>{{ $prod->hsn_code }}</code></td>
                                <td class="fw-bold" style="color:var(--primary);">
                                    {{ number_format($prod->total_qty) }} {{ $prod->unit }}
                                </td>
                                <td class="fw-bold" style="color:#059669; font-size:14px;">
                                    ₹{{ number_format($prod->total_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge" style="background:#e0e7ff; color:#3730a3; padding:4px 8px; border-radius:6px; font-weight:600;">
                                        <i class="fa fa-receipt"></i> In {{ $prod->orders_count }} Bill(s)
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:700; border-top:2px solid var(--border);">
                                <td colspan="3" style="text-align:right;">Grand Total:</td>
                                <td style="color:var(--primary);">{{ number_format($productPurchases->sum('total_qty')) }} Total Units</td>
                                <td style="color:#059669; font-size:15px;">₹{{ number_format($productPurchases->sum('total_amount'), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- TAB 3: PAYMENT HISTORY -->
<div id="tab-payments" class="tab-pane">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-money-bill-wave" style="color:#10b981"></i> Payment Transactions &amp; Receipts</h3>
            <button class="btn btn-primary btn-sm" onclick="openRecordPaymentModal()">
                <i class="fa fa-plus"></i> Record Payment
            </button>
        </div>
        <div class="card-body" style="padding:0;">
            @if($payments->isEmpty())
                <div class="empty-state" style="padding:40px;">
                    <i class="fa fa-receipt" style="font-size:36px; color:var(--text-muted); margin-bottom:10px;"></i>
                    <p>No payment transactions recorded yet.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payment Date</th>
                                <th>Invoice #</th>
                                <th>Amount Paid</th>
                                <th>Payment Mode</th>
                                <th>Reference / Txn #</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $idx => $p)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="fw-bold">{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d M Y') : '—' }}</td>
                                <td>
                                    @if($p->invoice)
                                        <a href="{{ route('invoices.show', $p->invoice) }}" style="color:var(--primary); font-family:monospace; font-weight:600; text-decoration:none;">
                                            {{ $p->invoice->invoice_number }}
                                        </a>
                                    @else
                                        <span style="color:var(--text-muted);">Direct Payment</span>
                                    @endif
                                </td>
                                <td class="fw-bold" style="color:#059669; font-size:14px;">
                                    ₹{{ number_format($p->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9; color:#334155; font-weight:600; padding:4px 8px;">
                                        {{ $p->payment_mode ?? 'Cash' }}
                                    </span>
                                </td>
                                <td>{{ $p->reference_number ?? '—' }}</td>
                                <td style="font-size:12.5px; color:var(--text-muted);">{{ $p->notes ?? $p->remarks ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:700; border-top:2px solid var(--border);">
                                <td colspan="3" style="text-align:right;">Total Payments Received:</td>
                                <td style="color:#059669; font-size:15px;">₹{{ number_format($payments->sum('amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- TAB 4: ACCOUNT LEDGER -->
<div id="tab-ledger" class="tab-pane">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-book-bookmark" style="color:var(--primary)"></i> Account Ledger Statement</h3>
            <a href="{{ route('ledger.index') }}?entity_type=Customer&entity_id={{ $customer->id }}" class="btn btn-outline btn-sm">
                <i class="fa fa-arrow-up-right-from-square"></i> Open in Full Ledger
            </a>
        </div>
        <div class="card-body" style="padding:0;">
            @if($ledgers->isEmpty())
                <div class="empty-state" style="padding:40px;">
                    <i class="fa fa-book" style="font-size:36px; color:var(--text-muted); margin-bottom:10px;"></i>
                    <p>No ledger entries found for this customer.</p>
                </div>
            @else
                @php $runningBalance = 0; @endphp
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Particulars / Narration</th>
                                <th>Type</th>
                                <th>Debit (Dr)</th>
                                <th>Credit (Cr)</th>
                                <th>Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ledgers as $entry)
                            @php
                                if ($entry->type === 'Debit') {
                                    $runningBalance += $entry->amount;
                                } else {
                                    $runningBalance -= $entry->amount;
                                }
                            @endphp
                            <tr>
                                <td>{{ $entry->transaction_date ? \Carbon\Carbon::parse($entry->transaction_date)->format('d M Y') : '—' }}</td>
                                <td>
                                    <div style="font-weight:600;">{{ $entry->description ?: 'Transaction' }}</div>
                                    @if($entry->hsn_code)<small style="color:var(--text-muted);">HSN: {{ $entry->hsn_code }}</small>@endif
                                </td>
                                <td>
                                    <span class="badge" style="{{ $entry->type === 'Debit' ? 'background:#fee2e2;color:#991b1b;' : 'background:#dcfce7;color:#166534;' }} font-weight:700;">
                                        {{ $entry->type }}
                                    </span>
                                </td>
                                <td style="font-weight:600; color:{{ $entry->type === 'Debit' ? '#dc2626' : 'inherit' }};">
                                    {{ $entry->type === 'Debit' ? '₹' . number_format($entry->amount, 2) : '—' }}
                                </td>
                                <td style="font-weight:600; color:{{ $entry->type === 'Credit' ? '#16a34a' : 'inherit' }};">
                                    {{ $entry->type === 'Credit' ? '₹' . number_format($entry->amount, 2) : '—' }}
                                </td>
                                <td class="fw-bold" style="color:{{ $runningBalance > 0 ? '#dc2626' : '#16a34a' }};">
                                    ₹{{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}
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

<!-- Record Payment Modal -->
<div class="modal-overlay" id="recordPaymentModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa fa-money-bill-wave" style="color:var(--primary)"></i> Record Payment for {{ $customer->name }}</h3>
            <button class="modal-close" onclick="closeModal('recordPaymentModal')">✕</button>
        </div>
        <form id="recordPaymentForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Invoice / Bill *</label>
                    <select name="invoice_id" id="pay_invoice_id" required onchange="onInvoiceSelectChange(this)">
                        <option value="">-- Choose Invoice --</option>
                        @foreach($invoices as $inv)
                            @php $bal = max(0, $inv->grand_total - $inv->paid_amount); @endphp
                            <option value="{{ $inv->id }}" data-balance="{{ $bal }}" {{ $bal <= 0 ? 'disabled' : '' }}>
                                {{ $inv->invoice_number }} (Total: ₹{{ number_format($inv->grand_total, 2) }} | Due: ₹{{ number_format($bal, 2) }}) {{ $bal <= 0 ? '— [PAID]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Amount (₹) *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="pay_amount" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Payment Mode *</label>
                        <select name="payment_mode" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer / NEFT / RTGS</option>
                            <option value="UPI">UPI / GPay / PhonePe</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Reference / Cheque / Txn #</label>
                        <input type="text" name="reference_number" placeholder="e.g. UTR / Chq No.">
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="Optional payment remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('recordPaymentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal-overlay" id="editCustomerModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Customer Profile</h3>
            <button class="modal-close" onclick="closeModal('editCustomerModal')">✕</button>
        </div>
        <form id="editCustomerForm" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN</label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="edit_gstin" name="gstin" maxlength="15" style="text-transform:uppercase" value="{{ $customer->gstin }}">
                            <button type="button" id="edit_gst_btn" class="btn btn-outline btn-sm" onclick="verifyGst('edit')" style="white-space:nowrap;padding:0 12px;">
                                <i class="fa-solid fa-bolt" style="color:var(--primary)"></i> Verify
                            </button>
                        </div>
                        <div id="edit_gst_status" style="font-size:12px;margin-top:4px;display:none;"></div>
                    </div>
                    <div class="form-group"><label>Customer / Business Name *</label><input type="text" id="edit_name" name="name" required value="{{ $customer->name }}"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Phone</label><input type="text" id="edit_phone" name="phone" value="{{ $customer->phone }}"></div>
                    <div class="form-group"><label>Email</label><input type="email" id="edit_email" name="email" value="{{ $customer->email }}"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>State</label><input type="text" id="edit_state" name="state" value="{{ $customer->state }}"></div>
                    <div class="form-group"><label>Billing Address</label><textarea id="edit_address" name="address" rows="2">{{ $customer->address }}</textarea></div>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Customer Photo / Logo</label>
                    <input type="file" name="image" id="edit_cust_img_input" accept="image/*" onchange="previewCustImage(this, 'edit_cust_preview')">
                    <div class="image-preview-container" id="edit_cust_preview_container" style="{{ $customer->image ? 'display:flex;' : 'display:none;' }}">
                        <img id="edit_cust_preview" src="{{ $customer->image ? asset($customer->image) : '' }}" class="preview-img-box" alt="Preview" style="{{ $customer->image ? 'display:block;' : 'display:none;' }}">
                        <label style="font-size:12px;color:#dc2626;cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1" id="edit_cust_remove_chk"> Remove current photo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal-overlay" id="photoModal" onclick="closePhotoModal(event)">
    <div class="modal" style="max-width:550px; background:rgba(255,255,255,0.98); backdrop-filter:blur(10px);">
        <div class="modal-header" style="border:none; padding-bottom:0;">
            <h3 id="photoModalTitle"><i class="fa fa-image"></i> Photo View</h3>
            <button class="modal-close" onclick="closeModal('photoModal')">✕</button>
        </div>
        <div class="modal-body" style="text-align:center; padding:20px;">
            <img id="photoModalImg" src="" alt="Photo" style="max-width:100%; max-height:70vh; border-radius:12px; box-shadow:var(--shadow-lg);">
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Switch Tabs
window.switchTab = function(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn-custom').forEach(b => b.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    if (btn) btn.classList.add('active');
};

// Filter Invoices
window.filterInvoices = function() {
    const q = (document.getElementById('invoiceSearchInput')?.value || '').toLowerCase();
    const st = (document.getElementById('invoiceStatusFilter')?.value || '');
    const rows = document.querySelectorAll('#customerInvoicesTable tbody tr');
    
    rows.forEach(r => {
        const searchTxt = r.getAttribute('data-search') || '';
        const rowStatus = r.getAttribute('data-status') || '';
        const matchesQuery = !q || searchTxt.includes(q);
        const matchesStatus = !st || rowStatus === st;
        if (matchesQuery && matchesStatus) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
};

// Record Payment Modal
window.openRecordPaymentModal = function() {
    const form = document.getElementById('recordPaymentForm');
    if (form) form.reset();
    openModal('recordPaymentModal');
};

window.openQuickPayModal = function(invoiceId, invoiceNumber, balance) {
    openModal('recordPaymentModal');
    const select = document.getElementById('pay_invoice_id');
    const amountInput = document.getElementById('pay_amount');
    if (select) select.value = invoiceId;
    if (amountInput) amountInput.value = balance;
};

window.onInvoiceSelectChange = function(select) {
    const opt = select.options[select.selectedIndex];
    const balance = opt.getAttribute('data-balance');
    const amountInput = document.getElementById('pay_amount');
    if (amountInput && balance) {
        amountInput.value = parseFloat(balance) > 0 ? parseFloat(balance).toFixed(2) : '';
    }
};

const payForm = document.getElementById('recordPaymentForm');
if (payForm) {
    payForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '{{ route('payments.store') }}', 'POST');
    });
}

// Edit Customer
let editUrl = '{{ route('customers.update', $customer) }}';

window.verifyGst = async function(mode) {
    const input = document.getElementById(mode + '_gstin');
    const btn = document.getElementById(mode + '_gst_btn');
    const statusDiv = document.getElementById(mode + '_gst_status');
    if (!input) return;
    const gstin = (input.value || '').trim().toUpperCase();

    if (!gstin || gstin.length < 15) {
        showToast('Please enter a valid 15-character GSTIN', 'error');
        return;
    }

    const oldBtnText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
    }
    if (statusDiv) {
        statusDiv.style.display = 'block';
        statusDiv.innerHTML = '<span style="color:var(--primary)"><i class="fa-solid fa-spinner fa-spin"></i> Verifying GSTIN...</span>';
    }

    try {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : '{{ csrf_token() }}';
        const res = await fetch('{{ route('gstin.verify') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ gstin: gstin })
        });

        const data = await res.json();

        if (data.success && data.valid) {
            const detailLabel = data.trade_name || data.legal_name || `${data.state || 'Gujarat'} (${data.business_type || 'Active Taxpayer'})`;
            if (statusDiv) {
                statusDiv.innerHTML = `<span style="color:#10b981;font-weight:600;"><i class="fa-solid fa-circle-check"></i> ${data.status || 'Active'} • ${detailLabel}</span>`;
            }
            
            // Auto-fill fields when available
            if (data.name && document.getElementById(mode + '_name')) {
                document.getElementById(mode + '_name').value = data.name;
            }
            if (data.state && document.getElementById(mode + '_state')) {
                document.getElementById(mode + '_state').value = data.state;
            }
            if (data.address && document.getElementById(mode + '_address')) {
                document.getElementById(mode + '_address').value = data.address;
            }

            showToast(data.message || '✅ GSTIN Verified & State Auto-filled!', 'success');
        } else {
            if (statusDiv) statusDiv.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-circle-xmark"></i> ${data.message || 'Invalid GSTIN'}</span>`;
            showToast(data.message || 'GSTIN verification failed', 'error');
        }
    } catch (err) {
        if (statusDiv) statusDiv.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Error verifying GSTIN</span>`;
        showToast('Error connecting to GST verification service', 'error');
    } finally {
        if (btn) {
            btn.innerHTML = oldBtnText;
            btn.disabled = false;
        }
    }
};

window.editCustomer = function(id, name, phone, email, address, gstin, state, imageUrl) {
    editUrl = `/customers/${id}`;
    if (document.getElementById('edit_name')) document.getElementById('edit_name').value = name || '';
    if (document.getElementById('edit_phone')) document.getElementById('edit_phone').value = phone || '';
    if (document.getElementById('edit_email')) document.getElementById('edit_email').value = email || '';
    if (document.getElementById('edit_address')) document.getElementById('edit_address').value = address || '';
    if (document.getElementById('edit_gstin')) document.getElementById('edit_gstin').value = gstin || '';
    if (document.getElementById('edit_state')) document.getElementById('edit_state').value = state || '';

    const statusDiv = document.getElementById('edit_gst_status');
    if (statusDiv) statusDiv.style.display = 'none';

    const preview = document.getElementById('edit_cust_preview');
    const container = document.getElementById('edit_cust_preview_container');
    const removeChk = document.getElementById('edit_cust_remove_chk');
    if (removeChk) removeChk.checked = false;

    if (imageUrl && preview && container) {
        preview.src = imageUrl;
        preview.style.display = 'block';
        container.style.display = 'flex';
    } else if (preview && container) {
        preview.src = '';
        preview.style.display = 'none';
        container.style.display = 'none';
    }

    openModal('editCustomerModal');
};

const editCustForm = document.getElementById('editCustomerForm');
if (editCustForm) {
    editCustForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, editUrl, 'PUT');
    });
}

// Photo Viewer
window.viewPhoto = function(url, title) {
    const img = document.getElementById('photoModalImg');
    const titleEl = document.getElementById('photoModalTitle');
    if (img) img.src = url;
    if (titleEl) titleEl.innerHTML = '<i class="fa fa-image"></i> ' + (title || 'Customer Photo');
    openModal('photoModal');
};

window.closePhotoModal = function(e) {
    if (e.target.id === 'photoModal') {
        closeModal('photoModal');
    }
};

window.previewCustImage = function(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;
    const container = preview.parentElement;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (container) container.style.display = 'flex';
        };
        reader.readAsDataURL(input.files[0]);
    }
};
</script>
@endsection
