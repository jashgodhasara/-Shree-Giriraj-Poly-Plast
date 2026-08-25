@extends('layouts.app')
@section('title', 'Low Stock & Reorder Alerts')
@section('page-title', 'Low Stock Alerts')

@section('content')
<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-triangle-exclamation"></i></div>
            <span class="badge badge-warning">Action Required</span>
        </div>
        <div class="stat-label">Products Below Reorder Threshold</div>
        <div class="stat-value">{{ number_format($alerts->count()) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
            <span class="badge badge-success">Procurement</span>
        </div>
        <div class="stat-label">Estimated Reorder Cost</div>
        <div class="stat-value">₹{{ number_format($totalSuggestedValue, 2) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-boxes-packing text-warning"></i> Reorder Recommendations</h3>
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> New Purchase Order
        </a>
    </div>
    <div class="card-body" style="padding:0">
        @if($alerts->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-circle-check text-success"></i></div>
            <p>All products have healthy stock levels above their reorder thresholds.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Min / Max Stock</th>
                        <th>Status</th>
                        <th style="text-align:right;">Suggested Order Qty</th>
                        <th style="text-align:right;">Purchase Rate</th>
                        <th style="text-align:right;">Estimated Cost</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($alerts as $al)
                <tr>
                    <td>
                        <a href="{{ route('products.show', $al['product']) }}" class="fw-bold text-primary" style="text-decoration:none;">
                            {{ $al['product']->name }}
                        </a>
                        <div style="font-size:11px; color:var(--text-muted);">SKU: {{ $al['product']->sku }}</div>
                    </td>
                    <td><span class="badge badge-indigo">{{ $al['product']->category->name ?? '—' }}</span></td>
                    <td class="fw-bold {{ $al['current_stock'] <= 0 ? 'text-danger' : 'text-warning' }}">
                        {{ number_format($al['current_stock'], 2) }} {{ $al['product']->unit ?: 'PCS' }}
                    </td>
                    <td>{{ number_format($al['reorder_level'], 2) }}</td>
                    <td style="font-size:12px; color:var(--text-muted);">
                        {{ number_format($al['minimum_stock'], 2) }} / {{ $al['maximum_stock'] > 0 ? number_format($al['maximum_stock'], 2) : '—' }}
                    </td>
                    <td>
                        <span class="badge {{ $al['status'] == 'Out of Stock' ? 'badge-danger' : 'badge-warning' }}">
                            {{ $al['status'] }}
                        </span>
                    </td>
                    <td style="text-align:right;" class="fw-bold text-primary">
                        {{ number_format($al['suggested_qty'], 2) }} {{ $al['product']->unit ?: 'PCS' }}
                    </td>
                    <td style="text-align:right;">₹{{ number_format($al['purchase_rate'], 2) }}</td>
                    <td style="text-align:right;" class="fw-bold">₹{{ number_format($al['estimated_cost'], 2) }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('purchase-orders.create') }}" class="btn btn-outline btn-sm" title="Create Purchase Order">
                            <i class="fa fa-cart-plus"></i> Reorder
                        </a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
