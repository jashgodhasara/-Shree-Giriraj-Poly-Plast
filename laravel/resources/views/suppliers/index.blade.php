@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-truck-field"></i> Supplier List</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addSupplierModal')">
            <i class="fa fa-plus"></i> Add Supplier
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($suppliers->isEmpty())
        <div class="empty-state"><i class="fa fa-truck-field"></i><p>No suppliers added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>GSTIN</th><th>Address</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($suppliers as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td class="fw-bold">{{ $s->name }}</td>
                    <td>{{ $s->phone ?? '—' }}</td>
                    <td>{{ $s->email ?? '—' }}</td>
                    <td><code>{{ $s->gstin ?? '—' }}</code></td>
                    <td>{{ Str::limit($s->address, 40) ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editSupplier({{ $s->id }}, {{ json_encode($s->name) }}, {{ json_encode($s->phone) }}, {{ json_encode($s->email) }}, {{ json_encode($s->gstin) }}, {{ json_encode($s->address) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('suppliers.destroy', $s) }}', 'supplier')">
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
<div class="modal-overlay" id="addSupplierModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Supplier</h3>
            <button class="modal-close" onclick="closeModal('addSupplierModal')">✕</button>
        </div>
        <form id="addSupplierForm">
            <div class="modal-body">
                <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                </div>
                <div class="form-group"><label>GSTIN</label><input type="text" name="gstin" maxlength="15"></div>
                <div class="form-group"><label>Address</label><textarea name="address"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addSupplierModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editSupplierModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Supplier</h3>
            <button class="modal-close" onclick="closeModal('editSupplierModal')">✕</button>
        </div>
        <form id="editSupplierForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-group"><label>Name *</label><input type="text" id="es_name" name="name" required></div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Phone</label><input type="text" id="es_phone" name="phone"></div>
                    <div class="form-group"><label>Email</label><input type="email" id="es_email" name="email"></div>
                </div>
                <div class="form-group"><label>GSTIN</label><input type="text" id="es_gstin" name="gstin" maxlength="15"></div>
                <div class="form-group"><label>Address</label><textarea id="es_address" name="address"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editSupplierModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Supplier</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('suppliers.store') }}', 'POST');
});

function editSupplier(id, name, phone, email, gstin, address) {
    editUrl = `/suppliers/${id}`;
    document.getElementById('es_name').value = name || '';
    document.getElementById('es_phone').value = phone || '';
    document.getElementById('es_email').value = email || '';
    document.getElementById('es_gstin').value = gstin || '';
    document.getElementById('es_address').value = address || '';
    openModal('editSupplierModal');
}

document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
