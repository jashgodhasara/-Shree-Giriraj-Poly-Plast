@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-users"></i> Customer List</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addCustomerModal')">
            <i class="fa fa-plus"></i> Add Customer
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($customers->isEmpty())
        <div class="empty-state"><i class="fa fa-users"></i><p>No customers added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>GSTIN</th><th>State</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($customers as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td class="fw-bold">{{ $c->name }}</td>
                    <td>{{ $c->phone ?? '—' }}</td>
                    <td>{{ $c->email ?? '—' }}</td>
                    <td><code>{{ $c->gstin ?? '—' }}</code></td>
                    <td>{{ $c->state ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editCustomer({{ $c->id }}, {{ json_encode($c->name) }}, {{ json_encode($c->phone) }}, {{ json_encode($c->email) }}, {{ json_encode($c->address) }}, {{ json_encode($c->gstin) }}, {{ json_encode($c->state) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('customers.destroy', $c) }}', 'customer')">
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
<div class="modal-overlay" id="addCustomerModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Customer</h3>
            <button class="modal-close" onclick="closeModal('addCustomerModal')">✕</button>
        </div>
        <form id="addCustomerForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                    <div class="form-group"><label>State</label><input type="text" name="state" placeholder="e.g. Gujarat"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>GSTIN</label><input type="text" name="gstin" maxlength="15"></div>
                    <div class="form-group"><label>Address</label><textarea name="address" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editCustomerModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Customer</h3>
            <button class="modal-close" onclick="closeModal('editCustomerModal')">✕</button>
        </div>
        <form id="editCustomerForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label>Name *</label><input type="text" id="edit_name" name="name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" id="edit_phone" name="phone"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Email</label><input type="email" id="edit_email" name="email"></div>
                    <div class="form-group"><label>State</label><input type="text" id="edit_state" name="state"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>GSTIN</label><input type="text" id="edit_gstin" name="gstin" maxlength="15"></div>
                    <div class="form-group"><label>Address</label><textarea id="edit_address" name="address" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

document.getElementById('addCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('customers.store') }}', 'POST');
});

function editCustomer(id, name, phone, email, address, gstin, state) {
    editUrl = `/customers/${id}`;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_phone').value = phone || '';
    document.getElementById('edit_email').value = email || '';
    document.getElementById('edit_address').value = address || '';
    document.getElementById('edit_gstin').value = gstin || '';
    document.getElementById('edit_state').value = state || '';
    openModal('editCustomerModal');
}

document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
