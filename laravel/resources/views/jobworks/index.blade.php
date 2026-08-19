@extends('layouts.app')
@section('title', 'Job Work Orders')
@section('page-title', 'All Job Work Orders')

@section('content')
<style>
.jw-mobile-card { display:none; }
.jw-desktop-table { display:block; }
@media(max-width:900px){
    .jw-mobile-card { display:block; }
    .jw-desktop-table { display:none; }
    .jw-card-item {
        background:#fff; border-radius:12px; border:1px solid var(--border);
        padding:14px 16px; margin-bottom:12px;
        box-shadow:0 1px 4px rgba(0,0,0,.06);
    }
    .jw-card-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .jw-card-row:last-child { margin-bottom:0; }
    .jw-card-label { font-size:11.5px; color:var(--text-muted); font-weight:500; }
    .jw-card-val { font-size:13px; font-weight:700; color:var(--text); }
    .jw-card-actions { display:flex; gap:6px; margin-top:10px; padding-top:10px; border-top:1px solid var(--border); flex-wrap:wrap; }
}
</style>

{{-- Header & Quick Action --}}
<div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
    <div>
        <h2 style="font-size: 19px; font-weight: 700; color: var(--text);">
            <i class="fa fa-scale-balanced text-primary"></i> Job Work Register &amp; Material Tracking
        </h2>
        <p class="text-muted" style="font-size: 12.5px;">Client material intake, automatic pieces production, wastage tracking &amp; delivery records</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('jobworks.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Job Work Entry
        </a>
        <a href="{{ route('jobworks.dashboard') }}" class="btn btn-outline">
            <i class="fa fa-chart-pie"></i> JW Dashboard
        </a>
    </div>
</div>

{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('jobworks.index')])

{{-- Filters: Client & Status & Search --}}
<div class="card mb-3" style="padding: 12px 16px;">
    <form method="GET" action="{{ route('jobworks.index') }}" class="d-flex gap-3 align-center flex-wrap" id="jwFilterForm">
        @if($preset)<input type="hidden" name="preset" value="{{ $preset }}">@endif
        @if($dateFrom)<input type="hidden" name="date_from" value="{{ $dateFrom }}">@endif
        @if($dateTo)<input type="hidden" name="date_to" value="{{ $dateTo }}">@endif

        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by JW #, Ref #, Client..." style="width: 100%; padding: 7px 12px; font-size: 13px; border: 1px solid var(--border); border-radius: 8px;">
        </div>

        <div style="min-width: 180px;">
            <select name="client_id" onchange="document.getElementById('jwFilterForm').submit()" style="width: 100%; padding: 7px 12px; font-size: 13px; border: 1px solid var(--border); border-radius: 8px;">
                <option value="">All Clients / Parties</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 160px;">
            <select name="status" onchange="document.getElementById('jwFilterForm').submit()" style="width: 100%; padding: 7px 12px; font-size: 13px; border: 1px solid var(--border); border-radius: 8px;">
                <option value="">All Statuses</option>
                <option value="Draft" {{ $status == 'Draft' ? 'selected' : '' }}>Draft</option>
                <option value="Material Received" {{ $status == 'Material Received' ? 'selected' : '' }}>Material Received</option>
                <option value="In Production" {{ $status == 'In Production' ? 'selected' : '' }}>In Production</option>
                <option value="Partially Completed" {{ $status == 'Partially Completed' ? 'selected' : '' }}>Partially Completed</option>
                <option value="Completed" {{ $status == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Delivered" {{ $status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="Cancelled" {{ $status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-outline btn-sm"><i class="fa fa-filter"></i> Apply</button>
            @if($search || $clientId || $status || $preset)
                <a href="{{ route('jobworks.index') }}" class="btn btn-outline btn-sm" style="color: #ef4444; margin-left: 4px;"><i class="fa fa-xmark"></i> Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-table-list"></i> Job Work Register ({{ number_format($totalCount) }} Orders)</h3>
        <span class="text-muted" style="font-size: 12px;">Total Material: <strong>{{ number_format($totalReceivedKg, 2) }} KG</strong></span>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($orders->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-scale-balanced"></i></div>
            <p>No Job Work orders found matching the filter.</p>
            <a href="{{ route('jobworks.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Job Work Entry</a>
        </div>
        @else

        {{-- Desktop Table --}}
        <div class="jw-desktop-table table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Job Work #</th>
                        <th>Date</th>
                        <th>Client / Party</th>
                        <th>Material (KG)</th>
                        <th>Gross PCS</th>
                        <th>Wastage</th>
                        <th>Net PCS</th>
                        <th>Delivered</th>
                        <th>Balance</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                    <tr>
                        <td>
                            <a href="{{ route('jobworks.show', $o) }}" style="font-weight: 800; color: var(--primary); text-decoration: none;">
                                {{ $o->job_work_number }}
                            </a>
                            @if($o->reference_number)
                                <div style="font-size: 11px; color: var(--text-muted);">Ref: {{ $o->reference_number }}</div>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 12.5px;">{{ $o->order_date->format('d M Y') }}</td>
                        <td>
                            <strong>{{ $o->client->name }}</strong>
                            @if($o->client->phone)
                                <div style="font-size: 11px; color: var(--text-muted);"><i class="fa fa-phone" style="font-size: 10px;"></i> {{ $o->client->phone }}</div>
                            @endif
                        </td>
                        <td><strong>{{ number_format($o->total_received_weight_kg, 2) }} KG</strong></td>
                        <td>{{ number_format($o->total_gross_pieces) }}</td>
                        <td><span class="badge badge-orange">{{ number_format($o->total_wastage_pieces) }}</span></td>
                        <td><strong style="color: #059669;">{{ number_format($o->total_net_pieces) }}</strong></td>
                        <td style="color: #2563eb; font-weight: 600;">{{ number_format($o->total_delivered_pieces) }}</td>
                        <td>
                            @if($o->total_balance_pieces > 0)
                                <span class="badge badge-red">{{ number_format($o->total_balance_pieces) }}</span>
                            @else
                                <span class="badge badge-green"><i class="fa fa-check"></i> Fulfilled</span>
                            @endif
                        </td>
                        <td class="fw-bold">₹{{ number_format($o->grand_total, 2) }}</td>
                        <td>
                            <span class="badge {{ $o->status === 'Delivered' ? 'badge-green' : ($o->status === 'In Production' ? 'badge-purple' : ($o->status === 'Material Received' ? 'badge-blue' : ($o->status === 'Partially Completed' ? 'badge-orange' : 'badge-gray'))) }}">
                                {{ $o->status }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div class="d-flex gap-1 justify-end">
                                <a href="{{ route('jobworks.show', $o) }}" class="btn btn-outline btn-sm btn-icon" title="View Details"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('jobworks.edit', $o) }}" class="btn btn-outline btn-sm btn-icon" title="Edit"><i class="fa fa-pen"></i></a>
                                <a href="{{ route('jobworks.print', $o) }}" class="btn btn-outline btn-sm btn-icon" target="_blank" title="Print Challan"><i class="fa fa-print"></i></a>
                                <a href="{{ route('jobworks.duplicate', $o) }}" class="btn btn-outline btn-sm btn-icon" title="Duplicate"><i class="fa fa-copy"></i></a>
                                <button class="btn btn-danger btn-sm btn-icon" title="Delete" onclick="deleteRecord('{{ route('jobworks.destroy', $o) }}', 'Job Work {{ $o->job_work_number }}')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc; border-top:2px solid #cbd5e1; font-weight:800;">
                        <th colspan="3" style="text-align:right; font-weight:800; font-size:13.5px; padding:14px;">
                            <i class="fa fa-calculator text-primary"></i> TOTAL ({{ number_format($totalCount) }} Orders):
                        </th>
                        <th style="padding:14px; color:var(--primary); font-size:14px;">{{ number_format($totalReceivedKg, 2) }} KG</th>
                        <th style="padding:14px;">{{ number_format($totalGross) }}</th>
                        <th style="padding:14px; color:#ea580c;">{{ number_format($totalWastage) }}</th>
                        <th style="padding:14px; color:#059669; font-size:14px;">{{ number_format($totalNet) }}</th>
                        <th style="padding:14px; color:#2563eb;">{{ number_format($totalDelivered) }}</th>
                        <th style="padding:14px; color:#dc2626;">{{ number_format($totalBalance) }}</th>
                        <th style="padding:14px; color:var(--primary); font-size:14.5px;">₹{{ number_format($totalGrandTotal, 2) }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="jw-mobile-card" style="padding: 12px;">
            @foreach($orders as $o)
            <div class="jw-card-item">
                <div class="jw-card-row">
                    <span style="font-weight: 800; font-size: 14px; color: var(--primary);">{{ $o->job_work_number }}</span>
                    <span class="badge {{ $o->status === 'Delivered' ? 'badge-green' : 'badge-blue' }}">{{ $o->status }}</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Client:</span>
                    <span class="jw-card-val">{{ $o->client->name }}</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Date:</span>
                    <span class="jw-card-val" style="color:var(--text-muted); font-weight:500;">{{ $o->order_date->format('d M Y') }}</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Received Material:</span>
                    <span class="jw-card-val">{{ number_format($o->total_received_weight_kg, 2) }} KG</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Gross / Net PCS:</span>
                    <span class="jw-card-val">{{ number_format($o->total_gross_pieces) }} / <span style="color:#059669;">{{ number_format($o->total_net_pieces) }} PCS</span></span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Delivered / Balance:</span>
                    <span class="jw-card-val">{{ number_format($o->total_delivered_pieces) }} / <span style="color:#ef4444;">{{ number_format($o->total_balance_pieces) }} PCS</span></span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Amount:</span>
                    <span class="jw-card-val" style="color:var(--primary); font-size:14px;">₹{{ number_format($o->grand_total, 2) }}</span>
                </div>
                <div class="jw-card-actions">
                    <a href="{{ route('jobworks.show', $o) }}" class="btn btn-outline btn-sm"><i class="fa fa-eye"></i> View</a>
                    <a href="{{ route('jobworks.edit', $o) }}" class="btn btn-outline btn-sm"><i class="fa fa-pen"></i> Edit</a>
                    <a href="{{ route('jobworks.print', $o) }}" class="btn btn-outline btn-sm" target="_blank"><i class="fa fa-print"></i> Print</a>
                    <button class="btn btn-danger btn-sm" onclick="deleteRecord('{{ route('jobworks.destroy', $o) }}', 'Job Work {{ $o->job_work_number }}')"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            @endforeach

            {{-- Mobile Summary Box --}}
            <div style="background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-top:10px;">
                <div style="font-weight:800; font-size:13px; margin-bottom:8px; border-bottom:1px solid #e2e8f0; padding-bottom:6px; display:flex; justify-content:space-between;">
                    <span><i class="fa fa-calculator text-primary"></i> TOTAL SUMMARY</span>
                    <span class="badge badge-gray">{{ number_format($totalCount) }} Orders</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Total Material:</span>
                    <span class="jw-card-val">{{ number_format($totalReceivedKg, 2) }} KG</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Net Finished PCS:</span>
                    <span class="jw-card-val" style="color:#059669;">{{ number_format($totalNet) }} PCS</span>
                </div>
                <div class="jw-card-row">
                    <span class="jw-card-label">Total Amount:</span>
                    <span class="jw-card-val" style="color:var(--primary); font-size:14px;">₹{{ number_format($totalGrandTotal, 2) }}</span>
                </div>
            </div>
        </div>

        <div style="padding: 12px 16px; border-top: 1px solid var(--border);">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
