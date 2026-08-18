@extends('layouts.app')

@section('title', 'Purchase Orders - Shree Giriraj Poly Plast')
@section('page-title', 'Purchase Orders & Bills')

@section('content')
<div class="d-flex justify-between align-center mb-4" style="margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;">Purchase Orders & Vendor Bills</h2>
        <p class="text-muted" style="font-size: 13px;">Manage inbound materials, vendor orders, and stock receipts</p>
    </div>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Create Purchase Order
    </a>
</div>

{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('purchase-orders.index')])

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Expected Date</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                <tr>
                    <td>
                        <strong style="color: var(--primary);">{{ $po->po_number }}</strong>
                    </td>
                    <td>{{ $po->po_date->format('d M Y') }}</td>
                    <td>
                        <strong>{{ $po->supplier->name ?? 'N/A' }}</strong>
                        @if(!empty($po->supplier->gstin))
                            <br><small class="text-muted">GST: {{ $po->supplier->gstin }}</small>
                        @endif
                    </td>
                    <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : '-' }}</td>
                    <td><strong>₹{{ number_format($po->grand_total, 2) }}</strong></td>
                    <td>
                        @if($po->status === 'Received')
                            <span class="badge badge-green"><i class="fa fa-check-circle"></i> Stock Received</span>
                        @elseif($po->status === 'Cancelled')
                            <span class="badge badge-red"><i class="fa fa-times-circle"></i> Cancelled</span>
                        @else
                            <span class="badge badge-orange"><i class="fa fa-clock"></i> Pending Receipt</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-outline btn-sm btn-icon" title="View Details">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="btn btn-outline btn-sm btn-icon" title="Print PO">
                            <i class="fa fa-print"></i>
                        </a>
                        @if($po->status === 'Pending')
                            <button onclick="receiveStock({{ $po->id }}, '{{ $po->po_number }}')" class="btn btn-success btn-sm" title="Receive Stock & Post Ledger">
                                <i class="fa fa-box-open"></i> Receive Stock
                            </button>
                        @endif
                        <button onclick="deletePo({{ $po->id }})" class="btn btn-danger btn-sm btn-icon" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fa fa-cart-flatbed"></i></div>
                            <p>No Purchase Orders Found</p>
                            <small>Create your first vendor purchase order to manage material inward stock.</small>
                            <div class="mt-3">
                                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">
                                    + Create Purchase Order
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchaseOrders->hasPages())
    <div class="card-footer" style="padding: 16px;">
        {{ $purchaseOrders->links() }}
    </div>
    @endif
</div>

<script>
async function receiveStock(id, poNumber) {
    if (!confirm(`Are you sure you want to mark PO ${poNumber} as RECEIVED?\n\nThis will:\n1. Add material stock to inventory\n2. Post a Credit entry to the Supplier Ledger`)) {
        return;
    }

    try {
        const res = await fetch(`/purchase-orders/${id}/receive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        showToast('Error receiving stock', 'error');
    }
}

async function deletePo(id) {
    if (!confirm('Are you sure you want to delete this Purchase Order?')) return;
    try {
        const res = await fetch(`/purchase-orders/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        showToast('Error deleting PO', 'error');
    }
}
</script>
@endsection
