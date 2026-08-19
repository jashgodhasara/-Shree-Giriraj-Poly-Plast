@extends('layouts.app')

@section('title', 'Multi-Branch & Inter-Branch Stock Transfers - Shree Giriraj Poly Plast')
@section('page-title', 'Multi-Location Management')

@section('content')
<div class="d-flex justify-between align-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;">Multi-Branch &amp; Stock Redistribution Engine</h2>
        <p class="text-muted" style="font-size: 13px;">Manage branch locations, assigned managers, stock redistribution &amp; inter-branch transfers</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('addBranchModal')">
        <i class="fa fa-plus"></i> Add New Branch / Depot
    </button>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-between align-center flex-wrap gap-2">
        <h3><i class="fa fa-diagram-project"></i> Active Locations &amp; Branch Managers</h3>
        <span class="text-muted" style="font-size: 12px;">Active Working Branch: <strong class="text-primary">{{ session('current_branch', 'Main Plant & HQ') }}</strong></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Branch ID</th>
                    <th>Branch Name</th>
                    <th>Location / City</th>
                    <th>Branch Type</th>
                    <th><i class="fa fa-user-tie text-primary"></i> Managed By (Branch Manager)</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $b)
                <tr>
                    <td>#{{ $b->id }}</td>
                    <td>
                        <strong>{{ $b->name }}</strong>
                        @if($b->is_main)
                            <span class="badge badge-green" style="font-size: 10px; margin-left: 4px;"><i class="fa fa-star"></i> HQ</span>
                        @endif
                    </td>
                    <td><i class="fa fa-location-dot text-primary"></i> {{ $b->city ?? 'N/A' }}</td>
                    <td><span class="badge badge-gray">{{ $b->type ?? 'Depot' }}</span></td>
                    <td>
                        @if($b->manager_name)
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <div style="font-weight: 600; color: var(--text-color, #1e293b); display: flex; align-items: center; gap: 5px;">
                                    <i class="fa fa-user-circle text-primary" style="font-size: 14px;"></i>
                                    {{ $b->manager_name }}
                                </div>
                                @if($b->manager_phone)
                                    <div style="font-size: 12px; color: var(--text-muted, #64748b);">
                                        <i class="fa fa-phone" style="font-size: 11px; margin-right: 3px;"></i>
                                        <a href="tel:{{ $b->manager_phone }}" style="color: inherit; text-decoration: none;">{{ $b->manager_phone }}</a>
                                    </div>
                                @endif
                                @if($b->manager_email)
                                    <div style="font-size: 11px; color: var(--text-muted, #64748b);">
                                        <i class="fa fa-envelope" style="font-size: 10px; margin-right: 3px;"></i>
                                        <a href="mailto:{{ $b->manager_email }}" style="color: inherit; text-decoration: none;">{{ $b->manager_email }}</a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted" style="font-size: 12px; font-style: italic;">
                                <i class="fa fa-user-slash"></i> Not assigned
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($b->is_main)
                            <span class="badge badge-green"><i class="fa fa-star"></i> Main Plant</span>
                        @else
                            <span class="badge badge-blue"><i class="fa fa-building"></i> Active</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div class="d-flex gap-2 justify-end align-center flex-wrap">
                            @if(session('current_branch') === $b->name || (session('current_branch') === 'Ahmedabad, Gujarat' && $b->is_main))
                                <span class="badge badge-green" style="padding: 6px 10px;"><i class="fa fa-check"></i> Current Active</span>
                            @else
                                <button onclick="switchBranch('{{ $b->name }}')" class="btn btn-outline btn-sm" title="Switch active working branch">
                                    <i class="fa fa-right-to-bracket"></i> Switch
                                </button>
                            @endif

                            <button class="btn btn-outline btn-sm btn-icon" 
                                title="Edit Branch & Manager Details" 
                                onclick="editBranch({{ $b->id }}, {{ json_encode($b->name) }}, {{ json_encode($b->city) }}, {{ json_encode($b->type) }}, {{ json_encode($b->manager_name) }}, {{ json_encode($b->manager_phone) }}, {{ json_encode($b->manager_email) }})">
                                <i class="fa fa-pen"></i>
                            </button>

                            @if(!$b->is_main)
                                <button class="btn btn-danger btn-sm btn-icon" 
                                    title="Delete Branch" 
                                    onclick="deleteRecord('{{ route('branches.destroy', $b) }}', 'branch &quot;{{ addslashes($b->name) }}&quot;')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            @else
                                <span class="badge badge-gray" style="opacity: 0.7; font-size: 11px;" title="Primary headquarters cannot be deleted">
                                    <i class="fa fa-shield"></i> Primary
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 24px;">No branches found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-robot"></i> AI Inter-Branch Stock Redistribution Suggestions</h3>
        <span class="badge badge-purple"><i class="fa fa-wand-magic-sparkles"></i> AI Auto-Optimization</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Source Location</th>
                        <th>Destination Location</th>
                        <th>Material / SKU</th>
                        <th>Suggested Qty</th>
                        <th>AI Recommendation Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockTransfers as $st)
                    <tr>
                        <td><i class="fa fa-building text-muted"></i> {{ $st['from_branch'] }}</td>
                        <td><i class="fa fa-arrow-right text-primary"></i> <strong>{{ $st['to_branch'] }}</strong></td>
                        <td><strong>{{ $st['material'] }}</strong></td>
                        <td><span class="badge badge-orange">{{ $st['quantity'] }}</span></td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $st['reason'] }}</td>
                        <td>
                            <button onclick="showToast('Inter-branch stock transfer initiated!', 'success')" class="btn btn-success btn-sm">
                                <i class="fa fa-truck-arrow-right"></i> Approve &amp; Transfer
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Branch Modal -->
<div class="modal-overlay" id="addBranchModal">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-header">
            <h3><i class="fa fa-building-circle-check"></i> Add New Branch / Depot</h3>
            <button class="modal-close" onclick="closeModal('addBranchModal')">✕</button>
        </div>
        <form id="addBranchForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Branch / Depot Name *</label>
                    <input type="text" name="name" id="add_b_name" required placeholder="e.g. Rajkot Distribution Hub">
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>City &amp; Location *</label>
                        <input type="text" name="city" id="add_b_city" required placeholder="e.g. Rajkot, Gujarat">
                    </div>
                    <div class="form-group">
                        <label>Branch Type *</label>
                        <select name="type" id="add_b_type" required>
                            <option value="Retail Depot">Retail Depot</option>
                            <option value="Distribution Hub">Distribution Hub</option>
                            <option value="Factory & Warehouse">Factory & Warehouse</option>
                            <option value="Sales Office">Sales Office</option>
                            <option value="Regional Storage">Regional Storage</option>
                        </select>
                    </div>
                </div>

                <div style="background: var(--bg-surface-secondary, #f8fafc); padding: 12px 14px; border-radius: 8px; margin-top: 6px; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--primary);">
                        <i class="fa fa-user-tie"></i> Branch Manager Details (કોણ મેનેજ કરે છે)
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Manager Name (સંચાલકનું નામ) *</label>
                        <input type="text" name="manager_name" id="add_b_manager_name" required placeholder="e.g. Ramesh Patel (Branch Head)">
                    </div>
                    <div class="form-row cols-2" style="margin-bottom: 0;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Manager Phone Number</label>
                            <input type="text" name="manager_phone" id="add_b_manager_phone" placeholder="e.g. +91 98250 XXXXX">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Manager Email</label>
                            <input type="email" name="manager_email" id="add_b_manager_email" placeholder="e.g. ramesh@shreegiriraj.com">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addBranchModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Branch</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal-overlay" id="editBranchModal">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-header">
            <h3><i class="fa fa-pen-to-square"></i> Edit Branch &amp; Manager Details</h3>
            <button class="modal-close" onclick="closeModal('editBranchModal')">✕</button>
        </div>
        <form id="editBranchForm">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-group">
                    <label>Branch / Depot Name *</label>
                    <input type="text" name="name" id="edit_b_name" required>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>City &amp; Location *</label>
                        <input type="text" name="city" id="edit_b_city" required>
                    </div>
                    <div class="form-group">
                        <label>Branch Type *</label>
                        <select name="type" id="edit_b_type" required>
                            <option value="Retail Depot">Retail Depot</option>
                            <option value="Distribution Hub">Distribution Hub</option>
                            <option value="Factory & Warehouse">Factory & Warehouse</option>
                            <option value="Sales Office">Sales Office</option>
                            <option value="Regional Storage">Regional Storage</option>
                        </select>
                    </div>
                </div>

                <div style="background: var(--bg-surface-secondary, #f8fafc); padding: 12px 14px; border-radius: 8px; margin-top: 6px; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--primary);">
                        <i class="fa fa-user-tie"></i> Branch Manager Details (કોણ મેનેજ કરે છે)
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Manager Name (સંચાલકનું નામ) *</label>
                        <input type="text" name="manager_name" id="edit_b_manager_name" required>
                    </div>
                    <div class="form-row cols-2" style="margin-bottom: 0;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Manager Phone Number</label>
                            <input type="text" name="manager_phone" id="edit_b_manager_phone">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Manager Email</label>
                            <input type="email" name="manager_email" id="edit_b_manager_email">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editBranchModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Branch</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editBranchUrl = '';

async function switchBranch(name) {
    try {
        const res = await fetch('{{ route("branches.switch") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ branch_name: name })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Error switching branch', 'error');
        }
    } catch(e) {
        showToast('Error switching branch', 'error');
    }
}

document.getElementById('addBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route("branches.store") }}', 'POST');
});

function editBranch(id, name, city, type, manager_name, manager_phone, manager_email) {
    editBranchUrl = `/branches/${id}`;
    document.getElementById('edit_b_name').value = name || '';
    document.getElementById('edit_b_city').value = city || '';
    document.getElementById('edit_b_type').value = type || 'Retail Depot';
    document.getElementById('edit_b_manager_name').value = manager_name || '';
    document.getElementById('edit_b_manager_phone').value = manager_phone || '';
    document.getElementById('edit_b_manager_email').value = manager_email || '';
    openModal('editBranchModal');
}

document.getElementById('editBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editBranchUrl, 'PUT');
});
</script>
@endsection
