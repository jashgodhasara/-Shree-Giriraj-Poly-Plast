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
    <div class="card-header">
        <h3><i class="fa fa-tag"></i> Product List</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddProductModal()">
            <i class="fa fa-plus"></i> Add Product
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($products->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-tag"></i></div>
            <p>No products added yet.</p>
            <small class="text-muted">Click "+ Add Product" to add your first product with photo.</small>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>HSN Code</th>
                        <th>Price</th>
                        <th>GST Rate</th>
                        <th>Stock Qty</th>
                        <th>Description</th>
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
                    <td class="fw-bold">{{ $p->name }}</td>
                    <td><code>{{ $p->hsn_code ?? '—' }}</code></td>
                    <td>₹{{ number_format($p->price, 2) }}</td>
                    <td><span class="badge badge-blue">{{ $p->gst_rate }}%</span></td>
                    <td>
                        @if($p->stock_quantity <= 5)
                            <span class="badge badge-red">{{ number_format($p->stock_quantity, 2) }} Pcs</span>
                        @else
                            <span class="badge badge-green">{{ number_format($p->stock_quantity, 2) }} Pcs</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($p->description, 40) ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon" title="Edit Product"
                                onclick="editProduct({{ $p->id }}, {{ json_encode($p->name) }}, {{ json_encode($p->description) }}, {{ $p->price }}, {{ json_encode($p->hsn_code) }}, {{ $p->gst_rate }}, {{ $p->stock_quantity ?? 0 }}, {{ json_encode($p->image ? asset($p->image) : null) }})">
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
    <div class="modal">
        <div class="modal-header">
            <h3><div class="modal-header-icon"><i class="fa fa-tag"></i></div> Add Product</h3>
            <button class="modal-close" onclick="closeModal('addProductModal')">✕</button>
        </div>
        <form id="addProductForm" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Plastic Container 500ml">
                </div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" name="price" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>GST Rate (%) *</label>
                        <input type="number" name="gst_rate" step="0.01" value="18" required>
                    </div>
                    <div class="form-group">
                        <label>Opening Stock Qty</label>
                        <input type="number" name="stock_quantity" step="0.01" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>HSN Code</label>
                    <input type="text" name="hsn_code" placeholder="e.g. 39241090">
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
    <div class="modal">
        <div class="modal-header">
            <h3><div class="modal-header-icon"><i class="fa fa-pen"></i></div> Edit Product</h3>
            <button class="modal-close" onclick="closeModal('editProductModal')">✕</button>
        </div>
        <form id="editProductForm" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" id="ep_name" name="name" required>
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
                        <label>Stock Quantity</label>
                        <input type="number" id="ep_stock" name="stock_quantity" step="0.01">
                    </div>
                </div>
                <div class="form-group">
                    <label>HSN Code</label>
                    <input type="text" id="ep_hsn" name="hsn_code">
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

function editProduct(id, name, desc, price, hsn, gst, stock, image) {
    editUrl = `/products/${id}`;
    document.getElementById('ep_name').value = name || '';
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
