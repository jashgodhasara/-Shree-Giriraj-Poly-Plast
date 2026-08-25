@extends('layouts.app')
@section('title', 'Inventory Valuation & Dead Stock')
@section('page-title', 'Inventory Valuation')

@section('content')
<style>
    .val-tab-btn {
        padding: 10px 18px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
    }
    .val-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
</style>

<!-- Top Valuation Metrics -->
<div class="stats-grid">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-cubes"></i></div>
            <span class="badge badge-purple">Total Stock</span>
        </div>
        <div class="stat-label">Stock Units in Warehouse</div>
        <div class="stat-value">{{ number_format($data['total_stock_qty'], 2) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-scale-balanced"></i></div>
            <span class="badge badge-success">Cost Basis</span>
        </div>
        <div class="stat-label">Total Inventory Valuation</div>
        <div class="stat-value">₹{{ number_format($data['total_valuation'], 2) }}</div>
    </div>
    <div class="stat-card s-cyan">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-chart-pie"></i></div>
            <span class="badge badge-indigo">Sales Basis</span>
        </div>
        <div class="stat-label">Potential Realizable Revenue</div>
        <div class="stat-value">₹{{ number_format($data['total_retail_value'], 2) }}</div>
    </div>
    <div class="stat-card s-teal">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-arrow-trend-up"></i></div>
            <span class="badge badge-purple">Profit Potential</span>
        </div>
        <div class="stat-label">Unrealized Gross Margin</div>
        <div class="stat-value">₹{{ number_format(max(0, $data['total_retail_value'] - $data['total_valuation']), 2) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <div class="d-flex gap-2">
            <button class="val-tab-btn active" onclick="switchValTab('tab-valuation', this)">
                <i class="fa fa-list-check"></i> Complete Inventory Valuation ({{ count($data['items']) }})
            </button>
            <button class="val-tab-btn" onclick="switchValTab('tab-deadstock', this)">
                <i class="fa fa-hourglass-end text-danger"></i> Dead Stock Analysis ({{ $deadStock->count() }})
            </button>
        </div>
        <button class="btn btn-outline btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print Report</button>
    </div>
    <div class="card-body" style="padding:0;">
        <!-- Tab 1: Valuation -->
        <div id="tab-valuation" class="val-tab-content">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th style="text-align:right;">Current Stock</th>
                            <th style="text-align:right;">Weighted Avg Cost</th>
                            <th style="text-align:right;">Inventory Value (Cost)</th>
                            <th style="text-align:right;">Sales Rate</th>
                            <th style="text-align:right;">Potential Revenue</th>
                            <th style="text-align:right;">Margin (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data['items'] as $item)
                    @php
                        $margin = $item['retail_value'] > 0 ? round((($item['retail_value'] - $item['stock_value']) / $item['retail_value']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>{{ $item['id'] }}</td>
                        <td>
                            <a href="{{ route('products.show', $item['id']) }}" class="fw-bold text-primary" style="text-decoration:none;">
                                {{ $item['name'] }}
                            </a>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $item['sku'] }}</div>
                        </td>
                        <td><span class="badge badge-indigo">{{ $item['category'] }}</span></td>
                        <td style="text-align:right;" class="fw-bold">{{ number_format($item['current_stock'], 2) }} {{ $item['unit'] }}</td>
                        <td style="text-align:right;">₹{{ number_format($item['average_cost'], 2) }}</td>
                        <td style="text-align:right;" class="fw-bold text-success">₹{{ number_format($item['stock_value'], 2) }}</td>
                        <td style="text-align:right;">₹{{ number_format($item['sales_rate'], 2) }}</td>
                        <td style="text-align:right;" class="fw-bold">₹{{ number_format($item['retail_value'], 2) }}</td>
                        <td style="text-align:right;">
                            <span class="badge {{ $margin >= 20 ? 'badge-success' : ($margin > 0 ? 'badge-warning' : 'badge-danger') }}">
                                {{ $margin }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Dead Stock -->
        <div id="tab-deadstock" class="val-tab-content" style="display:none;">
            @if($deadStock->isEmpty())
                <div class="empty-state" style="padding:30px;">
                    <i class="fa fa-circle-check text-success" style="font-size:32px;"></i>
                    <p style="margin-top:8px;">Great news! No dead stock detected in the last 60 days.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th style="text-align:right;">Idle Stock Qty</th>
                                <th style="text-align:right;">Blocked Capital Value</th>
                                <th>Last Movement Date</th>
                                <th>Recommendation</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($deadStock as $ds)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $ds['product']) }}" class="fw-bold text-primary" style="text-decoration:none;">
                                    {{ $ds['product']->name }}
                                </a>
                                <div style="font-size:11px; color:var(--text-muted);">{{ $ds['product']->sku }}</div>
                            </td>
                            <td><span class="badge badge-indigo">{{ $ds['product']->category->name ?? '—' }}</span></td>
                            <td style="text-align:right;" class="fw-bold text-danger">{{ number_format($ds['current_stock'], 2) }} {{ $ds['product']->unit ?: 'PCS' }}</td>
                            <td style="text-align:right;" class="fw-bold text-danger">₹{{ number_format($ds['stock_value'], 2) }}</td>
                            <td>{{ $ds['last_movement_date'] }}</td>
                            <td>
                                <span class="badge badge-warning">Discount / Liquidate</span>
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
function switchValTab(tabId, btn) {
    document.querySelectorAll('.val-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.val-tab-content').forEach(c => c.style.display = 'none');
    btn.classList.add('active');
    document.getElementById(tabId).style.display = 'block';
}
</script>
@endsection
