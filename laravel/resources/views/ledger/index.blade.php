@extends('layouts.app')
@section('title', 'Ledger / Book Keeping')
@section('page-title', 'Ledger / Book Keeping')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-book"></i> Ledger Entries</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addLedgerModal')">
            <i class="fa fa-plus"></i> Add Entry
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($entries->isEmpty())
        <div class="empty-state"><i class="fa fa-book"></i><p>No ledger entries yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Entity Type</th><th>Party</th><th>Type</th><th>Amount</th><th>HSN Code</th><th>CSM Code</th><th>Description</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($entries as $e)
                <tr>
                    <td>{{ $e->transaction_date->format('d M Y') }}</td>
                    <td><span class="badge badge-blue">{{ $e->entity_type }}</span></td>
                    <td>{{ $e->entityName() }}</td>
                    <td>
                        <span class="badge {{ $e->type === 'Credit' ? 'badge-green' : 'badge-red' }}">
                            {{ $e->type }}
                        </span>
                    </td>
                    <td class="fw-bold">₹{{ number_format($e->amount, 2) }}</td>
                    <td>{{ $e->hsn_code ?? '—' }}</td>
                    <td>{{ $e->csm_code ?? '—' }}</td>
                    <td>{{ Str::limit($e->description, 40) ?? '—' }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-icon"
                            onclick="deleteRecord('{{ route('ledger.destroy', $e) }}', 'ledger entry')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 20px;">
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addLedgerModal">
    <div class="modal" style="max-width:560px">
        <div class="modal-header">
            <h3>Add Ledger Entry</h3>
            <button class="modal-close" onclick="closeModal('addLedgerModal')">✕</button>
        </div>
        <form id="addLedgerForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Entity Type *</label>
                        <select name="entity_type" id="entityType" onchange="onEntityTypeChange()" required>
                            <option value="">Select type</option>
                            <option value="Customer">Customer</option>
                            <option value="Supplier">Supplier</option>
                            <option value="Investor">Investor</option>
                            <option value="Job Work">Job Work</option>
                        </select>
                    </div>
                    <div class="form-group" id="entityGroup">
                        <label>Party *</label>
                        <select name="entity_id" id="entitySelect" required>
                            <option value="">Select entity type first</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="Debit">Debit (Expense / Payment Out)</option>
                            <option value="Credit">Credit (Receipt / Payment In)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Amount (₹) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" name="hsn_code">
                    </div>
                    <div class="form-group">
                        <label>CSM Code</label>
                        <input type="text" name="csm_code">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description / Narration</label>
                    <textarea name="description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addLedgerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const customers = @json($customers);
const suppliers  = @json($suppliers);
const investors  = @json($investors);
const jobWorks   = @json($jobWorks);

function onEntityTypeChange() {
    const type = document.getElementById('entityType').value;
    const sel  = document.getElementById('entitySelect');
    sel.innerHTML = '<option value="">Select...</option>';

    if (type === 'Customer') {
        customers.forEach(c => sel.innerHTML += `<option value="${c.id}">${c.name}</option>`);
    } else if (type === 'Supplier') {
        suppliers.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.name}</option>`);
    } else if (type === 'Investor') {
        investors.forEach(i => sel.innerHTML += `<option value="${i.id}">${i.name}</option>`);
    } else if (type === 'Job Work') {
        jobWorks.forEach(j => sel.innerHTML += `<option value="${j.id}">${j.party_name}</option>`);
    }
}

document.getElementById('addLedgerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('ledger.store') }}', 'POST');
});
</script>
@endsection
