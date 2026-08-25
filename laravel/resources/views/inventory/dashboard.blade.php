@extends('layouts.app')
@section('title', 'Inventory & Stock Dashboard')
@section('page-title', 'Inventory Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Top Row Inventory KPI Metric Cards -->
<div class="stats-grid">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-boxes-stacked"></i></div>
            <span class="badge badge-purple">Catalog</span>
        </div>
        <div class="stat-label">Total Active Products</div>
        <div class="stat-value">{{ number_format($totalProducts) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
            <span class="badge badge-success">Valuation</span>
        </div>
        <div class="stat-label">Total Inventory Value</div>
        <div class="stat-value">₹{{ number_format($totalValuation, 2) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-triangle-exclamation"></i></div>
            <span class="badge badge-warning">Reorder</span>
        </div>
        <div class="stat-label">Low Stock Alerts</div>
        <div class="stat-value">{{ number_format($lowStockCount) }}</div>
    </div>
    <div class="stat-card s-red">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-circle-xmark"></i></div>
            <span class="badge badge-danger">Out of Stock</span>
        </div>
        <div class="stat-label">Depleted Items</div>
        <div class="stat-value">{{ number_format($outOfStockCount) }}</div>
    </div>
</div>

<!-- Secondary Row: Today's Stock Movements -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-teal">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-arrow-down-long"></i></div>
            <span class="badge badge-success">Today IN</span>
        </div>
        <div class="stat-label">Today's Inward Quantity</div>
        <div class="stat-value">+{{ number_format($todayIn, 2) }}</div>
    </div>
    <div class="stat-card s-rose">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-arrow-up-long"></i></div>
            <span class="badge badge-danger">Today OUT</span>
        </div>
        <div class="stat-label">Today's Outward Quantity</div>
        <div class="stat-value">-{{ number_format($todayOut, 2) }}</div>
    </div>
    <div class="stat-card s-cyan">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-money-bill-transfer"></i></div>
            <span class="badge badge-indigo">Today Value</span>
        </div>
        <div class="stat-label">Today's Movement Value</div>
        <div class="stat-value">₹{{ number_format($todayVal, 2) }}</div>
    </div>
    <div class="stat-card s-violet">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-layer-group"></i></div>
            <span class="badge badge-purple">Categories</span>
        </div>
        <div class="stat-label">Categories Tracked</div>
        <div class="stat-value">{{ count($categoryValuations) }}</div>
    </div>
</div>

<!-- Interactive Charts Row -->
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; margin-bottom:24px;">
    <!-- Monthly Movement Chart -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-chart-line text-primary"></i> Stock IN vs Stock OUT (Monthly Movement)</h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyMovementChart" style="max-height:280px; width:100%;"></canvas>
        </div>
    </div>

    <!-- Category Valuation Breakdown -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-chart-pie text-primary"></i> Value by Category</h3>
        </div>
        <div class="card-body">
            <canvas id="categoryValuationChart" style="max-height:280px; width:100%;"></canvas>
        </div>
    </div>
</div>

<!-- Low Stock & Recent Transactions Grid -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    <!-- Low Stock Priority List -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-triangle-exclamation text-danger"></i> Low Stock &amp; Reorder Alerts</h3>
            <a href="{{ route('inventory.low-stock') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
            @if($lowStockList->isEmpty())
                <div class="empty-state" style="padding:24px;">
                    <i class="fa fa-check-circle text-success" style="font-size:24px;"></i>
                    <p style="margin-top:6px;">All products are adequately stocked!</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($lowStockList as $alert)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $alert['product']) }}" class="fw-bold" style="text-decoration:none; color:var(--primary);">
                                    {{ $alert['product']->name }}
                                </a>
                                <div style="font-size:11px; color:var(--text-muted);">{{ $alert['product']->sku }}</div>
                            </td>
                            <td class="fw-bold text-danger">{{ number_format($alert['current_stock'], 2) }} {{ $alert['product']->unit ?: 'PCS' }}</td>
                            <td>{{ number_format($alert['reorder_level'], 2) }}</td>
                            <td>
                                <span class="badge {{ $alert['status'] == 'Out of Stock' ? 'badge-danger' : 'badge-warning' }}" style="font-size:10px;">
                                    {{ $alert['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Stock Ledger Transactions -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-clock-rotate-left text-primary"></i> Recent Stock Ledger Movements</h3>
            <a href="{{ route('inventory.ledger') }}" class="btn btn-outline btn-sm">Full Ledger</a>
        </div>
        <div class="card-body" style="padding:0;">
            @if($recentTransactions->isEmpty())
                <div class="empty-state" style="padding:24px;">
                    <p>No recent stock movements recorded.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Movement</th>
                                <th>New Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($recentTransactions as $tx)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $tx->product->name ?? '—' }}</span>
                                <div style="font-size:11px; color:var(--text-muted);">{{ $tx->reference_number ?: 'Ref #' . $tx->id }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $tx->quantity_in > 0 ? 'badge-success' : 'badge-danger' }}" style="font-size:10px;">
                                    {{ $tx->transaction_type }}
                                </span>
                            </td>
                            <td class="fw-bold {{ $tx->quantity_in > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $tx->quantity_in > 0 ? '+' . number_format($tx->quantity_in, 2) : '-' . number_format($tx->quantity_out, 2) }}
                            </td>
                            <td class="fw-bold" style="color:var(--primary);">
                                {{ number_format($tx->new_stock, 2) }}
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
document.addEventListener('DOMContentLoaded', function() {
    // 1. Monthly Movement Chart
    const monthlyData = @json($monthlyMovements);
    const months = monthlyData.map(m => m.month || 'Recent');
    const inData = monthlyData.map(m => parseFloat(m.total_in || 0));
    const outData = monthlyData.map(m => parseFloat(m.total_out || 0));

    const ctxMove = document.getElementById('monthlyMovementChart');
    if (ctxMove) {
        new Chart(ctxMove, {
            type: 'bar',
            data: {
                labels: months.length ? months : ['No Data'],
                datasets: [
                    { label: 'Stock IN', data: inData.length ? inData : [0], backgroundColor: '#10b981', borderRadius: 4 },
                    { label: 'Stock OUT', data: outData.length ? outData : [0], backgroundColor: '#ef4444', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // 2. Category Valuation Pie Chart
    const categoryData = @json($categoryValuations);
    const catLabels = categoryData.map(c => c.name);
    const catValues = categoryData.map(c => c.value);

    const ctxCat = document.getElementById('categoryValuationChart');
    if (ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: catLabels.length ? catLabels : ['Uncategorized'],
                datasets: [{
                    data: catValues.length ? catValues : [1],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6', '#ec4899', '#3b82f6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
            }
        });
    }
});
</script>
@endsection
