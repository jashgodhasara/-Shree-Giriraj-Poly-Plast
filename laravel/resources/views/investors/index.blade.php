@extends('layouts.app')
@section('title', 'Investors')
@section('page-title', 'Investors')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-hand-holding-dollar"></i> Investors</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addInvestorModal')">
            <i class="fa fa-plus"></i> Add Investor
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($investors->isEmpty())
        <div class="empty-state"><i class="fa fa-hand-holding-dollar"></i><p>No investors added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Investment Amount</th><th>Notes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($investors as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td class="fw-bold">{{ $inv->name }}</td>
                    <td>{{ $inv->phone ?? '—' }}</td>
                    <td>{{ $inv->email ?? '—' }}</td>
                    <td class="fw-bold">₹{{ number_format($inv->investment_amount, 2) }}</td>
                    <td>{{ Str::limit($inv->notes, 40) ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editInvestor({{ $inv->id }}, {{ json_encode($inv->name) }}, {{ json_encode($inv->phone) }}, {{ json_encode($inv->email) }}, {{ json_encode($inv->address) }}, {{ $inv->investment_amount }}, {{ json_encode($inv->notes) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('investors.destroy', $inv) }}', 'investor')">
                                <i class="fa fa-trash"></i>
                            </button>
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

<!-- Add Modal -->
<div class="modal-overlay" id="addInvestorModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Investor</h3>
            <button class="modal-close" onclick="closeModal('addInvestorModal')">✕</button>
        </div>
        <form id="addInvestorForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                    <div class="form-group"><label>Investment Amount (₹)</label><input type="number" name="investment_amount" step="0.01" value="0"></div>
                </div>
                <div class="form-group"><label>Address</label><textarea name="address" rows="2"></textarea></div>
                <div class="form-group"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addInvestorModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Investor</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editInvestorModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Investor</h3>
            <button class="modal-close" onclick="closeModal('editInvestorModal')">✕</button>
        </div>
        <form id="editInvestorForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label>Name *</label><input type="text" id="ei_name" name="name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" id="ei_phone" name="phone"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Email</label><input type="email" id="ei_email" name="email"></div>
                    <div class="form-group"><label>Investment Amount (₹)</label><input type="number" id="ei_amount" name="investment_amount" step="0.01"></div>
                </div>
                <div class="form-group"><label>Address</label><textarea id="ei_address" name="address" rows="2"></textarea></div>
                <div class="form-group"><label>Notes</label><textarea id="ei_notes" name="notes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editInvestorModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Investor</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

document.getElementById('addInvestorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('investors.store') }}', 'POST');
});

function editInvestor(id, name, phone, email, address, amount, notes) {
    editUrl = `/investors/${id}`;
    document.getElementById('ei_name').value = name || '';
    document.getElementById('ei_phone').value = phone || '';
    document.getElementById('ei_email').value = email || '';
    document.getElementById('ei_address').value = address || '';
    document.getElementById('ei_amount').value = amount || 0;
    document.getElementById('ei_notes').value = notes || '';
    openModal('editInvestorModal');
}

document.getElementById('editInvestorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
