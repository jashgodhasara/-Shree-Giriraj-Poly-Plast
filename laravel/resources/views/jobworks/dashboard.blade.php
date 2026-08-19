@extends('layouts.app')
@section('title', 'Job Work Dashboard')
@section('page-title', 'Job Work Management & Production Overview')

@section('content')
<div class="d-flex justify-between align-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: var(--text);">
            <i class="fa fa-scale-balanced text-primary" style="margin-right: 6px;"></i> Job Work Automatic Weight &amp; Production Engine
        </h2>
        <p class="text-muted" style="font-size: 13px;">Manage client material intake, automatic piece production calculations, delivery tracking &amp; settlement</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('jobworks.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Job Work Entry
        </a>
        <a href="{{ route('jobworks.clients.index') }}" class="btn btn-outline">
            <i class="fa fa-users"></i> Job Work Clients
        </a>
    </div>
</div>

{{-- Top KPI Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 22px;">
    <div class="card" style="padding: 16px; margin: 0; border-left: 4px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">Total Job Works</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary);">{{ number_format($totalOrders) }}</div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Orders Registered</div>
    </div>

    <div class="card" style="padding: 16px; margin: 0; border-left: 4px solid #3b82f6; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">Material Received</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #2563eb;">{{ number_format($materialReceivedKg, 2) }} <span style="font-size: 13px; font-weight: 600;">KG</span></div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Raw Material Intake</div>
    </div>

    <div class="card" style="padding: 16px; margin: 0; border-left: 4px solid #8b5cf6; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">Gross Production</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #7c3aed;">{{ number_format($grossPieces) }} <span style="font-size: 13px; font-weight: 600;">PCS</span></div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Auto Calculated from Weight</div>
    </div>

    <div class="card" style="padding: 16px; margin: 0; border-left: 4px solid #f97316; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">Total Wastage / Salwaton</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #ea580c;">{{ number_format($wastagePieces) }} <span style="font-size: 13px; font-weight: 600;">PCS</span></div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Allowance / Process Loss</div>
    </div>

    <div class="card" style="padding: 16px; margin: 0; border-left: 4px solid #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">Net Finished Pieces</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #059669;">{{ number_format($netPieces) }} <span style="font-size: 13px; font-weight: 600;">PCS</span></div>
        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Ready For Dispatch</div>
    </div>
</div>

{{-- Secondary Row: Delivery & Financial KPI Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 22px;">
    <div class="card" style="padding: 14px; margin: 0; background: #f0fdf4; border: 1px solid #bbf7d0;">
        <div style="font-size: 11px; font-weight: 700; color: #166534;">DELIVERED TO CLIENT</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #15803d; margin-top: 4px;">{{ number_format($deliveredPieces) }} PCS</div>
        <div style="font-size: 11px; color: #166534;">Dispatched Products</div>
    </div>

    <div class="card" style="padding: 14px; margin: 0; background: #fef2f2; border: 1px solid #fecaca;">
        <div style="font-size: 11px; font-weight: 700; color: #991b1b;">PENDING DELIVERY BALANCE</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #b91c1c; margin-top: 4px;">{{ number_format($pendingPieces) }} PCS</div>
        <div style="font-size: 11px; color: #991b1b;">In Plant / Buffer</div>
    </div>

    <div class="card" style="padding: 14px; margin: 0; background: #eef2ff; border: 1px solid #c7d2fe;">
        <div style="font-size: 11px; font-weight: 700; color: #3730a3;">TOTAL JOB WORK AMOUNT</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #4338ca; margin-top: 4px;">₹{{ number_format($totalAmount, 2) }}</div>
        <div style="font-size: 11px; color: #3730a3;">Billed Job Work Revenue</div>
    </div>

    <div class="card" style="padding: 14px; margin: 0; background: #fffbeb; border: 1px solid #fde68a;">
        <div style="font-size: 11px; font-weight: 700; color: #92400e;">OUTSTANDING CLIENT RECEIVABLE</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #b45309; margin-top: 4px;">₹{{ number_format($balanceAmount, 2) }}</div>
        <div style="font-size: 11px; color: #92400e;">Collected: ₹{{ number_format($paidAmount, 2) }}</div>
    </div>
</div>

{{-- Status Pipeline Badges --}}
<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fa fa-bars-progress"></i> Production &amp; Fulfillment Pipeline</h3>
    </div>
    <div class="card-body" style="padding: 16px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @php
                $pipeline = [
                    'Draft'               => ['gray', 'fa-file-lines'],
                    'Material Received'   => ['blue', 'fa-truck-ramp-box'],
                    'In Production'       => ['purple', 'fa-gears'],
                    'Partially Completed' => ['orange', 'fa-spinner'],
                    'Completed'           => ['green', 'fa-circle-check'],
                    'Delivered'           => ['teal', 'fa-truck-fast'],
                ];
            @endphp
            @foreach($pipeline as $st => $meta)
                <div style="flex: 1; min-width: 140px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; margin-bottom: 4px;">
                        <i class="fa {{ $meta[1] }}"></i> {{ $st }}
                    </div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--text);">
                        {{ $statusCounts[$st] ?? 0 }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Recent Job Works Table --}}
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-clock-rotate-left"></i> Recent Job Work Orders</h3>
        <a href="{{ route('jobworks.index') }}" class="btn btn-outline btn-sm">View All Orders</a>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($recentOrders->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-scale-balanced"></i></div>
            <p>No Job Work orders created yet.</p>
            <a href="{{ route('jobworks.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create First Job Work Entry</a>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Job Work #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Material (KG)</th>
                        <th>Gross Pieces</th>
                        <th>Wastage</th>
                        <th>Net Pieces</th>
                        <th>Amount (₹)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('jobworks.show', $order) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                {{ $order->job_work_number }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $order->client->name }}</strong>
                            @if($order->client->company_name)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $order->client->company_name }}</div>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 12.5px;">{{ $order->order_date->format('d M Y') }}</td>
                        <td><strong>{{ number_format($order->total_received_weight_kg, 2) }} KG</strong></td>
                        <td>{{ number_format($order->total_gross_pieces) }} PCS</td>
                        <td><span class="badge badge-orange">{{ number_format($order->total_wastage_pieces) }}</span></td>
                        <td><strong style="color: #059669;">{{ number_format($order->total_net_pieces) }} PCS</strong></td>
                        <td class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</td>
                        <td>
                            <span class="badge {{ $order->status === 'Delivered' ? 'badge-green' : ($order->status === 'In Production' ? 'badge-purple' : ($order->status === 'Material Received' ? 'badge-blue' : 'badge-gray')) }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('jobworks.show', $order) }}" class="btn btn-outline btn-sm btn-icon" title="View"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('jobworks.print', $order) }}" class="btn btn-outline btn-sm btn-icon" target="_blank" title="Print"><i class="fa fa-print"></i></a>
                            </div>
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
