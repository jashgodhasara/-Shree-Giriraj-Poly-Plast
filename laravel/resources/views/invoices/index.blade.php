@extends('layouts.app')
@section('title', 'Invoices')
@section('page-title', 'All Invoices')

@section('content')
<style>
.inv-mobile-card { display:none; }
.inv-desktop-table { display:block; }
@media(max-width:768px){
    .inv-mobile-card { display:block; }
    .inv-desktop-table { display:none; }
    .inv-card-item {
        background:#fff; border-radius:12px; border:1px solid var(--border);
        padding:14px 16px; margin-bottom:12px;
        box-shadow:0 1px 4px rgba(0,0,0,.06);
    }
    .inv-card-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .inv-card-row:last-child { margin-bottom:0; }
    .inv-card-label { font-size:11px; color:var(--text-muted); font-weight:500; }
    .inv-card-val { font-size:13px; font-weight:700; color:var(--text); }
    .inv-card-actions { display:flex; gap:8px; margin-top:10px; padding-top:10px; border-top:1px solid var(--border); }
    .inv-card-actions .btn { flex:1; justify-content:center; }
}
</style>

{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('invoices.index')])

{{-- Summary Cards --}}
@if($preset || $dateFrom)
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:18px;">
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Total Invoices</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--primary);">{{ number_format($totalCount) }}</div>
    </div>
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Grand Total</div>
        <div style="font-size:1.4rem;font-weight:800;color:var(--primary);">₹{{ number_format($totalAmount, 2) }}</div>
    </div>
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Total Paid</div>
        <div style="font-size:1.4rem;font-weight:800;color:#10b981;">₹{{ number_format($totalPaid, 2) }}</div>
    </div>
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Pending</div>
        <div style="font-size:1.4rem;font-weight:800;color:#ef4444;">₹{{ number_format($totalAmount - $totalPaid, 2) }}</div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-receipt"></i> Invoice History</h3>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Create Bill
        </a>
    </div>
    <div class="card-body" style="padding:0">
        @if($invoices->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-receipt"></i></div>
            <p>No invoices{{ $preset ? ' found for this period' : ' yet' }}</p>
            @if(!$preset)<small>Create your first bill</small>@endif
        </div>
        @else

        {{-- DESKTOP TABLE --}}
        <div class="inv-desktop-table table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th><th>Customer</th><th>Date</th>
                        <th>Total</th><th>Paid</th><th>Pending</th>
                        <th>Transporter</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($invoices as $inv)
                <tr>
                    <td class="fw-bold" style="color:var(--primary)">{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->customer->name }}</td>
                    <td style="color:var(--text-muted)">{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td class="fw-bold">₹{{ number_format($inv->grand_total, 2) }}</td>
                    <td style="color:#10b981;font-weight:600">₹{{ number_format($inv->paid_amount, 2) }}</td>
                    <td style="color:#ef4444;font-weight:600">₹{{ number_format($inv->pending_amount, 2) }}</td>
                    <td>{{ $inv->transporter?->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $inv->status === 'Paid' ? 'badge-green' : ($inv->status === 'Partial' ? 'badge-orange' : 'badge-red') }}">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline btn-sm btn-icon" title="View"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('invoices.print', $inv) }}" class="btn btn-outline btn-sm btn-icon" target="_blank" title="Print"><i class="fa fa-print"></i></a>
                            <button class="btn btn-danger btn-sm btn-icon" onclick="deleteRecord('{{ route('invoices.destroy', $inv) }}', 'invoice')"><i class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc; border-top:2px solid #cbd5e1;">
                        <th colspan="3" style="text-align:right; font-weight:800; font-size:13.5px; color:var(--text-color, #1e293b); padding:14px;">
                            <i class="fa fa-calculator" style="color:var(--primary); margin-right:5px;"></i> TOTAL ({{ number_format($totalCount) }} Invoices):
                        </th>
                        <th style="font-weight:800; font-size:14.5px; color:var(--primary); padding:14px;">
                            ₹{{ number_format($totalAmount, 2) }}
                        </th>
                        <th style="font-weight:800; font-size:14.5px; color:#10b981; padding:14px;">
                            ₹{{ number_format($totalPaid, 2) }}
                        </th>
                        <th style="font-weight:800; font-size:14.5px; color:#ef4444; padding:14px;">
                            ₹{{ number_format($totalAmount - $totalPaid, 2) }}
                        </th>
                        <th colspan="3" style="padding:14px; text-align:right;">
                            <span class="badge badge-purple" style="font-size:12px; font-weight:700; padding:6px 12px;">
                                Total Pending: ₹{{ number_format($totalAmount - $totalPaid, 2) }}
                            </span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="inv-mobile-card" style="padding:12px">
            @foreach($invoices as $inv)
            <div class="inv-card-item">
                <div class="inv-card-row">
                    <span style="font-size:14px;font-weight:800;color:var(--primary)">{{ $inv->invoice_number }}</span>
                    <span class="badge {{ $inv->status === 'Paid' ? 'badge-green' : ($inv->status === 'Partial' ? 'badge-orange' : 'badge-red') }}">{{ $inv->status }}</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label">Customer</span>
                    <span class="inv-card-val">{{ $inv->customer->name }}</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label">Date</span>
                    <span class="inv-card-val" style="font-weight:500;color:var(--text-muted)">{{ $inv->invoice_date->format('d M Y') }}</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label">Total</span>
                    <span class="inv-card-val">₹{{ number_format($inv->grand_total, 2) }}</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label">Paid</span>
                    <span class="inv-card-val" style="color:#10b981">₹{{ number_format($inv->paid_amount, 2) }}</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label">Pending</span>
                    <span class="inv-card-val" style="color:#ef4444">₹{{ number_format($inv->pending_amount, 2) }}</span>
                </div>
                <div class="inv-card-actions">
                    <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline btn-sm"><i class="fa fa-eye"></i> View</a>
                    <a href="{{ route('invoices.print', $inv) }}" class="btn btn-outline btn-sm" target="_blank"><i class="fa fa-print"></i> Print</a>
                    <button class="btn btn-danger btn-sm" onclick="deleteRecord('{{ route('invoices.destroy', $inv) }}', 'invoice')"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            @endforeach

            {{-- Mobile Bottom Total Card --}}
            <div style="background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-top:10px; margin-bottom:8px;">
                <div style="font-weight:800; font-size:13px; color:var(--text-color, #1e293b); margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
                    <span><i class="fa fa-calculator text-primary"></i> TOTAL SUMMARY</span>
                    <span class="badge badge-gray">{{ number_format($totalCount) }} Invoices</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label" style="font-weight:700;">Total Invoiced:</span>
                    <span class="inv-card-val" style="color:var(--primary); font-size:14px;">₹{{ number_format($totalAmount, 2) }}</span>
                </div>
                <div class="inv-card-row">
                    <span class="inv-card-label" style="font-weight:700;">Total Paid:</span>
                    <span class="inv-card-val" style="color:#10b981; font-size:14px;">₹{{ number_format($totalPaid, 2) }}</span>
                </div>
                <div class="inv-card-row" style="padding-top:6px; border-top:1px dashed #e2e8f0;">
                    <span class="inv-card-label" style="font-weight:800; color:#ef4444;">Total Pending:</span>
                    <span class="inv-card-val" style="color:#ef4444; font-size:15px; font-weight:800;">₹{{ number_format($totalAmount - $totalPaid, 2) }}</span>
                </div>
            </div>
        </div>

        <div style="padding:12px 16px;border-top:1px solid var(--border)">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
