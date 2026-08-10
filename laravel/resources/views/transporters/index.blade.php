@extends('layouts.app')
@section('title', 'Transporters')
@section('page-title', 'Transporters')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-truck"></i> Transporter List</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addTransporterModal')">
            <i class="fa fa-plus"></i> Add Transporter
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($transporters->isEmpty())
        <div class="empty-state"><i class="fa fa-truck"></i><p>No transporters added yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Vehicle No.</th><th>Phone</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($transporters as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td class="fw-bold">{{ $t->name }}</td>
                    <td><code>{{ $t->vehicle_no ?? '—' }}</code></td>
                    <td>{{ $t->phone ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline btn-sm btn-icon"
                                onclick="editTransporter({{ $t->id }}, {{ json_encode($t->name) }}, {{ json_encode($t->vehicle_no) }}, {{ json_encode($t->phone) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('{{ route('transporters.destroy', $t) }}', 'transporter')">
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
<div class="modal-overlay" id="addTransporterModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Transporter</h3>
            <button class="modal-close" onclick="closeModal('addTransporterModal')">✕</button>
        </div>
        <form id="addTransporterForm">
            <div class="modal-body">
                <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Vehicle No.</label><input type="text" name="vehicle_no"></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addTransporterModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editTransporterModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Transporter</h3>
            <button class="modal-close" onclick="closeModal('editTransporterModal')">✕</button>
        </div>
        <form id="editTransporterForm">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-group"><label>Name *</label><input type="text" id="et_name" name="name" required></div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Vehicle No.</label><input type="text" id="et_vehicle" name="vehicle_no"></div>
                    <div class="form-group"><label>Phone</label><input type="text" id="et_phone" name="phone"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editTransporterModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

document.getElementById('addTransporterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('transporters.store') }}', 'POST');
});

function editTransporter(id, name, vehicle, phone) {
    editUrl = `/transporters/${id}`;
    document.getElementById('et_name').value = name || '';
    document.getElementById('et_vehicle').value = vehicle || '';
    document.getElementById('et_phone').value = phone || '';
    openModal('editTransporterModal');
}

document.getElementById('editTransporterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});
</script>
@endsection
