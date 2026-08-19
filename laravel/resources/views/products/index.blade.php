@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<style>
    .product-thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid var(--border);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .product-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(99,102,241,0.25);
    }
    .product-thumb-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
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
        width: 64px;
        height: 64px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid var(--border);
        display: none;
    }
    .preview-placeholder {
        width: 64px;
        height: 64px;
        border-radius: 8px;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
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
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-tag"></i> Product Master</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddProductModal()">
            <i class="fa fa-plus"></i> Add Product
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($products->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-tag"></i></div>
            <p>No products added yet.</p>
            <small class="text-muted">Click "+ Add Product" to add your first product with Job Work piece weight.</small>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th><i class="fa fa-weight-scale text-primary"></i> Piece Weight</th>
                        <th>Wastage</th>
                        <th>Price</th>
                        <th>GST</th>
                        <th>Stock Qty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($products as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>
                        @if($p->image)
                            <img src="{{ asset($p->image) }}" alt="{{ $p->name }}" class="product-thumb" onclick="viewPhoto('{{ asset($p->image) }}', '{{ e($p->name) }}')">
                        @else
                            <div class="product-thumb-placeholder" title="No Photo Available"><i class="fa fa-image"></i></div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $p->name }}</div>
                        @if($p->job_work_applicable)
                            <span class="badge badge-purple" style="font-size:10px;"><i class="fa fa-gears"></i> Job Work Ready</span>
                        @endif
                    </td>
                    <td><code>{{ $p->sku ?? '—' }}</code></td>
                    <td>
                        <strong style="color:var(--primary);">{{ $p->weight_per_piece > 0 ? $p->weight_per_piece . ' ' . $p->weight_unit : '—' }}</strong>
                    </td>
                    <td>
                        @if($p->wastage_percentage > 0)
                            <span class="badge badge-orange">{{ $p->wastage_percentage }}%</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">0%</span>
                        @endif
                    </td>
                    <td>₹{{ number_format($p->price, 2) }}</td>
                    <td><span class="badge badge-blue">{{ $p->gst_rate }}%</span></td>
                    <td>
                        @if($p->stock_quantity <= 5)
                            <span class="badge badge-red">{{ number_format($p->stock_quantity, 2) }} {{ $p->unit_type ?: 'Pcs' }}</span>
                        @else
                            <span class="badge badge-green">{{ number_format($p->stock_quantity, 2) }} {{ $p->unit_type ?: 'Pcs' }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon" title="Edit Product"
                                onclick="editProduct({{ $p->id }}, {{ json_encode($p->name) }}, {{ json_encode($p->sku) }}, {{ json_encode($p->unit_type) }}, {{ $p->weight_per_piece ?? 0 }}, {{ json_encode($p->weight_unit ?? 'Gram') }}, {{ $p->wastage_percentage ?? 0 }}, {{ $p->job_work_applicable ? 1 : 0 }}, {{ json_encode($p->description) }}, {{ $p->price }}, {{ json_encode($p->hsn_code) }}, {{ $p->gst_rate }}, {{ $p->stock_quantity ?? 0 }}, {{ json_encode($p->image ? asset($p->image) : null) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon" title="Delete Product"
                                onclick="deleteRecord('{{ route('products.destroy', $p) }}', 'product')">
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
<div class="modal-overlay" id="addProductModal">
    <div class="modal" style="max-width: 650px;">
        <div class="modal-header">
            <h3><div class="modal-header-icon"><i class="fa fa-tag"></i></div> Add Product</h3>
            <button class="modal-close" onclick="closeModal('addProductModal')">✕</button>
        </div>
        <form id="addProductForm" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Plastic Tub 500ml">
                    </div>
                    <div class="form-group">
                        <label>SKU / Product Code</label>
                        <input type="text" name="sku" placeholder="e.g. TUB-500ML-A">
                    </div>
                </div>

                {{-- Job Work Weight & Piece Calculation Settings --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-weight: 700; font-size: 13px; color: var(--primary); margin-bottom: 10px; display:flex; align-items:center; justify-content:space-between;">
                        <span><i class="fa fa-scale-balanced"></i> Job Work Automatic Weight &amp; Wastage Master</span>
                        <label style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                            <input type="checkbox" name="job_work_applicable" value="1" checked> Job Work Applicable
                        </label>
                    </div>
                    <div class="form-row cols-3">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Weight Per Piece *</label>
                            <input type="number" name="weight_per_piece" step="0.0001" min="0" value="10" placeholder="e.g. 10">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Weight Unit *</label>
                            <select name="weight_unit">
                                <option value="Gram" selected>Gram (g)</option>
                                <option value="KG">Kilogram (KG)</option>
                                <option value="Milligram">Milligram (mg)</option>
                                <option value="Ton">Ton (T)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Wastage / Salwaton (%)</label>
                            <input type="number" name="wastage_percentage" step="0.01" min="0" max="100" value="2" placeholder="e.g. 2%">
                        </div>
                    </div>
                    <div style="font-size:11px; color:#64748b; margin-top:6px;">
                        <i class="fa fa-circle-info"></i> Used to calculate produced pieces from received raw material (e.g. 500 KG ÷ 10g = 50,000 PCS).
                    </div>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" name="price" step="0.01" min="0" required placeholder="0.00" value="0.00">
                    </div>
                    <div class="form-group">
                        <label>GST Rate (%) *</label>
                        <input type="number" name="gst_rate" step="0.01" value="18" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Type</label>
                        <select name="unit_type">
                            <option value="PCS" selected>PCS (Pieces)</option>
                            <option value="KG">KG</option>
                            <option value="BOX">BOX</option>
                            <option value="BAG">BAG</option>
                            <option value="SET">SET</option>
                        </select>
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Opening Stock Qty</label>
                        <input type="number" name="stock_quantity" step="0.01" value="0">
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" name="hsn_code" placeholder="e.g. 39241090">
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Photo</label>
                    <input type="file" name="image" id="add_image_input" accept="image/*" onchange="previewImage(this, 'add_preview_img', 'add_preview_placeholder')">
                    <div class="image-preview-container mt-1">
                        <img id="add_preview_img" src="#" alt="Preview" class="preview-img-box">
                        <div id="add_preview_placeholder" class="preview-placeholder"><i class="fa fa-cloud-arrow-up"></i></div>
                        <div>
                            <div class="fw-600" style="font-size:12px;">Upload Photo (Optional)</div>
                            <div class="form-hint">Accepted formats: JPG, PNG, WEBP, GIF. Max 4MB.</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Additional product notes or specifications..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editProductModal">
    <div class="modal" style="max-width: 650px;">
        <div class="modal-header">
            <h3><div class="modal-header-icon"><i class="fa fa-pen"></i></div> Edit Product</h3>
            <button class="modal-close" onclick="closeModal('editProductModal')">✕</button>
        </div>
        <form id="editProductForm" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" id="ep_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>SKU / Product Code</label>
                        <input type="text" id="ep_sku" name="sku">
                    </div>
                </div>

                {{-- Job Work Settings --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-weight: 700; font-size: 13px; color: var(--primary); margin-bottom: 10px; display:flex; align-items:center; justify-content:space-between;">
                        <span><i class="fa fa-scale-balanced"></i> Job Work Automatic Weight &amp; Wastage Master</span>
                        <label style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                            <input type="checkbox" id="ep_jw_app" name="job_work_applicable" value="1"> Job Work Applicable
                        </label>
                    </div>
                    <div class="form-row cols-3">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Weight Per Piece *</label>
                            <input type="number" id="ep_weight" name="weight_per_piece" step="0.0001" min="0">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Weight Unit *</label>
                            <select id="ep_weight_unit" name="weight_unit">
                                <option value="Gram">Gram (g)</option>
                                <option value="KG">Kilogram (KG)</option>
                                <option value="Milligram">Milligram (mg)</option>
                                <option value="Ton">Ton (T)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Wastage / Salwaton (%)</label>
                            <input type="number" id="ep_wastage" name="wastage_percentage" step="0.01" min="0" max="100">
                        </div>
                    </div>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" id="ep_price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>GST Rate (%) *</label>
                        <input type="number" id="ep_gst" name="gst_rate" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Type</label>
                        <select id="ep_unit_type" name="unit_type">
                            <option value="PCS">PCS (Pieces)</option>
                            <option value="KG">KG</option>
                            <option value="BOX">BOX</option>
                            <option value="BAG">BAG</option>
                            <option value="SET">SET</option>
                        </select>
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" id="ep_stock" name="stock_quantity" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" id="ep_hsn" name="hsn_code">
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Photo</label>
                    <input type="file" name="image" id="ep_image_input" accept="image/*" onchange="previewImage(this, 'ep_preview_img', 'ep_preview_placeholder')">
                    <div class="image-preview-container mt-1">
                        <img id="ep_preview_img" src="#" alt="Preview" class="preview-img-box">
                        <div id="ep_preview_placeholder" class="preview-placeholder"><i class="fa fa-image"></i></div>
                        <div>
                            <div class="fw-600" style="font-size:12px;">Change Photo</div>
                            <div class="form-hint">Upload a new file to replace existing photo.</div>
                            <div id="ep_remove_wrapper" style="display:none; margin-top:4px;">
                                <label style="display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--danger); cursor:pointer;">
                                    <input type="checkbox" name="remove_image" value="1" id="ep_remove_checkbox"> Remove existing photo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="ep_desc" name="description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Photo View Lightbox Modal -->
<div class="modal-overlay" id="photoModal">
    <div class="modal" style="max-width: 600px; text-align: center;">
        <div class="modal-header">
            <h3 id="photoModalTitle"><i class="fa fa-image"></i> Product Photo</h3>
            <button class="modal-close" onclick="closeModal('photoModal')">✕</button>
        </div>
        <div class="modal-body" style="padding:20px;">
            <img id="photoModalImg" src="" alt="Product Photo" class="photo-modal-img">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

function openAddProductModal() {
    document.getElementById('addProductForm').reset();
    document.getElementById('add_preview_img').style.display = 'none';
    document.getElementById('add_preview_placeholder').style.display = 'flex';
    openModal('addProductModal');
}

function previewImage(input, imgId, placeholderId) {
    const img = document.getElementById(imgId);
    const placeholder = document.getElementById(placeholderId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            img.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('products.store') }}', 'POST');
});

function editProduct(id, name, sku, unit_type, weight, weight_unit, wastage, jw_app, desc, price, hsn, gst, stock, image) {
    editUrl = `/products/${id}`;
    document.getElementById('ep_name').value = name || '';
    document.getElementById('ep_sku').value = sku || '';
    document.getElementById('ep_unit_type').value = unit_type || 'PCS';
    document.getElementById('ep_weight').value = weight || '';
    document.getElementById('ep_weight_unit').value = weight_unit || 'Gram';
    document.getElementById('ep_wastage').value = wastage || 0;
    document.getElementById('ep_jw_app').checked = Boolean(jw_app);
    document.getElementById('ep_price').value = price || '';
    document.getElementById('ep_gst').value = gst || 18;
    document.getElementById('ep_stock').value = stock || 0;
    document.getElementById('ep_hsn').value = hsn || '';
    document.getElementById('ep_desc').value = desc || '';
    document.getElementById('ep_image_input').value = '';
    
    const imgPreview = document.getElementById('ep_preview_img');
    const placeholder = document.getElementById('ep_preview_placeholder');
    const removeWrapper = document.getElementById('ep_remove_wrapper');
    const removeCheckbox = document.getElementById('ep_remove_checkbox');
    
    if (removeCheckbox) removeCheckbox.checked = false;

    if (image) {
        imgPreview.src = image;
        imgPreview.style.display = 'block';
        placeholder.style.display = 'none';
        removeWrapper.style.display = 'block';
    } else {
        imgPreview.style.display = 'none';
        placeholder.style.display = 'flex';
        removeWrapper.style.display = 'none';
    }

    openModal('editProductModal');
}

document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});

function viewPhoto(url, title) {
    document.getElementById('photoModalImg').src = url;
    document.getElementById('photoModalTitle').innerHTML = `<i class="fa fa-image"></i> ${title}`;
    openModal('photoModal');
}
</script>
@endsection
