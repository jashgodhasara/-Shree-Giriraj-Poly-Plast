@extends('layouts.app')
@section('title', 'Warehouse & Location Master')
@section('page-title', 'Warehouse Master')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <div>
            <h3 style="margin:0; font-size:18px; font-weight:800;"><i class="fa fa-warehouse text-primary"></i> Warehouses &amp; Plants ({{ $warehouses->count() }})</h3>
            <p style="margin:4px 0 0 0; font-size:13px; color:var(--text-muted);">Manage manufacturing plants, secondary godowns, supervisor contacts and dispatch points</p>
        </div>
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
                        <th>Location &amp; Address</th>
                        <th>Supervisor / Contact</th>
                        <th>Primary Plant</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($warehouses as $wh)
                <tr>
                    <td>{{ $wh->id }}</td>
                    <td>
                        <div class="fw-bold" style="color:var(--text-dark); font-size:14px;">{{ $wh->name }}</div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                            <i class="fa fa-boxes-stacked"></i> {{ $wh->products_count }} Products | {{ $wh->stock_ledgers_count }} Movements
                        </div>
                    </td>
                    <td><code>{{ $wh->code }}</code></td>
                    <td>
                        <div style="font-weight:600; font-size:13px; color:var(--text-dark);">{{ $wh->location ?: '—' }}</div>
                        @if($wh->address)
                            <div style="font-size:11.5px; color:var(--text-muted); max-width:240px; margin-top:2px;">{{ $wh->address }}</div>
                        @endif
                    </td>
                    <td>
                        @if($wh->contact_person || $wh->contact_number)
                            @if($wh->contact_person)
                                <div style="font-weight:700; font-size:13px; color:var(--text-dark); display:flex; align-items:center; gap:5px;">
                                    <i class="fa-solid fa-user-tie text-primary"></i> {{ $wh->contact_person }}
                                </div>
                            @endif
                            @if($wh->contact_number)
                                <div style="font-size:12px; font-weight:600; color:var(--primary); margin-top:2px;">
                                    <a href="tel:{{ $wh->contact_number }}" style="text-decoration:none; color:inherit;">
                                        <i class="fa-solid fa-phone"></i> {{ $wh->contact_number }}
                                    </a>
                                </div>
                            @endif
                            @if($wh->email)
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    <i class="fa-solid fa-envelope"></i> {{ $wh->email }}
                                </div>
                            @endif
                        @else
                            <span style="font-size:12px; color:var(--text-muted);">No Contact Added</span>
                        @endif
                    </td>
                    <td>
                        @if($wh->is_primary)
                            <span class="badge badge-success"><i class="fa fa-check"></i> Primary Plant</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">Secondary</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $wh->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($wh->status) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <button class="btn btn-outline btn-sm btn-icon" title="Edit Warehouse" onclick="openEditWarehouseModal({{ $wh->id }})">
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
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-warehouse text-primary"></i> Add Warehouse Master</h3>
            <button class="modal-close" onclick="closeModal('addWarehouseModal')">✕</button>
        </div>
        <form id="addWarehouseForm">
            <div class="modal-body">
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Warehouse / Plant Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Plant 2 GIDC Warehouse" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Code *</label>
                        <input type="text" name="code" required placeholder="WH-PLANT-2" style="text-transform:uppercase;" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Contact Person / Manager (સુપરવાઇઝર)</label>
                        <input type="text" name="contact_person" placeholder="e.g. Ramesh Patel" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Contact Mobile / Phone (મોબાઈલ નંબર)</label>
                        <input type="text" name="contact_number" placeholder="e.g. +91 98765 43210" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Location / City (શહેર / જીઆઈડીસી)</label>
                        <input type="text" name="location" placeholder="e.g. GIDC Lodhika, Rajkot" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Email Address (ઇમેઇલ)</label>
                        <input type="email" name="email" placeholder="plant@shreegiriraj.com" class="form-control">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label">Full Address (પૂરું સરનામું)</label>
                    <textarea name="address" rows="2" placeholder="Plant / warehouse full postal address..." class="form-control"></textarea>
                </div>

                <div>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:13px;">
                        <input type="checkbox" name="is_primary" value="1">
                        <span>Set as Default / Primary Manufacturing Plant</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addWarehouseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Warehouse</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Warehouse Modal -->
<div class="modal-overlay" id="editWarehouseModal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square text-primary"></i> Edit Warehouse</h3>
            <button class="modal-close" onclick="closeModal('editWarehouseModal')">✕</button>
        </div>
        <form id="editWarehouseForm">
            <input type="hidden" id="edit_wh_id">
            <div class="modal-body">
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Warehouse / Plant Name *</label>
                        <input type="text" name="name" id="edit_wh_name" required class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Code *</label>
                        <input type="text" name="code" id="edit_wh_code" required style="text-transform:uppercase;" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Contact Person / Manager (સુપરવાઇઝર)</label>
                        <input type="text" name="contact_person" id="edit_wh_contact_person" placeholder="e.g. Ramesh Patel" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Contact Mobile / Phone (મોબાઈલ નંબર)</label>
                        <input type="text" name="contact_number" id="edit_wh_contact_number" placeholder="e.g. +91 98765 43210" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Location / City</label>
                        <input type="text" name="location" id="edit_wh_location" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="edit_wh_email" class="form-control">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label">Full Address</label>
                    <textarea name="address" id="edit_wh_address" rows="2" class="form-control"></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; align-items:center;">
                    <div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:13px;">
                            <input type="checkbox" name="is_primary" id="edit_wh_is_primary" value="1">
                            <span>Primary Plant</span>
                        </label>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_wh_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editWarehouseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Warehouse</button>
            </div>
        </form>
    </div>
</div>

<script>
window.warehousesData = @json($warehouses);
window.warehousesMap = {};
window.warehousesData.forEach(function(w) { window.warehousesMap[w.id] = w; });
window.editWhUrl = '';

window.openAddWarehouseModal = function() {
    document.getElementById('addWarehouseForm').reset();
    openModal('addWarehouseModal');
};

window.openEditWarehouseModal = function(whId) {
    const wh = window.warehousesMap[whId] || {};
    window.editWhUrl = `/warehouses/${wh.id || whId}`;
    document.getElementById('edit_wh_id').value = wh.id || whId;
    document.getElementById('edit_wh_name').value = wh.name || '';
    document.getElementById('edit_wh_code').value = wh.code || '';
    document.getElementById('edit_wh_contact_person').value = wh.contact_person || '';
    document.getElementById('edit_wh_contact_number').value = wh.contact_number || '';
    document.getElementById('edit_wh_email').value = wh.email || '';
    document.getElementById('edit_wh_location').value = wh.location || '';
    document.getElementById('edit_wh_address').value = wh.address || '';
    document.getElementById('edit_wh_is_primary').checked = !!wh.is_primary;
    document.getElementById('edit_wh_status').value = wh.status || 'active';
    openModal('editWarehouseModal');
};

document.getElementById('addWarehouseForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('warehouses.store') }}', 'POST', function() {
        window.location.reload();
    });
});

document.getElementById('editWarehouseForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, window.editWhUrl, 'POST', function() {
        window.location.reload();
    });
});
</script>
@endsection
