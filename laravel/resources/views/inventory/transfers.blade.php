@extends('layouts.app')
@section('title', 'Inter-Warehouse Stock Transfers')
@section('page-title', 'Stock Transfers')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-truck-ramp-box text-primary"></i> Stock Transfers ({{ $transfers->total() }})</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddTransferModal()">
            <i class="fa fa-plus"></i> New Transfer
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($transfers->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-truck-ramp-box"></i></div>
            <p>No inter-warehouse stock transfers recorded yet.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Transfer #</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>From Warehouse</th>
                        <th>To Warehouse</th>
                        <th style="text-align:right;">Transferred Qty</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($transfers as $tr)
                <tr>
                    <td><code>{{ $tr->transfer_number }}</code></td>
                    <td>{{ $tr->transfer_date ? $tr->transfer_date->format('d-m-Y') : '—' }}</td>
                    <td>
                        <span class="fw-bold">{{ $tr->product->name ?? '—' }}</span>
                        <div style="font-size:11px; color:var(--text-muted);">{{ $tr->product->sku ?? '' }}</div>
                    </td>
                    <td><span class="badge badge-warning">{{ $tr->fromWarehouse->name ?? 'Source WH' }}</span></td>
                    <td><span class="badge badge-success">{{ $tr->toWarehouse->name ?? 'Dest WH' }}</span></td>
                    <td style="text-align:right;" class="fw-bold text-primary">{{ number_format($tr->quantity, 2) }} {{ $tr->unit }}</td>
                    <td><span class="badge badge-success">{{ $tr->status }}</span></td>
                    <td style="font-size:12px;">{{ $tr->creator->name ?? 'Admin' }}</td>
                    <td style="font-size:11.5px; color:var(--text-muted);">{{ $tr->remarks ?: '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px;">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- New Transfer Modal -->
<div class="modal-overlay" id="addTransferModal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fa fa-truck-ramp-box text-primary"></i> Inter-Warehouse Transfer</h3>
            <button class="modal-close" onclick="closeModal('addTransferModal')">✕</button>
        </div>
        <form id="addTransferForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Product *</label>
                    <select name="product_id" required>
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) — Available: {{ $p->stock_quantity }} {{ $p->unit ?: 'PCS' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>From Warehouse (Source) *</label>
                        <select name="from_warehouse_id" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To Warehouse (Destination) *</label>
                        <select name="to_warehouse_id" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Transfer Quantity *</label>
                    <input type="number" step="0.01" name="quantity" required placeholder="Quantity to transfer">
                </div>
                <div class="form-group">
                    <label>Remarks / Dispatch Note</label>
                    <textarea name="remarks" rows="2" placeholder="Vehicle number, dispatch notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addTransferModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Execute Transfer</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openAddTransferModal() {
    document.getElementById('addTransferForm').reset();
    openModal('addTransferModal');
}

document.getElementById('addTransferForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('inventory.transfers.store') }}', 'POST');
});
</script>
@endsection
