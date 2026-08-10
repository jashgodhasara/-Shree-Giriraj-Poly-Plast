@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<style>
.dashboard-grid { display:grid; grid-template-columns:1fr 320px; gap:22px; }
@media(max-width:1024px){ .dashboard-grid { grid-template-columns:1fr; } }
</style>

@include('partials.guided-tour')

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
        </div>
        <div class="stat-label">Total Customers</div>
        <div class="stat-value">{{ $stats['customers'] }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-truck-field"></i></div>
        </div>
        <div class="stat-label">Suppliers</div>
        <div class="stat-value">{{ $stats['suppliers'] }}</div>
    </div>
    <div class="stat-card s-violet">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
        </div>
        <div class="stat-label">Total Sales</div>
        <div class="stat-value"><small>₹</small>{{ number_format($stats['revenue'], 0) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-circle-check"></i></div>
        </div>
        <div class="stat-label">Amount Received</div>
        <div class="stat-value"><small>₹</small>{{ number_format($stats['paid'], 0) }}</div>
    </div>
    <div class="stat-card s-red">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-clock"></i></div>
        </div>
        <div class="stat-label">Pending Dues</div>
        <div class="stat-value"><small>₹</small>{{ number_format($stats['unpaid'], 0) }}</div>
    </div>
    <div class="stat-card s-cyan">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-industry"></i></div>
        </div>
        <div class="stat-label">Production Runs</div>
        <div class="stat-value">{{ $stats['productions'] }}</div>
    </div>
    <div class="stat-card s-teal">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-boxes-stacked"></i></div>
        </div>
        <div class="stat-label">Raw Material Stock (Kg)</div>
        <div class="stat-value">{{ number_format($stats['raw_materials'], 0) }}</div>
    </div>
    <div class="stat-card s-rose">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-tag"></i></div>
        </div>
        <div class="stat-label">Total Products</div>
        <div class="stat-value">{{ $stats['products'] }}</div>
    </div>
</div>

<div class="dashboard-grid">

{{-- Recent Invoices --}}
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-receipt"></i> Recent Invoices</h3>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> New Bill
        </a>
    </div>
    <div class="card-body" style="padding:0">
        @if($recentInvoices->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-receipt"></i></div>
            <p>No invoices yet</p>
            <small>Create your first bill to get started</small>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th><th>Customer</th><th>Date</th>
                        <th>Total</th><th>Pending</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentInvoices as $inv)
                <tr>
                    <td><span style="font-weight:700;color:var(--primary)">{{ $inv->invoice_number }}</span></td>
                    <td class="fw-600">{{ $inv->customer->name }}</td>
                    <td style="color:var(--text-muted)">{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td class="fw-bold">₹{{ number_format($inv->grand_total, 2) }}</td>
                    <td style="color:{{ $inv->pending_amount > 0 ? '#ef4444' : '#10b981' }};font-weight:700">
                        ₹{{ number_format($inv->pending_amount, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $inv->status === 'Paid' ? 'badge-green' : ($inv->status === 'Partial' ? 'badge-orange' : 'badge-red') }}">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-ghost btn-sm btn-icon" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('invoices.print', $inv) }}" class="btn btn-ghost btn-sm btn-icon" target="_blank" title="Print">
                            <i class="fa fa-print"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid var(--border)">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline btn-sm">
                <i class="fa fa-list"></i> View All Invoices
            </a>
        </div>
        @endif
    </div>
</div>

{{-- Right column --}}
<div>
    {{-- Quick Actions --}}
    <div class="card" style="margin-bottom:22px">
        <div class="card-header"><h3><i class="fa fa-bolt"></i> Quick Actions</h3></div>
        <div class="card-body" style="padding:14px;display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary w-full" style="justify-content:center">
                <i class="fa fa-file-invoice-dollar"></i> Create New Bill
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-outline w-full" style="justify-content:center">
                <i class="fa fa-user-plus"></i> Add Customer
            </a>
            <a href="{{ route('production.index') }}" class="btn btn-outline w-full" style="justify-content:center">
                <i class="fa fa-industry"></i> Log Production
            </a>
            <a href="{{ route('material-transactions.index') }}" class="btn btn-outline w-full" style="justify-content:center">
                <i class="fa fa-right-left"></i> Raw Material IN/OUT
            </a>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-triangle-exclamation" style="background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e"></i> Low Stock</h3>
            <a href="{{ route('materials.index') }}" class="btn btn-ghost btn-sm">View All</a>
        </div>
        @if($lowStock->isEmpty())
        <div class="empty-state" style="padding:24px">
            <div class="empty-icon" style="width:48px;height:48px;font-size:20px;background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#065f46;margin-bottom:10px">
                <i class="fa fa-check"></i>
            </div>
            <p style="font-size:13px">All stock levels OK</p>
        </div>
        @else
        @foreach($lowStock as $mat)
        <div style="padding:11px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-weight:600;font-size:13px;">{{ $mat->name }}</div>
                <div style="font-size:11px;color:var(--text-muted)">{{ $mat->type }}</div>
            </div>
            <span class="badge badge-red">{{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }}</span>
        </div>
        @endforeach
        @endif
    </div>
</div>

</div>
@endsection
