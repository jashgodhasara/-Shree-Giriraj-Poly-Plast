@extends('layouts.app')

@section('title', 'Purchase Order Details - ' . $purchaseOrder->po_number)
@section('page-title', 'PO Details')

@section('content')
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;">Purchase Order #{{ $purchaseOrder->po_number }}</h2>
        <p class="text-muted" style="font-size: 13px;">Created on {{ $purchaseOrder->po_date->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> Back to PO List
        </a>
        <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="btn btn-primary">
            <i class="fa fa-print"></i> Print / Download PO
        </a>
        @if($purchaseOrder->status === 'Pending')
            <button onclick="receiveStock({{ $purchaseOrder->id }}, '{{ $purchaseOrder->po_number }}')" class="btn btn-success">
                <i class="fa fa-box-open"></i> Receive Stock &amp; Post Ledger
            </button>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="form-row cols-3 mb-3">
            <div>
                <span class="text-muted" style="font-size: 11px; text-transform: uppercase;">Vendor / Supplier</span>
                <h3 style="font-size: 16px; font-weight: 700; margin-top: 2px;">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</h3>
                <p class="text-muted" style="font-size: 12px;">
                    GSTIN: {{ $purchaseOrder->supplier->gstin ?? 'N/A' }}<br>
                    Phone: {{ $purchaseOrder->supplier->phone ?? 'N/A' }}<br>
                    Address: {{ $purchaseOrder->supplier->address ?? 'N/A' }}
                </p>
            </div>
            <div>
                <span class="text-muted" style="font-size: 11px; text-transform: uppercase;">Order Terms</span>
                <p style="font-size: 13px; margin-top: 4px;">
                    <strong>Payment Terms:</strong> {{ $purchaseOrder->payment_terms ?? 'N/A' }}<br>
                    <strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : 'N/A' }}<br>
                    <strong>Delivery Address:</strong> {{ $purchaseOrder->delivery_address ?? 'Factory Gate' }}
                </p>
            </div>
            <div>
                <span class="text-muted" style="font-size: 11px; text-transform: uppercase;">PO Status</span>
                <div style="margin-top: 4px;">
                    @if($purchaseOrder->status === 'Received')
                        <span class="badge badge-green" style="font-size: 13px; padding: 6px 12px;"><i class="fa fa-check-circle"></i> Stock Received &amp; Credited</span>
                    @elseif($purchaseOrder->status === 'Cancelled')
                        <span class="badge badge-red" style="font-size: 13px; padding: 6px 12px;"><i class="fa fa-times-circle"></i> Cancelled</span>
                    @else
                        <span class="badge badge-orange" style="font-size: 13px; padding: 6px 12px;"><i class="fa fa-clock"></i> Pending Material Inward</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fa fa-list"></i> Order Items</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Material Item</th>
                    <th>HSN</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>GST Rate</th>
                    <th style="text-align: right;">Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->material->name ?? 'Material' }}</strong> ({{ $item->material->type ?? '' }})</td>
                    <td><code>{{ $item->hsn_code ?? '-' }}</code></td>
                    <td>{{ number_format($item->quantity, 2) }} {{ $item->material->unit ?? 'Kg' }}</td>
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->gst_rate, 0) }}%</td>
                    <td style="text-align: right; font-weight: 700;">₹{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-between" style="gap: 20px;">
    <div class="card" style="flex: 1;">
        <div class="card-body">
            <span class="text-muted" style="font-size: 11px; text-transform: uppercase;">Notes / Instructions</span>
            <p style="font-size: 13px; margin-top: 4px;">{{ $purchaseOrder->notes ?: 'No special notes.' }}</p>
        </div>
    </div>

    <div class="card" style="width: 340px;">
        <div class="card-body">
            <div class="d-flex justify-between mb-2">
                <span class="text-muted">Subtotal:</span>
                <strong>₹{{ number_format($purchaseOrder->subtotal, 2) }}</strong>
            </div>
            @if($purchaseOrder->igst > 0)
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">IGST:</span>
                    <strong>₹{{ number_format($purchaseOrder->igst, 2) }}</strong>
                </div>
            @else
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">CGST:</span>
                    <strong>₹{{ number_format($purchaseOrder->cgst, 2) }}</strong>
                </div>
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">SGST:</span>
                    <strong>₹{{ number_format($purchaseOrder->sgst, 2) }}</strong>
                </div>
            @endif
            <div class="divider"></div>
            <div class="d-flex justify-between align-center">
                <span style="font-size: 15px; font-weight: 700;">Grand Total:</span>
                <span style="font-size: 20px; font-weight: 800; color: var(--primary);">₹{{ number_format($purchaseOrder->grand_total, 2) }}</span>
            </div>
        </div>
    </div>
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
</script>
@endsection
