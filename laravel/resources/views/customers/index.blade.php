@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<style>
.customer-avatar {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    object-fit: cover;
    border: 1.5px solid var(--border);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.customer-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.customer-initial-avatar {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid rgba(99,102,241,0.2);
    box-shadow: 0 2px 6px rgba(99,102,241,0.2);
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
        <h3><i class="fa fa-users"></i> Customer List</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddCustomerModal()">
            <i class="fa fa-plus"></i> Add Customer
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($customers->isEmpty())
        <div class="empty-state"><i class="fa fa-users"></i><p>No customers added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Photo</th><th>Name</th><th>Phone</th><th>Email</th><th>GSTIN</th><th>State</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($customers as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>
                        @if($c->image)
                            <img src="{{ asset($c->image) }}" alt="{{ $c->name }}" class="customer-avatar" onclick="viewPhoto('{{ asset($c->image) }}', '{{ e($c->name) }}')">
                        @else
                            <div class="customer-initial-avatar">{{ strtoupper(substr($c->name, 0, 1)) }}</div>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $c->name }}</td>
                    <td>{{ $c->phone ?? '—' }}</td>
                    <td>{{ $c->email ?? '—' }}</td>
                    <td><code>{{ $c->gstin ?? '—' }}</code></td>
                    <td>{{ $c->state ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editCustomer({{ $c->id }}, {{ json_encode($c->name) }}, {{ json_encode($c->phone) }}, {{ json_encode($c->email) }}, {{ json_encode($c->address) }}, {{ json_encode($c->gstin) }}, {{ json_encode($c->state) }}, {{ json_encode($c->image ? asset($c->image) : null) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('customers.destroy', $c) }}', 'customer')">
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
<div class="modal-overlay" id="addCustomerModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Customer</h3>
            <button class="modal-close" onclick="closeModal('addCustomerModal')">✕</button>
        </div>
        <form id="addCustomerForm" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN <span style="font-size:11px;color:var(--primary);font-weight:500;">(Enter GST to Auto-Fill details)</span></label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="add_gstin" name="gstin" maxlength="15" placeholder="e.g. 24AHUPP7924M1ZG" style="text-transform:uppercase">
                            <button type="button" id="add_gst_btn" class="btn btn-outline btn-sm" onclick="verifyGst('add')" style="white-space:nowrap;padding:0 12px;">
                                <i class="fa-solid fa-bolt" style="color:var(--primary)"></i> Verify &amp; Fill
                            </button>
                        </div>
                        <div id="add_gst_status" style="font-size:12px;margin-top:4px;display:none;"></div>
                    </div>
                    <div class="form-group"><label>Customer / Business Name *</label><input type="text" id="add_name" name="name" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>State</label><input type="text" id="add_state" name="state" placeholder="e.g. Gujarat"></div>
                    <div class="form-group"><label>Billing Address</label><textarea id="add_address" name="address" rows="2"></textarea></div>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Customer Photo / Logo</label>
                    <input type="file" name="image" id="add_cust_img_input" accept="image/*" onchange="previewCustImage(this, 'add_cust_preview')">
                    <div class="image-preview-container" id="add_cust_preview_container" style="display:none;">
                        <img id="add_cust_preview" class="preview-img-box" alt="Preview">
                        <span style="font-size:12px;color:var(--text-muted);">Selected customer photo</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editCustomerModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Customer</h3>
            <button class="modal-close" onclick="closeModal('editCustomerModal')">✕</button>
        </div>
        <form id="editCustomerForm" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN</label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="edit_gstin" name="gstin" maxlength="15" style="text-transform:uppercase">
                            <button type="button" id="edit_gst_btn" class="btn btn-outline btn-sm" onclick="verifyGst('edit')" style="white-space:nowrap;padding:0 12px;">
                                <i class="fa-solid fa-bolt" style="color:var(--primary)"></i> Verify
                            </button>
                        </div>
                        <div id="edit_gst_status" style="font-size:12px;margin-top:4px;display:none;"></div>
                    </div>
                    <div class="form-group"><label>Customer / Business Name *</label><input type="text" id="edit_name" name="name" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Phone</label><input type="text" id="edit_phone" name="phone"></div>
                    <div class="form-group"><label>Email</label><input type="email" id="edit_email" name="email"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>State</label><input type="text" id="edit_state" name="state"></div>
                    <div class="form-group"><label>Billing Address</label><textarea id="edit_address" name="address" rows="2"></textarea></div>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Customer Photo / Logo</label>
                    <input type="file" name="image" id="edit_cust_img_input" accept="image/*" onchange="previewCustImage(this, 'edit_cust_preview')">
                    <div class="image-preview-container" id="edit_cust_preview_container" style="display:none;">
                        <img id="edit_cust_preview" class="preview-img-box" alt="Preview">
                        <label style="font-size:12px;color:#dc2626;cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1" id="edit_cust_remove_chk"> Remove current photo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Customer</button>
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

window.openAddCustomerModal = function() {
    const form = document.getElementById('addCustomerForm');
    if (form) form.reset();
    const previewContainer = document.getElementById('add_cust_preview_container');
    if (previewContainer) previewContainer.style.display = 'none';
    const gstStatus = document.getElementById('add_gst_status');
    if (gstStatus) gstStatus.style.display = 'none';
    openModal('addCustomerModal');
};

window.previewCustImage = function(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;
    const container = preview.parentElement;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (container) container.style.display = 'flex';
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.viewPhoto = function(url, title) {
    const img = document.getElementById('photoModalImg');
    const titleEl = document.getElementById('photoModalTitle');
    if (img) img.src = url;
    if (titleEl) titleEl.innerHTML = '<i class="fa fa-image"></i> ' + (title || 'Customer Photo');
    openModal('photoModal');
};

window.closePhotoModal = function(e) {
    if (e.target.id === 'photoModal') {
        closeModal('photoModal');
    }
};

window.verifyGst = async function(mode) {
    const input = document.getElementById(mode + '_gstin');
    const btn = document.getElementById(mode + '_gst_btn');
    const statusDiv = document.getElementById(mode + '_gst_status');
    if (!input) return;
    const gstin = (input.value || '').trim().toUpperCase();

    if (!gstin || gstin.length < 15) {
        showToast('Please enter a valid 15-character GSTIN', 'error');
        return;
    }

    const oldBtnText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
    }
    if (statusDiv) {
        statusDiv.style.display = 'block';
        statusDiv.innerHTML = '<span style="color:var(--primary)"><i class="fa-solid fa-spinner fa-spin"></i> Verifying with GST portal...</span>';
    }

    try {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : '{{ csrf_token() }}';
        const res = await fetch('{{ route('gstin.verify') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ gstin: gstin })
        });

        const data = await res.json();

        if (data.success && data.valid) {
            if (statusDiv) statusDiv.innerHTML = `<span style="color:#10b981;font-weight:600;"><i class="fa-solid fa-circle-check"></i> ${data.status} • ${data.trade_name || data.legal_name}</span>`;
            
            // Auto-fill fields
            if (data.name && document.getElementById(mode + '_name')) {
                document.getElementById(mode + '_name').value = data.name;
            }
            if (data.state && document.getElementById(mode + '_state')) {
                document.getElementById(mode + '_state').value = data.state;
            }
            if (data.address && document.getElementById(mode + '_address')) {
                document.getElementById(mode + '_address').value = data.address;
            }

            showToast('✅ GSTIN Verified & Form Auto-filled!', 'success');
        } else {
            if (statusDiv) statusDiv.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-circle-xmark"></i> ${data.message || 'Invalid GSTIN'}</span>`;
            showToast(data.message || 'GSTIN verification failed', 'error');
        }
    } catch (err) {
        if (statusDiv) statusDiv.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Error verifying GSTIN</span>`;
        showToast('Error connecting to GST API', 'error');
    } finally {
        if (btn) {
            btn.innerHTML = oldBtnText;
            btn.disabled = false;
        }
    }
};

const addForm = document.getElementById('addCustomerForm');
if (addForm) {
    addForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '{{ route('customers.store') }}', 'POST');
    });
}

window.editCustomer = function(id, name, phone, email, address, gstin, state, imageUrl) {
    editUrl = `/customers/${id}`;
    if (document.getElementById('edit_name')) document.getElementById('edit_name').value = name || '';
    if (document.getElementById('edit_phone')) document.getElementById('edit_phone').value = phone || '';
    if (document.getElementById('edit_email')) document.getElementById('edit_email').value = email || '';
    if (document.getElementById('edit_address')) document.getElementById('edit_address').value = address || '';
    if (document.getElementById('edit_gstin')) document.getElementById('edit_gstin').value = gstin || '';
    if (document.getElementById('edit_state')) document.getElementById('edit_state').value = state || '';
    
    const statusDiv = document.getElementById('edit_gst_status');
    if (statusDiv) statusDiv.style.display = 'none';

    const preview = document.getElementById('edit_cust_preview');
    const container = document.getElementById('edit_cust_preview_container');
    const removeChk = document.getElementById('edit_cust_remove_chk');
    if (removeChk) removeChk.checked = false;

    if (imageUrl && preview && container) {
        preview.src = imageUrl;
        preview.style.display = 'block';
        container.style.display = 'flex';
    } else if (preview && container) {
        preview.src = '';
        preview.style.display = 'none';
        container.style.display = 'none';
    }

    openModal('editCustomerModal');
};

const editForm = document.getElementById('editCustomerForm');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, editUrl, 'PUT');
    });
}
</script>
@endsection
