@extends('layouts.app')
@section('title', 'Job Work Clients')
@section('page-title', 'Job Work Clients & Parties')

@section('content')
<div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
    <div>
        <h2 style="font-size: 19px; font-weight: 700; color: var(--text);">
            <i class="fa fa-users text-primary"></i> Job Work Clients &amp; Parties Master
        </h2>
        <p class="text-muted" style="font-size: 12.5px;">Manage reusable clients, GSTIN, contact details, and their Job Work orders</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" onclick="openAddClientModal()">
            <i class="fa fa-plus"></i> Add Client / Party
        </button>
        <a href="{{ route('jobworks.index') }}" class="btn btn-outline btn-sm">
            <i class="fa fa-scale-balanced"></i> Job Work Orders
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-building-user"></i> Registered Clients ({{ $clients->count() }})</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($clients->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-users"></i></div>
            <p>No Job Work clients added yet.</p>
            <button class="btn btn-primary btn-sm" onclick="openAddClientModal()"><i class="fa fa-plus"></i> Add First Client</button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Company / Firm</th>
                        <th>Phone</th>
                        <th>GSTIN</th>
                        <th>Orders Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td class="fw-bold">{{ $c->name }}</td>
                        <td>{{ $c->company_name ?: '—' }}</td>
                        <td>
                            @if($c->phone)
                                <a href="tel:{{ $c->phone }}" style="color:inherit; text-decoration:none;"><i class="fa fa-phone text-muted"></i> {{ $c->phone }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td><code>{{ $c->gstin ?: '—' }}</code></td>
                        <td>
                            <a href="{{ route('jobworks.index', ['client_id' => $c->id]) }}" class="badge badge-purple" style="text-decoration:none;">
                                {{ $c->orders_count }} Orders
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ $c->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $c->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline btn-sm btn-icon" title="Edit Client"
                                    onclick="editClient({{ $c->id }}, {{ json_encode($c->name) }}, {{ json_encode($c->company_name) }}, {{ json_encode($c->phone) }}, {{ json_encode($c->email) }}, {{ json_encode($c->gstin) }}, {{ json_encode($c->address) }}, {{ $c->is_active ? 1 : 0 }}, {{ json_encode($c->notes) }})">
                                    <i class="fa fa-pen"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-icon" title="Delete Client"
                                    onclick="deleteRecord('{{ route('jobworks.clients.destroy', $c) }}', 'client {{ $c->name }}')">
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

<!-- Add Client Modal -->
<div class="modal-overlay" id="addClientModal">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-header">
            <h3><i class="fa fa-user-plus"></i> Add Job Work Client</h3>
            <button class="modal-close" onclick="closeModal('addClientModal')">✕</button>
        </div>
        <form id="addClientForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Client / Contact Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Ramesh Patel">
                    </div>
                    <div class="form-group">
                        <label>Company / Firm Name</label>
                        <input type="text" name="company_name" placeholder="e.g. Shree Polymers">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Phone / Mobile</label>
                        <input type="text" name="phone" placeholder="+91 98250 XXXXX">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="client@example.com">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN</label>
                        <input type="text" name="gstin" placeholder="24AAAAA0000A1Z5">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" placeholder="Full postal address..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Notes / Work Type</label>
                    <input type="text" name="notes" placeholder="e.g. Specializes in HDPE molding">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addClientModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Client</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Client Modal -->
<div class="modal-overlay" id="editClientModal">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-header">
            <h3><i class="fa fa-pen"></i> Edit Job Work Client</h3>
            <button class="modal-close" onclick="closeModal('editClientModal')">✕</button>
        </div>
        <form id="editClientForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Client / Contact Name *</label>
                        <input type="text" id="ec_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Company / Firm Name</label>
                        <input type="text" id="ec_company" name="company_name">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Phone / Mobile</label>
                        <input type="text" id="ec_phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="ec_email" name="email">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN</label>
                        <input type="text" id="ec_gstin" name="gstin">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="ec_active" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea id="ec_address" name="address" rows="2"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Notes / Work Type</label>
                    <input type="text" id="ec_notes" name="notes">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editClientModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Update Client</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editClientUrl = '';

function openAddClientModal() {
    document.getElementById('addClientForm').reset();
    openModal('addClientModal');
}

document.getElementById('addClientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('jobworks.clients.store') }}', 'POST');
});

function editClient(id, name, company, phone, email, gstin, address, is_active, notes) {
    editClientUrl = `/job-work-clients/${id}`;
    document.getElementById('ec_name').value = name || '';
    document.getElementById('ec_company').value = company || '';
    document.getElementById('ec_phone').value = phone || '';
    document.getElementById('ec_email').value = email || '';
    document.getElementById('ec_gstin').value = gstin || '';
    document.getElementById('ec_address').value = address || '';
    document.getElementById('ec_active').value = is_active ? '1' : '0';
    document.getElementById('ec_notes').value = notes || '';
    openModal('editClientModal');
}

document.getElementById('editClientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editClientUrl, 'PUT');
});
</script>
@endsection
