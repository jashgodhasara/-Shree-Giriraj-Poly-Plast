@extends('layouts.app')
@section('title', 'Materials')
@section('page-title', 'Materials & Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-boxes-stacked"></i> Materials List</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addMaterialModal')">
            <i class="fa fa-plus"></i> Add Material
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($materials->isEmpty())
        <div class="empty-state"><i class="fa fa-boxes-stacked"></i><p>No materials added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Type</th><th>Name</th><th>Unit</th><th>Grade/Variation</th><th>Temp</th><th>Size</th><th>Stock</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($materials as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>
                        <span class="badge {{ $m->type === 'Raw Material' ? 'badge-orange' : ($m->type === 'Additive' ? 'badge-blue' : 'badge-green') }}">
                            {{ $m->type }}
                        </span>
                    </td>
                    <td class="fw-bold">{{ $m->name }}</td>
                    <td>{{ $m->unit ?? '—' }}</td>
                    <td>{{ $m->grade_variation ?? '—' }}</td>
                    <td>{{ $m->temp ?? '—' }}</td>
                    <td>{{ $m->size ?? '—' }}</td>
                    <td class="fw-bold">{{ number_format($m->stock_quantity, 2) }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editMaterial({{ $m->id }}, {{ json_encode($m->type) }}, {{ json_encode($m->name) }}, {{ json_encode($m->unit) }}, {{ json_encode($m->grade_variation) }}, {{ json_encode($m->temp) }}, {{ json_encode($m->size) }}, {{ $m->stock_quantity }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('materials.destroy', $m) }}', 'material')">
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
<div class="modal-overlay" id="addMaterialModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Material</h3>
            <button class="modal-close" onclick="closeModal('addMaterialModal')">✕</button>
        </div>
        <form id="addMaterialForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="">Select type</option>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Additive">Additive</option>
                            <option value="Final Product">Final Product</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Unit</label><input type="text" name="unit" placeholder="Kg, Pcs..."></div>
                    <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock_quantity" step="0.01" value="0"></div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Grade/Variation</label><input type="text" name="grade_variation"></div>
                    <div class="form-group"><label>Temp</label><input type="text" name="temp"></div>
                    <div class="form-group"><label>Size</label><input type="text" name="size"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editMaterialModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Material</h3>
            <button class="modal-close" onclick="closeModal('editMaterialModal')">✕</button>
        </div>
        <form id="editMaterialForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Type *</label>
                        <select id="em_type" name="type" required>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Additive">Additive</option>
                            <option value="Final Product">Final Product</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Name *</label><input type="text" id="em_name" name="name" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Unit</label><input type="text" id="em_unit" name="unit"></div>
                    <div class="form-group"><label>Stock Quantity</label><input type="number" id="em_stock" name="stock_quantity" step="0.01"></div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Grade/Variation</label><input type="text" id="em_grade" name="grade_variation"></div>
                    <div class="form-group"><label>Temp</label><input type="text" id="em_temp" name="temp"></div>
                    <div class="form-group"><label>Size</label><input type="text" id="em_size" name="size"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

document.getElementById('addMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('materials.store') }}', 'POST');
});

function editMaterial(id, type, name, unit, grade, temp, size, stock) {
    editUrl = `/materials/${id}`;
    document.getElementById('em_type').value = type || 'Raw Material';
    document.getElementById('em_name').value = name || '';
    document.getElementById('em_unit').value = unit || '';
    document.getElementById('em_stock').value = stock || 0;
    document.getElementById('em_grade').value = grade || '';
    document.getElementById('em_temp').value = temp || '';
    document.getElementById('em_size').value = size || '';
    openModal('editMaterialModal');
}

document.getElementById('editMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
