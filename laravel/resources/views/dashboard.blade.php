@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<style>
.dashboard-grid { display:grid; grid-template-columns:1fr 320px; gap:22px; }
@media(max-width:1024px){ .dashboard-grid { grid-template-columns:1fr; } }
a.stat-card {
    text-decoration: none;
    color: inherit;
    display: block;
    cursor: pointer;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
a.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(99,102,241,.12);
    border-color: rgba(99,102,241,.35);
}
.stat-card .stat-arrow {
    font-size: 11px;
    opacity: 0;
    transform: translateX(-4px);
    transition: all .2s ease;
    color: var(--text-muted);
}
a.stat-card:hover .stat-arrow {
    opacity: 0.85;
    transform: translateX(0);
}
</style>

@include('partials.guided-tour')

@if(!empty($isCustomDate))
<div style="margin-bottom:20px; padding:14px 20px; background:linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08)); border:1.5px solid rgba(99,102,241,0.35); border-radius:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; box-shadow: 0 4px 16px rgba(99,102,241,0.08);">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,var(--primary),#8b5cf6); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; box-shadow: 0 4px 12px rgba(99,102,241,0.3);">
            <i class="fa fa-filter"></i>
        </div>
        <div>
            <div style="font-weight:700; font-size:14.5px; color:var(--text); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                Filtered Period: <span style="color:var(--primary);">{{ $dateLabel ?? $workingDate }}</span>
                <span style="font-size:10px; font-weight:700; background:var(--primary); color:#fff; padding:2px 7px; border-radius:10px; text-transform:uppercase;">Filter Active</span>
            </div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                Dashboard metrics & invoices are filtered for this period. Active bill creation date: <strong>{{ \Carbon\Carbon::parse($workingDate)->format('D, d M, Y') }}</strong>
            </div>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openDateSelectorModal()"><i class="fa fa-calendar-days"></i> Change Filter</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="resetWorkingDate()"><i class="fa fa-rotate-left"></i> Reset to Today</button>
    </div>
</div>
@endif

{{-- Stats Grid --}}
<div class="stats-grid">
    <a href="{{ route('customers.index') }}" class="stat-card s-indigo" title="Click to view all Customers">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Total Customers</div>
        <div class="stat-value">{{ $stats['customers'] }}</div>
    </a>
    <a href="{{ route('suppliers.index') }}" class="stat-card s-amber" title="Click to view all Suppliers">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-truck-field"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Suppliers</div>
        <div class="stat-value">{{ $stats['suppliers'] }}</div>
    </a>
    <a href="{{ route('invoices.index') }}" class="stat-card s-violet" title="Click to view all Sales Invoices">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Total Sales</div>
        <div class="stat-value"><small>₹</small>{{ number_format($stats['revenue'], 0) }}</div>
    </a>
    <a href="{{ route('invoices.index') }}?status=Paid" class="stat-card s-emerald" title="Click to view Paid Invoices">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-circle-check"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Amount Received</div>
        <div class="stat-value"><small>₹</small>{{ number_format($stats['paid'], 0) }}</div>
    </a>
    <a href="{{ route('invoices.index') }}?status=Unpaid" class="stat-card s-red" title="Click to view Pending Dues">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-clock"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Pending Dues</div>
        <div class="stat-value"><small>₹</small>{{ number_format($stats['unpaid'], 0) }}</div>
    </a>
    <a href="{{ route('production.index') }}" class="stat-card s-cyan" title="Click to view Production Runs">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-industry"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Production Runs</div>
        <div class="stat-value">{{ $stats['productions'] }}</div>
    </a>
    <a href="{{ route('materials.index') }}" class="stat-card s-teal" title="Click to view Raw Material Inventory">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-boxes-stacked"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Raw Material Stock (Kg)</div>
        <div class="stat-value">{{ number_format($stats['raw_materials'], 0) }}</div>
    </a>
    <a href="{{ route('products.index') }}" class="stat-card s-rose" title="Click to view all Finished Products">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-tag"></i></div>
            <i class="fa-solid fa-arrow-up-right-from-square stat-arrow"></i>
        </div>
        <div class="stat-label">Total Products</div>
        <div class="stat-value">{{ $stats['products'] }}</div>
    </a>
</div>

{{-- Live Plastic Market Rates (3MinAPI Feed) --}}
<div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,rgba(30,41,59,0.95),rgba(15,23,42,0.98));border:1px solid rgba(59,130,246,0.3);box-shadow:0 10px 25px -5px rgba(0,0,0,0.3),0 8px 10px -6px rgba(0,0,0,0.3);color:#fff;overflow:hidden;position:relative;">
    <div style="position:absolute;top:-40px;right:-40px;width:150px;height:150px;background:radial-gradient(circle,rgba(59,130,246,0.15),transparent 70%);border-radius:50%;pointer-events:none;"></div>
    <div class="card-header" style="border-bottom:1px solid rgba(255,255,255,0.08);background:transparent;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(37,99,235,0.35);">
                <i class="fa-solid fa-chart-line" style="color:#fff;font-size:16px;"></i>
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <h3 style="margin:0;font-size:15px;font-weight:700;letter-spacing:0.3px;color:#fff;">Live Polymer Market Rates</h3>
                    <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(52,211,153,0.3);">
                        <span style="width:6px;height:6px;border-radius:50%;background:#34d399;box-shadow:0 0 8px #34d399;animation:pulse 1.8s infinite;"></span>
                        ONLINE LIVE FEED
                    </span>
                </div>
                <div style="font-size:11px;color:rgba(255,255,255,0.6);margin-top:2px;" id="polymer-last-sync">
                    Powered by 3MinAPI • Benchmarks updated today
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('materials.index') }}" class="btn btn-ghost btn-sm" style="color:#93c5fd;border:1px solid rgba(147,197,253,0.2);padding:5px 12px;border-radius:8px;font-size:12px;">
                <i class="fa fa-boxes-stacked"></i> Materials Master
            </a>
            <button type="button" id="btn-refresh-polymer" onclick="refreshPolymerRates()" class="btn btn-primary btn-sm" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);border:none;padding:5px 14px;border-radius:8px;font-size:12px;font-weight:600;">
                <i class="fa-solid fa-arrows-rotate" id="polymer-spinner"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body" style="padding:16px 20px;">
        <div id="polymer-rates-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:12px;">
            @if(isset($polymerRates['items']) && count($polymerRates['items']) > 0)
                @foreach($polymerRates['items'] as $item)
                    @php
                        $isUp = ($item['trend'] ?? '') === 'up' || (isset($item['change']) && str_starts_with($item['change'], '+') && $item['change'] !== '+0.00');
                        $isDown = ($item['trend'] ?? '') === 'down' || (isset($item['change']) && str_starts_with($item['change'], '-'));
                        $trendColor = $isUp ? '#34d399' : ($isDown ? '#f87171' : '#94a3b8');
                        $trendBg = $isUp ? 'rgba(52,211,153,0.12)' : ($isDown ? 'rgba(248,113,113,0.12)' : 'rgba(148,163,184,0.12)');
                        $trendIcon = $isUp ? 'fa-arrow-trend-up' : ($isDown ? 'fa-arrow-trend-down' : 'fa-minus');
                    @endphp
                    <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px 14px;transition:all 0.2s ease;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                            <span style="font-size:11px;font-weight:600;color:#93c5fd;text-transform:uppercase;letter-spacing:0.5px;">{{ $item['category'] ?? 'Polymer' }}</span>
                            <span style="font-size:10px;font-weight:700;color:{{ $trendColor }};background:{{ $trendBg }};padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                                <i class="fa-solid {{ $trendIcon }}" style="font-size:9px;"></i> {{ $item['change'] ?? '0.00' }}
                            </span>
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $item['material_name'] }}">
                            {{ $item['material_name'] }}
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:baseline;">
                            <div style="font-size:18px;font-weight:800;color:#f8fafc;letter-spacing:-0.3px;">
                                <span style="font-size:12px;color:rgba(255,255,255,0.6);font-weight:600;">₹</span>{{ number_format((float)($item['current_price'] ?? 0), 2) }}
                            </div>
                            <span style="font-size:11px;color:rgba(255,255,255,0.5);font-weight:500;">/ {{ $item['unit'] ?? 'Kg' }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div style="color:rgba(255,255,255,0.6);text-align:center;grid-column:1/-1;">No pricing data available.</div>
            @endif
        </div>
    </div>
</div>

<script>
async function loadPolymerRates(forceRefresh = false) {
    const grid = document.getElementById('polymer-rates-grid');
    const syncText = document.getElementById('polymer-last-sync');
    const spinner = document.getElementById('polymer-spinner');
    if (spinner) spinner.classList.add('fa-spin');

    try {
        const url = forceRefresh ? '/plastic-prices?refresh=1' : '/plastic-prices';
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (data && data.items && data.items.length > 0) {
            let html = '';
            data.items.forEach(item => {
                const isUp = item.trend === 'up' || (item.change && item.change.startsWith('+') && item.change !== '+0.00');
                const isDown = item.trend === 'down' || (item.change && item.change.startsWith('-'));
                const trendColor = isUp ? '#34d399' : (isDown ? '#f87171' : '#94a3b8');
                const trendBg = isUp ? 'rgba(52,211,153,0.12)' : (isDown ? 'rgba(248,113,113,0.12)' : 'rgba(148,163,184,0.12)');
                const trendIcon = isUp ? 'fa-arrow-trend-up' : (isDown ? 'fa-arrow-trend-down' : 'fa-minus');

                html += `
                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px 14px;transition:all 0.2s ease;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                        <span style="font-size:11px;font-weight:600;color:#93c5fd;text-transform:uppercase;letter-spacing:0.5px;">${item.category || 'Polymer'}</span>
                        <span style="font-size:10px;font-weight:700;color:${trendColor};background:${trendBg};padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;">
                            <i class="fa-solid ${trendIcon}" style="font-size:9px;"></i> ${item.change || '0.00'}
                        </span>
                    </div>
                    <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${item.material_name}">
                        ${item.material_name}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:baseline;">
                        <div style="font-size:18px;font-weight:800;color:#f8fafc;letter-spacing:-0.3px;">
                            <span style="font-size:12px;color:rgba(255,255,255,0.6);font-weight:600;">₹</span>${Number(item.current_price).toFixed(2)}
                        </div>
                        <span style="font-size:11px;color:rgba(255,255,255,0.5);font-weight:500;">/ ${item.unit || 'Kg'}</span>
                    </div>
                </div>`;
            });
            grid.innerHTML = html;

            if (syncText && data.last_updated) {
                const time = new Date(data.last_updated).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                syncText.innerHTML = `Source: <b>${data.source || '3MinAPI'}</b> • Live Synced at ${time}`;
            }
        }
    } catch (e) {
        console.error('Failed to load polymer prices:', e);
    } finally {
        if (spinner) spinner.classList.remove('fa-spin');
    }
}

function refreshPolymerRates() {
    loadPolymerRates(true);
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto background refresh every 60s without disrupting the UI
    setInterval(() => loadPolymerRates(false), 60000);
});
</script>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
</style>

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
