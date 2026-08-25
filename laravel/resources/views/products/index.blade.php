@extends('layouts.app')
@section('title', 'Product Master & Inventory')
@section('page-title', 'Product Master')

@section('content')
<style>
    .product-thumb {
        width: 44px;
        height: 44px;
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
    .badge-instock { background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); }
    .badge-lowstock { background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25); }
    .badge-critical { background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); }
    .badge-outofstock { background: rgba(100, 116, 139, 0.12); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.25); }

    .filter-bar {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }
    .filter-inputs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        flex: 1;
    }
    .filter-inputs input, .filter-inputs select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 13px;
        background: #fafbff;
    }
    .filter-inputs input:focus, .filter-inputs select:focus {
        border-color: var(--primary);
        outline: none;
        background: #fff;
    }
    .preview-box-container {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 6px;
        padding: 8px;
        border: 1px dashed var(--border);
        border-radius: 6px;
        background: #fafafa;
    }
</style>

<!-- Inventory Quick Metrics -->
<div class="stats-grid">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-boxes-stacked"></i></div>
            <span class="badge badge-purple">Total Products</span>
        </div>
        <div class="stat-label">Product Catalog</div>
        <div class="stat-value">{{ number_format($totalProductsCount) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
            <span class="badge badge-success">Valuation</span>
        </div>
        <div class="stat-label">Total Inventory Value</div>
        <div class="stat-value">₹{{ number_format($totalStockValue, 2) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-triangle-exclamation"></i></div>
            <span class="badge badge-warning">Reorder Needed</span>
        </div>
        <div class="stat-label">Low Stock Products</div>
        <div class="stat-value">{{ number_format($lowStockCount) }}</div>
    </div>
    <div class="stat-card s-red">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa fa-circle-xmark"></i></div>
            <span class="badge badge-danger">Out of Stock</span>
        </div>
        <div class="stat-label">Zero / Depleted Stock</div>
        <div class="stat-value">{{ number_format($outOfStockCount) }}</div>
    </div>
</div>

<!-- Search & Filter Controls -->
<div class="filter-bar">
    <form method="GET" action="{{ route('products.index') }}" class="filter-inputs">
        <div style="position:relative; min-width:220px;">
            <i class="fa fa-search" style="position:absolute; left:12px; top:11px; color:#94a3b8; font-size:12px;"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, code, barcode..." style="padding-left:32px; width:100%;">
        </div>

        <select name="category_id" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>

        <select name="stock_status" onchange="this.form.submit()">
            <option value="">All Stock Status</option>
            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
            <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock / Reorder</option>
            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </select>

        <select name="sort_by" onchange="this.form.submit()">
            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Sort: Newest First</option>
            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Sort: Product Name</option>
            <option value="stock_quantity" {{ request('sort_by') == 'stock_quantity' ? 'selected' : '' }}>Sort: Stock Qty</option>
            <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Sort: Selling Price</option>
            <option value="average_cost" {{ request('sort_by') == 'average_cost' ? 'selected' : '' }}>Sort: Valuation Cost</option>
        </select>

        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply</button>
        @if(request()->hasAny(['search', 'category_id', 'stock_status', 'sort_by']))
            <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm" title="Clear Filters"><i class="fa fa-rotate-left"></i> Reset</a>
        @endif
    </form>

    <div class="d-flex gap-2">
        <a href="{{ route('products.export') }}" class="btn btn-outline btn-sm" title="Export Product Catalog to CSV">
            <i class="fa fa-file-csv"></i> Export CSV
        </a>
        <button class="btn btn-outline btn-sm" onclick="openModal('importProductModal')" title="Bulk CSV Import">
            <i class="fa fa-file-import"></i> Import
        </button>
        <button class="btn btn-primary btn-sm" onclick="openAddProductModal()">
            <i class="fa fa-plus"></i> Add Product
        </button>
    </div>
</div>

<!-- Products Data Table -->
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-tag"></i> Products List ({{ $products->total() }})</h3>
        <span style="font-size:12px; color:var(--text-muted);">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
    </div>
    <div class="card-body" style="padding:0">
        @if($products->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-box-open"></i></div>
            <p>No products found matching the criteria.</p>
            <small class="text-muted">Try resetting filters or click "+ Add Product" to create a new product.</small>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Product &amp; Code</th>
                        <th>SKU / Barcode</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Stock Qty</th>
                        <th>Avg Cost</th>
                        <th>Selling Price</th>
                        <th>Inventory Value</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($products as $p)
                @php
                    $status = $p->stock_status;
                    $badgeClass = match($status) {
                        'In Stock'     => 'badge-instock',
                        'Low Stock'    => 'badge-lowstock',
                        'Critical'     => 'badge-critical',
                        'Out of Stock' => 'badge-outofstock',
                        default        => 'badge-instock',
                    };
                @endphp
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
                        <a href="{{ route('products.show', $p) }}" class="fw-bold text-primary" style="text-decoration:none;" title="View stock ledger & history">
                            {{ $p->name }}
                        </a>
                        @if($p->product_code)
                            <div style="font-size:11px; color:var(--text-muted);">Code: {{ $p->product_code }}</div>
                        @endif
                        @if($p->job_work_applicable)
                            <span class="badge badge-purple" style="font-size:9.5px; padding:1px 5px;"><i class="fa fa-gears"></i> Job Work</span>
                        @endif
                    </td>
                    <td>
                        <code>{{ $p->sku }}</code>
                        @if($p->barcode)
                            <div style="font-size:10px; color:var(--text-muted);"><i class="fa fa-barcode"></i> {{ $p->barcode }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-indigo">{{ $p->category->name ?? '—' }}</span>
                    </td>
                    <td>{{ $p->unit ?: 'PCS' }}</td>
                    <td>
                        <div class="fw-bold">{{ number_format($p->stock_quantity, 2) }}</div>
                        <span class="badge {{ $badgeClass }}" style="font-size:10px;">{{ $status }}</span>
                    </td>
                    <td>₹{{ number_format($p->average_cost > 0 ? $p->average_cost : $p->purchase_rate, 2) }}</td>
                    <td class="fw-bold">₹{{ number_format($p->price, 2) }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($p->inventory_value, 2) }}</td>
                    <td style="text-align:right;">
                        <div class="d-flex gap-2 justify-end">
                            <a href="{{ route('products.show', $p) }}" class="btn btn-outline btn-sm btn-icon" title="View Stock Ledger & History" style="color:var(--primary); background:rgba(99,102,241,0.06);">
                                <i class="fa fa-eye"></i>
                            </a>
                            <button class="btn btn-outline btn-sm btn-icon" title="Edit Product" onclick='openEditProductModal(@json($p))'>
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon" title="Delete Product" onclick="deleteRecord('{{ route('products.destroy', $p) }}', 'product', this)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px;">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addProductModal">
    <div class="modal" style="max-width:850px;">
        <div class="modal-header">
            <h3><i class="fa fa-plus-circle text-primary"></i> Add New Product Master</h3>
            <button class="modal-close" onclick="closeModal('addProductModal')">✕</button>
        </div>
        <form id="addProductForm" enctype="multipart/form-data">
            <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                <!-- General Info -->
                <div class="sidebar-section" style="padding-left:0; margin-bottom:8px;">Basic Information</div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="add_name" required placeholder="e.g. 500ml HDPE Bottle" onblur="checkDuplicateProduct('add')">
                        <div id="add_dup_alert" style="font-size:11px; color:#ef4444; display:none; margin-top:2px;"></div>
                    </div>
                    <div class="form-group">
                        <label>SKU (Auto or Custom)</label>
                        <input type="text" name="sku" id="add_sku" placeholder="Auto-generated if empty" style="text-transform:uppercase;" onblur="checkDuplicateProduct('add')">
                    </div>
                    <div class="form-group">
                        <label>Product Code</label>
                        <input type="text" name="product_code" id="add_product_code" placeholder="e.g. BTL-500-01">
                    </div>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Product Category</label>
                        <select name="category_id" id="add_category_id">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub-category</label>
                        <input type="text" name="subcategory" placeholder="e.g. Narrow Mouth">
                    </div>
                    <div class="form-group">
                        <label>Product Type</label>
                        <select name="product_type">
                            <option value="Finished Goods">Finished Goods</option>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Semi-Finished">Semi-Finished</option>
                            <option value="Trading">Trading Item</option>
                        </select>
                    </div>
                </div>

                <!-- Units & Conversions -->
                <div class="sidebar-section" style="padding-left:0; margin-top:12px; margin-bottom:8px;">Units &amp; Packaging</div>
                <div class="form-row cols-4">
                    <div class="form-group">
                        <label>Base Unit *</label>
                        <select name="unit" id="add_unit" required>
                            @foreach($units as $u)
                                <option value="{{ $u->code }}" {{ $u->code == 'PCS' ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Purchase Unit</label>
                        <select name="purchase_unit">
                            <option value="">Same as Base Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->code }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sales Unit</label>
                        <select name="sales_unit">
                            <option value="">Same as Base Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->code }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Conversion Factor</label>
                        <input type="number" step="0.0001" name="conversion_factor" value="1.0000" placeholder="1.0">
                    </div>
                </div>

                <!-- Pricing & GST -->
                <div class="sidebar-section" style="padding-left:0; margin-top:12px; margin-bottom:8px;">Pricing &amp; Taxation</div>
                <div class="form-row cols-4">
                    <div class="form-group">
                        <label>Sales Rate (Price) *</label>
                        <input type="number" step="0.01" name="price" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Purchase Rate</label>
                        <input type="number" step="0.01" name="purchase_rate" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Wholesale Rate</label>
                        <input type="number" step="0.01" name="wholesale_rate" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>MRP</label>
                        <input type="number" step="0.01" name="mrp" placeholder="0.00">
                    </div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>GST Rate (%) *</label>
                        <select name="gst_rate" required>
                            <option value="18">18% (Standard Polymer)</option>
                            <option value="12">12%</option>
                            <option value="5">5%</option>
                            <option value="28">28%</option>
                            <option value="0">0% (Exempt)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" name="hsn_code" value="3923" placeholder="e.g. 3923">
                    </div>
                    <div class="form-group">
                        <label>Barcode</label>
                        <input type="text" name="barcode" id="add_barcode" placeholder="Scan or enter barcode" onblur="checkDuplicateProduct('add')">
                    </div>
                </div>

                <!-- Stock Levels & Inventory Tracking -->
                <div class="sidebar-section" style="padding-left:0; margin-top:12px; margin-bottom:8px;">Stock &amp; Inventory Settings</div>
                <div class="form-row cols-4">
                    <div class="form-group">
                        <label>Opening Stock</label>
                        <input type="number" step="0.01" name="opening_stock" value="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Reorder Level</label>
                        <input type="number" step="0.01" name="reorder_level" placeholder="Alert threshold">
                    </div>
                    <div class="form-group">
                        <label>Minimum Stock</label>
                        <input type="number" step="0.01" name="minimum_stock" placeholder="Safety stock">
                    </div>
                    <div class="form-group">
                        <label>Primary Warehouse</label>
                        <select name="warehouse_id">
                            <option value="">-- Main Warehouse --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Job Work & Technical Attributes -->
                <div class="sidebar-section" style="padding-left:0; margin-top:12px; margin-bottom:8px;">Job Work &amp; Technical Specs</div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Weight Per Piece</label>
                        <input type="number" step="0.0001" name="weight_per_piece" placeholder="e.g. 24.5">
                    </div>
                    <div class="form-group">
                        <label>Weight Unit</label>
                        <select name="weight_unit">
                            <option value="Gram">Gram (g)</option>
                            <option value="KG">Kilogram (kg)</option>
                            <option value="Milligram">Milligram (mg)</option>
                            <option value="Ton">Ton (t)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Wastage Allowance (%)</label>
                        <input type="number" step="0.01" name="wastage_percentage" value="2.00" placeholder="e.g. 2%">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description / Technical Notes</label>
                    <textarea name="description" rows="2" placeholder="Specifications, mold details, raw material grades..."></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Product Image</label>
                    <input type="file" name="image" accept="image/*" onchange="previewProductImg(this, 'add_prod_preview')">
                    <div class="preview-box-container" id="add_prod_preview_box" style="display:none;">
                        <img id="add_prod_preview" class="preview-img-box" style="display:block;" alt="Preview">
                        <span style="font-size:12px; color:var(--text-muted);">Selected Product Image</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Product Master</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="editProductModal">
    <div class="modal" style="max-width:850px;">
        <div class="modal-header">
            <h3><i class="fa fa-pen-to-square text-primary"></i> Edit Product Master</h3>
            <button class="modal-close" onclick="closeModal('editProductModal')">✕</button>
        </div>
        <form id="editProductForm" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="edit_product_id">
            <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                <div class="sidebar-section" style="padding-left:0; margin-bottom:8px;">Basic Information</div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" id="edit_sku" required style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label>Product Code</label>
                        <input type="text" name="product_code" id="edit_product_code">
                    </div>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Product Category</label>
                        <select name="category_id" id="edit_category_id">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub-category</label>
                        <input type="text" name="subcategory" id="edit_subcategory">
                    </div>
                    <div class="form-group">
                        <label>Product Type</label>
                        <select name="product_type" id="edit_product_type">
                            <option value="Finished Goods">Finished Goods</option>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Semi-Finished">Semi-Finished</option>
                            <option value="Trading">Trading Item</option>
                        </select>
                    </div>
                </div>

                <div class="sidebar-section" style="padding-left:0; margin-top:12px; margin-bottom:8px;">Units &amp; Pricing</div>
                <div class="form-row cols-4">
                    <div class="form-group">
                        <label>Base Unit *</label>
                        <select name="unit" id="edit_unit" required>
                            @foreach($units as $u)
                                <option value="{{ $u->code }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sales Rate (Price) *</label>
                        <input type="number" step="0.01" name="price" id="edit_price" required>
                    </div>
                    <div class="form-group">
                        <label>Purchase Rate</label>
                        <input type="number" step="0.01" name="purchase_rate" id="edit_purchase_rate">
                    </div>
                    <div class="form-group">
                        <label>GST Rate (%) *</label>
                        <select name="gst_rate" id="edit_gst_rate" required>
                            <option value="18">18% (Standard)</option>
                            <option value="12">12%</option>
                            <option value="5">5%</option>
                            <option value="28">28%</option>
                            <option value="0">0%</option>
                        </select>
                    </div>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" name="hsn_code" id="edit_hsn_code">
                    </div>
                    <div class="form-group">
                        <label>Barcode</label>
                        <input type="text" name="barcode" id="edit_barcode">
                    </div>
                    <div class="form-group">
                        <label>Reorder Level</label>
                        <input type="number" step="0.01" name="reorder_level" id="edit_reorder_level">
                    </div>
                </div>

                <div class="sidebar-section" style="padding-left:0; margin-top:12px; margin-bottom:8px;">Job Work Weight Specs</div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Weight Per Piece</label>
                        <input type="number" step="0.0001" name="weight_per_piece" id="edit_weight_per_piece">
                    </div>
                    <div class="form-group">
                        <label>Weight Unit</label>
                        <select name="weight_unit" id="edit_weight_unit">
                            <option value="Gram">Gram (g)</option>
                            <option value="KG">Kilogram (kg)</option>
                            <option value="Milligram">Milligram (mg)</option>
                            <option value="Ton">Ton (t)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Wastage Allowance (%)</label>
                        <input type="number" step="0.01" name="wastage_percentage" id="edit_wastage_percentage">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Product Image</label>
                    <input type="file" name="image" accept="image/*" onchange="previewProductImg(this, 'edit_prod_preview')">
                    <div class="preview-box-container" id="edit_prod_preview_box" style="display:none;">
                        <img id="edit_prod_preview" class="preview-img-box" style="display:block;" alt="Preview">
                        <label style="font-size:12px; color:#dc2626; cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1"> Remove current image
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal-overlay" id="importProductModal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fa fa-file-import text-primary"></i> Bulk Import Products (CSV)</h3>
            <button class="modal-close" onclick="closeModal('importProductModal')">✕</button>
        </div>
        <form id="importProductForm" enctype="multipart/form-data">
            <div class="modal-body">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
                    Upload a CSV file containing your product catalog. Supported headers: <code>Product Name, SKU, Category, Unit, Sales Rate, Purchase Rate, Opening Stock, GST %, HSN Code, Reorder Level</code>.
                </p>
                <div class="form-group">
                    <label>Select CSV File</label>
                    <input type="file" name="file" accept=".csv,text/csv" required>
                </div>
                <div id="import_results" style="display:none; margin-top:12px; font-size:12px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('importProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload &amp; Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Photo Viewer Modal -->
<div class="modal-overlay" id="photoModal">
    <div class="modal" style="max-width:500px; text-align:center;">
        <div class="modal-header">
            <h3 id="photoModalTitle"><i class="fa fa-image"></i> Product Image</h3>
            <button class="modal-close" onclick="closeModal('photoModal')">✕</button>
        </div>
        <div class="modal-body" style="padding:16px;">
            <img id="photoModalImg" class="photo-modal-img" src="" alt="Product">
        </div>
    </div>
</div>

<script>
window.editProductUrl = '';

window.openAddProductModal = function() {
    document.getElementById('addProductForm').reset();
    document.getElementById('add_prod_preview_box').style.display = 'none';
    document.getElementById('add_dup_alert').style.display = 'none';
    openModal('addProductModal');
};

window.openEditProductModal = function(p) {
    window.editProductUrl = `/products/${p.id}`;
    document.getElementById('edit_product_id').value = p.id;
    document.getElementById('edit_name').value = p.name || '';
    document.getElementById('edit_sku').value = p.sku || '';
    document.getElementById('edit_product_code').value = p.product_code || '';
    document.getElementById('edit_category_id').value = p.category_id || '';
    document.getElementById('edit_subcategory').value = p.subcategory || '';
    document.getElementById('edit_product_type').value = p.product_type || 'Finished Goods';
    document.getElementById('edit_unit').value = p.unit || 'PCS';
    document.getElementById('edit_price').value = p.price || '';
    document.getElementById('edit_purchase_rate').value = p.purchase_rate || '';
    document.getElementById('edit_gst_rate').value = Math.round(p.gst_rate) || 18;
    document.getElementById('edit_hsn_code').value = p.hsn_code || '';
    document.getElementById('edit_barcode').value = p.barcode || '';
    document.getElementById('edit_reorder_level').value = p.reorder_level || '';
    document.getElementById('edit_weight_per_piece').value = p.weight_per_piece || '';
    document.getElementById('edit_weight_unit').value = p.weight_unit || 'Gram';
    document.getElementById('edit_wastage_percentage').value = p.wastage_percentage || '2.00';
    document.getElementById('edit_description').value = p.description || '';

    if (p.image) {
        document.getElementById('edit_prod_preview').src = `/${p.image}`;
        document.getElementById('edit_prod_preview_box').style.display = 'flex';
    } else {
        document.getElementById('edit_prod_preview_box').style.display = 'none';
    }

    openModal('editProductModal');
};

window.previewProductImg = function(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.parentElement.style.display = 'flex';
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.viewPhoto = function(url, title) {
    document.getElementById('photoModalImg').src = url;
    document.getElementById('photoModalTitle').innerText = title;
    openModal('photoModal');
};

window.checkDuplicateProduct = async function(mode) {
    const sku = document.getElementById(mode + '_sku') ? document.getElementById(mode + '_sku').value.trim() : '';
    const barcode = document.getElementById(mode + '_barcode') ? document.getElementById(mode + '_barcode').value.trim() : '';
    const name = document.getElementById(mode + '_name') ? document.getElementById(mode + '_name').value.trim() : '';
    const excludeId = mode === 'edit' ? document.getElementById('edit_product_id').value : '';

    if (!sku && !barcode && !name) return;

    try {
        const res = await fetch(`/products/check-duplicate?sku=${encodeURIComponent(sku)}&barcode=${encodeURIComponent(barcode)}&name=${encodeURIComponent(name)}&exclude_id=${excludeId}`);
        const data = await res.json();
        const alertBox = document.getElementById(mode + '_dup_alert');
        if (alertBox) {
            if (data.duplicate) {
                alertBox.style.display = 'block';
                alertBox.innerHTML = `<i class="fa fa-warning"></i> Warning: Product "${data.product.name}" with SKU "${data.product.sku}" already exists.`;
            } else {
                alertBox.style.display = 'none';
            }
        }
    } catch (e) {}
};

// Add Product Submit
document.getElementById('addProductForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('products.store') }}', 'POST');
});

// Edit Product Submit
document.getElementById('editProductForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, window.editProductUrl, 'POST');
});

// CSV Import Submit
document.getElementById('importProductForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const resultBox = document.getElementById('import_results');
    resultBox.style.display = 'block';
    resultBox.innerHTML = '<span style="color:var(--primary)"><i class="fa fa-spinner fa-spin"></i> Processing import...</span>';

    try {
        const res = await fetch('{{ route('products.import') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            resultBox.innerHTML = `<span style="color:#10b981"><i class="fa fa-check"></i> ${data.message}</span>`;
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            resultBox.innerHTML = `<span style="color:#ef4444"><i class="fa fa-warning"></i> ${data.message || 'Import failed'}</span>`;
            showToast(data.message || 'Import failed', 'error');
        }
    } catch (err) {
        resultBox.innerHTML = '<span style="color:#ef4444">Error uploading file.</span>';
        showToast('Error uploading file', 'error');
    }
});
</script>
@endsection
