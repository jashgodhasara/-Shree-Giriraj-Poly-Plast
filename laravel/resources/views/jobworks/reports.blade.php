@extends('layouts.app')
@section('title', 'Job Work Reports')
@section('page-title', 'Job Work Analytics & Production Reports')

@section('content')
<div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
    <div>
        <h2 style="font-size: 19px; font-weight: 700; color: var(--text);">
            <i class="fa fa-chart-line text-primary"></i> Job Work Analytics &amp; Reports
        </h2>
        <p class="text-muted" style="font-size: 12.5px;">Client-wise, product-wise and periodic material intake &amp; production yield reports</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline btn-sm" onclick="window.print()">
            <i class="fa fa-print"></i> Print Report
        </button>
        <a href="{{ route('jobworks.index') }}" class="btn btn-outline btn-sm">
            <i class="fa fa-arrow-left"></i> Job Works List
        </a>
    </div>
</div>

{{-- Date Filter --}}
@include('partials.date-filter', ['action' => route('jobworks.reports')])

{{-- Report Type Tabs --}}
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('jobworks.reports', array_merge(request()->query(), ['type' => 'client'])) }}" 
       class="btn {{ $reportType === 'client' ? 'btn-primary' : 'btn-outline' }} btn-sm">
        <i class="fa fa-building-user"></i> Client-wise Report
    </a>
    <a href="{{ route('jobworks.reports', array_merge(request()->query(), ['type' => 'product'])) }}" 
       class="btn {{ $reportType === 'product' ? 'btn-primary' : 'btn-outline' }} btn-sm">
        <i class="fa fa-tag"></i> Product-wise Yield Report
    </a>
    <a href="{{ route('jobworks.reports', array_merge(request()->query(), ['type' => 'date'])) }}" 
       class="btn {{ $reportType === 'date' ? 'btn-primary' : 'btn-outline' }} btn-sm">
        <i class="fa fa-calendar-days"></i> Daily Production Log
    </a>
</div>

{{-- Client-Wise Report --}}
@if($reportType === 'client')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-building-user"></i> Client-Wise Job Work Summary</h3>
        <span class="badge badge-purple">{{ count($clientReport) }} Active Clients</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Client / Party</th>
                        <th>Orders</th>
                        <th>Material (KG)</th>
                        <th>Gross PCS</th>
                        <th>Wastage PCS</th>
                        <th>Net PCS</th>
                        <th>Delivered</th>
                        <th>Balance</th>
                        <th>Total Amount (₹)</th>
                        <th>Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientReport as $c)
                    <tr>
                        <td>
                            <strong>{{ $c['client_name'] }}</strong>
                            @if($c['company_name'])<div style="font-size:11px; color:var(--text-muted);">{{ $c['company_name'] }}</div>@endif
                        </td>
                        <td><span class="badge badge-gray">{{ $c['total_orders'] }}</span></td>
                        <td><strong>{{ number_format($c['total_material_kg'], 2) }} KG</strong></td>
                        <td>{{ number_format($c['total_gross_pcs']) }}</td>
                        <td><span class="badge badge-orange">{{ number_format($c['total_wastage_pcs']) }}</span></td>
                        <td><strong style="color:#059669;">{{ number_format($c['total_net_pcs']) }}</strong></td>
                        <td style="color:#2563eb;">{{ number_format($c['total_delivered']) }}</td>
                        <td>
                            @if($c['balance_pcs'] > 0)
                                <span class="badge badge-red">{{ number_format($c['balance_pcs']) }}</span>
                            @else
                                <span class="badge badge-green">0</span>
                            @endif
                        </td>
                        <td class="fw-bold">₹{{ number_format($c['total_amount'], 2) }}</td>
                        <td style="color: #ef4444; font-weight: 700;">₹{{ number_format($c['balance_amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 24px;">No client job work records found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Product-Wise Yield Report --}}
@if($reportType === 'product')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-tag"></i> Product-Wise Production &amp; Material Conversion</h3>
        <span class="badge badge-purple">{{ count($productReport) }} Products Processed</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Piece Weight</th>
                        <th>Received Material (KG)</th>
                        <th>Gross Calculated</th>
                        <th>Wastage</th>
                        <th>Net Finished PCS</th>
                        <th>Delivered</th>
                        <th>Remaining</th>
                        <th>Total Revenue (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productReport as $p)
                    <tr>
                        <td>
                            <strong>{{ $p['product_name'] }}</strong>
                            @if($p['sku'])<div style="font-size:11px; color:var(--text-muted);">SKU: {{ $p['sku'] }}</div>@endif
                        </td>
                        <td><span class="badge badge-purple">{{ $p['piece_weight'] }}</span></td>
                        <td><strong>{{ number_format($p['total_material_kg'], 2) }} KG</strong></td>
                        <td>{{ number_format($p['total_gross_pcs']) }} PCS</td>
                        <td><span class="badge badge-orange">{{ number_format($p['total_wastage_pcs']) }}</span></td>
                        <td><strong style="color:#059669;">{{ number_format($p['total_net_pcs']) }} PCS</strong></td>
                        <td style="color:#2563eb;">{{ number_format($p['total_delivered']) }}</td>
                        <td>
                            @if($p['balance_pcs'] > 0)
                                <span class="badge badge-red">{{ number_format($p['balance_pcs']) }}</span>
                            @else
                                <span class="badge badge-green">0</span>
                            @endif
                        </td>
                        <td class="fw-bold" style="color:var(--primary);">₹{{ number_format($p['total_amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 24px;">No product job work records found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Daily Production Log --}}
@if($reportType === 'date')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-calendar-days"></i> Periodic Job Work Intake &amp; Production Log</h3>
        <span class="badge badge-purple">{{ count($dateReport) }} Days Logged</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders Count</th>
                        <th>Total Material (KG)</th>
                        <th>Gross Pieces</th>
                        <th>Wastage</th>
                        <th>Net Finished Pieces</th>
                        <th>Delivered Pieces</th>
                        <th>Balance Pieces</th>
                        <th>Total Billed (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dateReport as $d)
                    <tr>
                        <td><strong>{{ date('d M Y', strtotime($d['date'])) }}</strong></td>
                        <td><span class="badge badge-gray">{{ $d['orders_count'] }}</span></td>
                        <td><strong>{{ number_format($d['total_material_kg'], 2) }} KG</strong></td>
                        <td>{{ number_format($d['total_gross_pcs']) }}</td>
                        <td><span class="badge badge-orange">{{ number_format($d['total_wastage_pcs']) }}</span></td>
                        <td><strong style="color:#059669;">{{ number_format($d['total_net_pcs']) }}</strong></td>
                        <td style="color:#2563eb;">{{ number_format($d['total_delivered']) }}</td>
                        <td>{{ number_format($d['balance_pcs']) }}</td>
                        <td class="fw-bold">₹{{ number_format($d['total_amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 24px;">No job work log found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
