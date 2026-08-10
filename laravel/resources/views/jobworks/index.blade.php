@extends('layouts.app')
@section('title', 'Job Work')
@section('page-title', 'Job Work Parties')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-screwdriver-wrench"></i> Job Work Parties</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addJobWorkModal')">
            <i class="fa fa-plus"></i> Add Party
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($jobWorks->isEmpty())
        <div class="empty-state"><i class="fa fa-screwdriver-wrench"></i><p>No job work parties added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Party Name</th><th>Phone</th><th>Work Type</th><th>Rate</th><th>Unit</th><th>Notes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($jobWorks as $jw)
                <tr>
                    <td>{{ $jw->id }}</td>
                    <td class="fw-bold">{{ $jw->party_name }}</td>
                    <td>{{ $jw->phone ?? '—' }}</td>
                    <td>{{ $jw->work_type ?? '—' }}</td>
                    <td>{{ $jw->rate ? '₹'.number_format($jw->rate, 2) : '—' }}</td>
                    <td>{{ $jw->unit ?? '—' }}</td>
                    <td>{{ Str::limit($jw->notes, 35) ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editJobWork({{ $jw->id }}, {{ json_encode($jw->party_name) }}, {{ json_encode($jw->phone) }}, {{ json_encode($jw->work_type) }}, {{ $jw->rate ?? 0 }}, {{ json_encode($jw->unit) }}, {{ json_encode($jw->address) }}, {{ json_encode($jw->notes) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('jobworks.destroy', $jw) }}', 'job work')">
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
<div class="modal-overlay" id="addJobWorkModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Job Work Party</h3>
            <button class="modal-close" onclick="closeModal('addJobWorkModal')">✕</button>
        </div>
        <form id="addJobWorkForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label>Party Name *</label><input type="text" name="party_name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Work Type</label><input type="text" name="work_type" placeholder="e.g. Weaving, Printing"></div>
                    <div class="form-group"><label>Rate (₹)</label><input type="number" name="rate" step="0.01" min="0"></div>
                    <div class="form-group"><label>Per Unit</label><input type="text" name="unit" placeholder="per kg / per pc"></div>
                </div>
                <div class="form-group"><label>Address</label><textarea name="address" rows="2"></textarea></div>
                <div class="form-group"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addJobWorkModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Party</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editJobWorkModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Job Work Party</h3>
            <button class="modal-close" onclick="closeModal('editJobWorkModal')">✕</button>
        </div>
        <form id="editJobWorkForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label>Party Name *</label><input type="text" id="ej_party" name="party_name" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" id="ej_phone" name="phone"></div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Work Type</label><input type="text" id="ej_worktype" name="work_type"></div>
                    <div class="form-group"><label>Rate (₹)</label><input type="number" id="ej_rate" name="rate" step="0.01" min="0"></div>
                    <div class="form-group"><label>Per Unit</label><input type="text" id="ej_unit" name="unit"></div>
                </div>
                <div class="form-group"><label>Address</label><textarea id="ej_address" name="address" rows="2"></textarea></div>
                <div class="form-group"><label>Notes</label><textarea id="ej_notes" name="notes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editJobWorkModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Party</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

document.getElementById('addJobWorkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('jobworks.store') }}', 'POST');
});

function editJobWork(id, party, phone, worktype, rate, unit, address, notes) {
    editUrl = `/job-works/${id}`;
    document.getElementById('ej_party').value = party || '';
    document.getElementById('ej_phone').value = phone || '';
    document.getElementById('ej_worktype').value = worktype || '';
    document.getElementById('ej_rate').value = rate || '';
    document.getElementById('ej_unit').value = unit || '';
    document.getElementById('ej_address').value = address || '';
    document.getElementById('ej_notes').value = notes || '';
    openModal('editJobWorkModal');
}

document.getElementById('editJobWorkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
