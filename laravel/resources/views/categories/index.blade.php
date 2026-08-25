@extends('layouts.app')
@section('title', 'Product Category Master')
@section('page-title', 'Product Categories')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-layer-group text-primary"></i> Product Categories ({{ $categories->total() }})</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddCategoryModal()">
            <i class="fa fa-plus"></i> Add Category
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($categories->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-layer-group"></i></div>
            <p>No product categories found.</p>
            <button class="btn btn-primary btn-sm" onclick="openAddCategoryModal()">Add First Category</button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Total Products</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($categories as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td class="fw-bold">{{ $c->name }}</td>
                    <td><code>{{ $c->code }}</code></td>
                    <td style="font-size:12.5px; color:var(--text-muted);">{{ $c->description ?: '—' }}</td>
                    <td>
                        <span class="badge badge-purple">{{ $c->products_count }} Products</span>
                    </td>
                    <td>
                        <span class="badge {{ $c->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div class="d-flex gap-2 justify-end">
                            <button class="btn btn-outline btn-sm btn-icon" title="Edit Category" onclick='openEditCategoryModal(@json($c))'>
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon" title="Delete Category" onclick="deleteRecord('{{ route('categories.destroy', $c) }}', 'category', this)">
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
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal-overlay" id="addCategoryModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3><i class="fa fa-plus-circle text-primary"></i> Add Category</h3>
            <button class="modal-close" onclick="closeModal('addCategoryModal')">✕</button>
        </div>
        <form id="addCategoryForm" method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. HDPE Containers & Bottles">
                </div>
                <div class="form-group">
                    <label>Category Code (Auto or Custom)</label>
                    <input type="text" name="code" placeholder="e.g. HDPE-CONT" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2" placeholder="Category details..."></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addCategoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal-overlay" id="editCategoryModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3><i class="fa fa-pen-to-square text-primary"></i> Edit Category</h3>
            <button class="modal-close" onclick="closeModal('editCategoryModal')">✕</button>
        </div>
        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" id="edit_cat_name" required>
                </div>
                <div class="form-group">
                    <label>Category Code *</label>
                    <input type="text" name="code" id="edit_cat_code" required style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_cat_description" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_cat_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCategoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openAddCategoryModal() {
    document.getElementById('addCategoryForm').reset();
    openModal('addCategoryModal');
}

function openEditCategoryModal(cat) {
    const form = document.getElementById('editCategoryForm');
    form.action = `/categories/${cat.id}`;
    document.getElementById('edit_cat_name').value = cat.name || '';
    document.getElementById('edit_cat_code').value = cat.code || '';
    document.getElementById('edit_cat_description').value = cat.description || '';
    document.getElementById('edit_cat_status').value = cat.status || 'active';
    openModal('editCategoryModal');
}
</script>
@endsection
