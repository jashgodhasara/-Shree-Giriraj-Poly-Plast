@extends('layouts.app')
@section('title', 'Warehouse & Location Master')
@section('page-title', 'Warehouse Master')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-warehouse text-primary"></i> Warehouses &amp; Plants ({{ $warehouses->count() }})</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddWarehouseModal()">
            <i class="fa fa-plus"></i> Add Warehouse
        </button>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Warehouse Name</th>
                        <th>Code</th>
                        <th>Location</th>
                        <th>Address</th>
                        <th>Primary Plant</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($warehouses as $wh)
                <tr>
                    <td>{{ $wh->id }}</td>
                    <td class="fw-bold">{{ $wh->name }}</td>
                    <td><code>{{ $wh->code }}</code></td>
                    <td>{{ $wh->location ?: '—' }}</td>
                    <td style="font-size:12.5px; color:var(--text-muted); max-width:200px;">{{ $wh->address ?: '—' }}</td>
                    <td>
                        @if($wh->is_primary)
                            <span class="badge badge-success"><i class="fa fa-check"></i> Primary Plant</span>
                        @else
                            <span class="text-muted">Secondary</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $wh->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($wh->status) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <button class="btn btn-outline btn-sm btn-icon" title="Edit Warehouse" onclick='openEditWarehouseModal(@json($wh))'>
                            <i class="fa fa-pen"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Warehouse Modal -->
<div class="modal-overlay" id="addWarehouseModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Add Warehouse Master</h3>
            <button class="modal-close" onclick="closeModal('addWarehouseModal')">✕</button>
        </div>
        <form id="addWarehouseForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Warehouse Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Plant 2 GIDC Warehouse">
                </div>
                <div class="form-group">
                    <label>Warehouse Code *</label>
                    <input type="text" name="code" required placeholder="e.g. WH-PLANT-2" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Location / City</label>
                    <input type="text" name="location" placeholder="e.g. Vatva, Ahmedabad">
                </div>
                <div class="form-group">
                    <label>Full Address</label>
                    <textarea name="address" rows="2" placeholder="Plant / warehouse full postal address..."></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_primary" value="1">
                        <span>Set as Default / Primary Warehouse</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addWarehouseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Warehouse</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Warehouse Modal -->
<div class="modal-overlay" id="editWarehouseModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Edit Warehouse</h3>
            <button class="modal-close" onclick="closeModal('editWarehouseModal')">✕</button>
        </div>
        <form id="editWarehouseForm">
            <input type="hidden" id="edit_wh_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Warehouse Name *</label>
                    <input type="text" name="name" id="edit_wh_name" required>
                </div>
                <div class="form-group">
                    <label>Warehouse Code *</label>
                    <input type="text" name="code" id="edit_wh_code" required style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Location / City</label>
                    <input type="text" name="location" id="edit_wh_location">
                </div>
                <div class="form-group">
                    <label>Full Address</label>
                    <textarea name="address" id="edit_wh_address" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_primary" id="edit_wh_is_primary" value="1">
                        <span>Primary Warehouse</span>
                    </label>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_wh_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editWarehouseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Warehouse</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
let editWhUrl = '';

function openAddWarehouseModal() {
    document.getElementById('addWarehouseForm').reset();
    openModal('addWarehouseModal');
}

function openEditWarehouseModal(wh) {
    editWhUrl = `/warehouses/${wh.id}`;
    document.getElementById('edit_wh_id').value = wh.id;
    document.getElementById('edit_wh_name').value = wh.name || '';
    document.getElementById('edit_wh_code').value = wh.code || '';
    document.getElementById('edit_wh_location').value = wh.location || '';
    document.getElementById('edit_wh_address').value = wh.address || '';
    document.getElementById('edit_wh_is_primary').checked = !!wh.is_primary;
    document.getElementById('edit_wh_status').value = wh.status || 'active';
    openModal('editWarehouseModal');
}

document.getElementById('addWarehouseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('warehouses.store') }}', 'POST');
});

document.getElementById('editWarehouseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editWhUrl, 'POST');
});
</script>
@endsection
