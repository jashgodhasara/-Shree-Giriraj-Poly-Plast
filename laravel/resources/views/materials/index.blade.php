@extends('layouts.app')
@section('title', 'Materials')
@section('page-title', 'Materials & Inventory')

@section('content')
<style>
.material-thumb {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.material-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.material-thumb-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    border: 1px dashed var(--border);
}
.image-preview-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
    padding: 10px;
    border: 1.5px dashed var(--border);
    border-radius: var(--radius-sm);
    background: #fafafa;
}
.preview-img-box {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border);
    display: none;
}
.photo-modal-img {
    max-width: 100%;
    max-height: 70vh;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    display: block;
    margin: 0 auto;
    object-fit: contain;
}
</style>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-boxes-stacked"></i> Materials List</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddMaterialModal()">
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
                    <tr><th>#</th><th>Photo</th><th>Type</th><th>Name</th><th>Unit</th><th>Grade/Variation</th><th>Temp</th><th>Size</th><th>Stock</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($materials as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>
                        @if($m->image)
                            <img src="{{ asset($m->image) }}" alt="{{ $m->name }}" class="material-thumb" onclick="viewPhoto('{{ asset($m->image) }}', '{{ e($m->name) }}')">
                        @else
                            <div class="material-thumb-placeholder">
                                <i class="fa fa-box-open"></i>
                            </div>
                        @endif
                    </td>
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
                                onclick="editMaterial({{ $m->id }}, {{ json_encode($m->type) }}, {{ json_encode($m->name) }}, {{ json_encode($m->unit) }}, {{ json_encode($m->grade_variation) }}, {{ json_encode($m->temp) }}, {{ json_encode($m->size) }}, {{ $m->stock_quantity }}, {{ json_encode($m->image ? asset($m->image) : null) }})">
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
        <form id="addMaterialForm" enctype="multipart/form-data">
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
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Material Photo / Image</label>
                    <input type="file" name="image" id="add_mat_img_input" accept="image/*" onchange="previewMatImage(this, 'add_mat_preview')">
                    <div class="image-preview-container" id="add_mat_preview_container" style="display:none;">
                        <img id="add_mat_preview" class="preview-img-box" alt="Preview">
                        <span style="font-size:12px;color:var(--text-muted);">Selected material image</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Material</button>
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
        <form id="editMaterialForm" enctype="multipart/form-data">
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
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Material Photo / Image</label>
                    <input type="file" name="image" id="edit_mat_img_input" accept="image/*" onchange="previewMatImage(this, 'edit_mat_preview')">
                    <div class="image-preview-container" id="edit_mat_preview_container" style="display:none;">
                        <img id="edit_mat_preview" class="preview-img-box" alt="Preview">
                        <label style="font-size:12px;color:#dc2626;cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1" id="edit_mat_remove_chk"> Remove current photo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Material</button>
            </div>
        </form>
    </div>
</div>

{{-- Photo Viewer Modal --}}
<div class="modal-overlay" id="photoModal" onclick="closePhotoModal(event)">
    <div class="modal" style="max-width:550px; background:rgba(255,255,255,0.98); backdrop-filter:blur(10px);">
        <div class="modal-header" style="border:none; padding-bottom:0;">
            <h3 id="photoModalTitle"><i class="fa fa-image"></i> Photo View</h3>
            <button class="modal-close" onclick="closeModal('photoModal')">✕</button>
        </div>
        <div class="modal-body" style="text-align:center; padding:20px;">
            <img id="photoModalImg" src="" alt="Photo" class="photo-modal-img">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

function openAddMaterialModal() {
    document.getElementById('addMaterialForm').reset();
    document.getElementById('add_mat_preview_container').style.display = 'none';
    openModal('addMaterialModal');
}

function previewMatImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const container = preview.parentElement;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            container.style.display = 'flex';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function viewPhoto(url, title) {
    document.getElementById('photoModalImg').src = url;
    document.getElementById('photoModalTitle').innerHTML = '<i class="fa fa-image"></i> ' + (title || 'Material Photo');
    openModal('photoModal');
}

function closePhotoModal(e) {
    if (e.target.id === 'photoModal') {
        closeModal('photoModal');
    }
}

document.getElementById('addMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('materials.store') }}', 'POST');
});

function editMaterial(id, type, name, unit, grade, temp, size, stock, imageUrl) {
    editUrl = `/materials/${id}`;
    document.getElementById('em_type').value = type || 'Raw Material';
    document.getElementById('em_name').value = name || '';
    document.getElementById('em_unit').value = unit || '';
    document.getElementById('em_stock').value = stock || 0;
    document.getElementById('em_grade').value = grade || '';
    document.getElementById('em_temp').value = temp || '';
    document.getElementById('em_size').value = size || '';

    const preview = document.getElementById('edit_mat_preview');
    const container = document.getElementById('edit_mat_preview_container');
    const removeChk = document.getElementById('edit_mat_remove_chk');
    if (removeChk) removeChk.checked = false;

    if (imageUrl) {
        preview.src = imageUrl;
        preview.style.display = 'block';
        container.style.display = 'flex';
    } else {
        preview.src = '';
        preview.style.display = 'none';
        container.style.display = 'none';
    }

    openModal('editMaterialModal');
}

document.getElementById('editMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
