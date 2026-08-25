@extends('layouts.app')
@section('title', 'Unit Master & Unit Conversions')
@section('page-title', 'Unit Management')

@section('content')
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    <!-- Unit Master List -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-ruler-combined text-primary"></i> Unit Master</h3>
            <button class="btn btn-primary btn-sm" onclick="openAddUnitModal()">
                <i class="fa fa-plus"></i> Add Unit
            </button>
        </div>
        <div class="card-body" style="padding:0">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Unit Name</th>
                            <th>Code</th>
                            <th>Symbol</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($units as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td class="fw-bold">{{ $u->name }}</td>
                        <td><code>{{ $u->code }}</code></td>
                        <td>{{ $u->symbol ?: '—' }}</td>
                        <td>
                            <span class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $u->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-outline btn-sm btn-icon" title="Edit Unit" onclick='openEditUnitModal(@json($u))'>
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

    <!-- Unit Conversions Matrix -->
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-arrows-rotate text-primary"></i> Unit Conversion Rules</h3>
            <button class="btn btn-primary btn-sm" onclick="openAddConversionModal()">
                <i class="fa fa-plus"></i> Add Conversion Rule
            </button>
        </div>
        <div class="card-body" style="padding:0">
            @if($conversions->isEmpty())
                <div class="empty-state">
                    <p>No unit conversion rules defined.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>From Unit</th>
                                <th>Multiplier Factor</th>
                                <th>To Unit</th>
                                <th>Formula</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($conversions as $cv)
                        <tr>
                            <td class="fw-bold">{{ $cv->fromUnit->name ?? '—' }} ({{ $cv->fromUnit->code ?? '' }})</td>
                            <td class="fw-bold text-primary">{{ $cv->operator }} {{ (float) $cv->conversion_factor }}</td>
                            <td class="fw-bold">{{ $cv->toUnit->name ?? '—' }} ({{ $cv->toUnit->code ?? '' }})</td>
                            <td><code>1 {{ $cv->fromUnit->code ?? '' }} = {{ (float) $cv->conversion_factor }} {{ $cv->toUnit->code ?? '' }}</code></td>
                            <td>
                                <button class="btn btn-danger btn-sm btn-icon" title="Delete Rule" onclick="deleteRecord('/unit-conversions/{{ $cv->id }}', 'conversion rule', this)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal-overlay" id="addUnitModal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <h3>Add Unit Master</h3>
            <button class="modal-close" onclick="closeModal('addUnitModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('units.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Unit Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Kilogram, Piece, Box">
                </div>
                <div class="form-group">
                    <label>Unit Code *</label>
                    <input type="text" name="code" required placeholder="e.g. KG, PCS, BOX" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Symbol</label>
                    <input type="text" name="symbol" placeholder="e.g. kg, pcs">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addUnitModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Unit</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal-overlay" id="editUnitModal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <h3>Edit Unit Master</h3>
            <button class="modal-close" onclick="closeModal('editUnitModal')">✕</button>
        </div>
        <form id="editUnitForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Unit Name *</label>
                    <input type="text" name="name" id="edit_u_name" required>
                </div>
                <div class="form-group">
                    <label>Unit Code *</label>
                    <input type="text" name="code" id="edit_u_code" required style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Symbol</label>
                    <input type="text" name="symbol" id="edit_u_symbol">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" id="edit_u_status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editUnitModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Unit</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Conversion Modal -->
<div class="modal-overlay" id="addConversionModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3>Add Unit Conversion Rule</h3>
            <button class="modal-close" onclick="closeModal('addConversionModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('units.conversions.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>From Unit *</label>
                        <select name="from_unit_id" required>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To Unit *</label>
                        <select name="to_unit_id" required>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Conversion Multiplier *</label>
                        <input type="number" step="0.000001" name="conversion_factor" required placeholder="e.g. 1000">
                    </div>
                    <div class="form-group">
                        <label>Operator</label>
                        <select name="operator">
                            <option value="*">Multiply (*)</option>
                            <option value="/">Divide (/)</option>
                        </select>
                    </div>
                </div>
                <small style="color:var(--text-muted);">Example: 1 KG = 1000 Gram (Factor = 1000, Operator = *)</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addConversionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Conversion Rule</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openAddUnitModal() {
    openModal('addUnitModal');
}
function openAddConversionModal() {
    openModal('addConversionModal');
}
function openEditUnitModal(u) {
    const form = document.getElementById('editUnitForm');
    form.action = `/units/${u.id}`;
    document.getElementById('edit_u_name').value = u.name || '';
    document.getElementById('edit_u_code').value = u.code || '';
    document.getElementById('edit_u_symbol').value = u.symbol || '';
    document.getElementById('edit_u_status').value = u.is_active ? '1' : '0';
    openModal('editUnitModal');
}
</script>
@endsection
