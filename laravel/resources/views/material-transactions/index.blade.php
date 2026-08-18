@extends('layouts.app')
@section('title', 'Raw Material IN/OUT')
@section('page-title', 'Raw Material Transactions (IN / OUT)')

@section('content')
{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('material-transactions.index')])

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-right-left"></i> Material Transactions</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addTxnModal')">
            <i class="fa fa-plus"></i> Add Transaction
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($transactions->isEmpty())
        <div class="empty-state"><i class="fa fa-right-left"></i><p>No transactions recorded yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Material</th><th>Type</th><th>Qty</th><th>Rate</th><th>Amount</th><th>Supplier</th><th>Ref / Challan</th><th>Vehicle</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($transactions as $txn)
                <tr>
                    <td>{{ $txn->transaction_date->format('d M Y') }}</td>
                    <td class="fw-bold">{{ $txn->material->name }}</td>
                    <td>
                        <span class="badge {{ $txn->type === 'IN' ? 'badge-green' : 'badge-red' }}">
                            {{ $txn->type === 'IN' ? '▲ IN' : '▼ OUT' }}
                        </span>
                    </td>
                    <td>{{ number_format($txn->quantity, 2) }} {{ $txn->material->unit }}</td>
                    <td>{{ $txn->rate ? '₹'.number_format($txn->rate, 2) : '—' }}</td>
                    <td>{{ $txn->total_amount ? '₹'.number_format($txn->total_amount, 2) : '—' }}</td>
                    <td>{{ $txn->supplier?->name ?? '—' }}</td>
                    <td>{{ $txn->reference_no ?? '—' }}</td>
                    <td>{{ $txn->vehicle_no ?? '—' }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-icon"
                            onclick="deleteRecord('{{ route('material-transactions.destroy', $txn) }}', 'transaction')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 20px;">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal-overlay" id="addTxnModal">
    <div class="modal" style="max-width:580px">
        <div class="modal-header">
            <h3>Record Material Transaction</h3>
            <button class="modal-close" onclick="closeModal('addTxnModal')">✕</button>
        </div>
        <form id="addTxnForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Material *</label>
                        <select name="material_id" required>
                            <option value="">Select material</option>
                            @foreach($materials as $m)
                            <option value="{{ $m->id }}">[{{ $m->type }}] {{ $m->name }} (Stock: {{ number_format($m->stock_quantity, 1) }} {{ $m->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction Type *</label>
                        <select name="type" required>
                            <option value="IN">IN — Purchase / Receipt</option>
                            <option value="OUT">OUT — Issue / Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" step="0.01" min="0.01" required></div>
                    <div class="form-group"><label>Rate per unit (₹)</label><input type="number" name="rate" step="0.01" min="0"></div>
                    <div class="form-group"><label>Date *</label><input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Supplier (for IN)</label>
                        <select name="supplier_id">
                            <option value="">-- None --</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Challan / Bill No.</label><input type="text" name="reference_no"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Vehicle No.</label><input type="text" name="vehicle_no"></div>
                    <div class="form-group"><label>Remarks</label><input type="text" name="remarks"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addTxnModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Transaction</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('addTxnForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('material-transactions.store') }}', 'POST');
});
</script>
@endsection
