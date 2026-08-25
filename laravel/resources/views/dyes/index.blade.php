@extends('layouts.app')
@section('title', 'Dyes & Moulds Inventory — Shree Giriraj Poly Plast')
@section('page-title', 'Dyes & Moulds Inventory')

@section('content')
<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-shapes" style="color:var(--primary, #6366f1);"></i> Dyes &amp; Moulds Inventory
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Tool room inventory, cavities, machine compatibility &amp; PM shot counter tracking</p>
    </div>
    <div style="display:flex; gap:10px;">
        <button class="btn btn-primary" onclick="openCreateDyeModal()">
            <i class="fa fa-plus"></i> Add New Dye / Mould
        </button>
    </div>
</div>

<!-- KPI Metric Cards Grid -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-shapes"></i></div>
            <span class="badge badge-purple">Total Tooling</span>
        </div>
        <div class="stat-label">Total Dyes / Moulds</div>
        <div class="stat-value">{{ number_format($totalCount) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <span class="badge badge-success">Ready</span>
        </div>
        <div class="stat-label">In Tool Room Storage</div>
        <div class="stat-value">{{ number_format($readyCount) }}</div>
    </div>
    <div class="stat-card s-blue">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-gears"></i></div>
            <span class="badge badge-info">Production</span>
        </div>
        <div class="stat-label">Mounted on Machine</div>
        <div class="stat-value">{{ number_format($onMachineCount) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-wrench"></i></div>
            <span class="badge badge-warning">Service</span>
        </div>
        <div class="stat-label">Under Maintenance</div>
        <div class="stat-value">{{ number_format($maintenanceCount) }}</div>
    </div>
    <div class="stat-card s-purple">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-user-tag"></i></div>
            <span class="badge badge-purple">Client Tools</span>
        </div>
        <div class="stat-label">Client Owned Moulds</div>
        <div class="stat-value">{{ number_format($clientOwned) }}</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('dyes.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px; gap:12px; align-items:center;">
        <div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search Code, Name, Rack..." class="form-control" style="width:100%;">
        </div>
        <div>
            <select name="mould_type" class="form-control" style="width:100%;">
                <option value="">All Mould Types</option>
                <option value="Blow Mould" {{ $type === 'Blow Mould' ? 'selected' : '' }}>Blow Mould (બોટલ/જાર)</option>
                <option value="Injection Mould" {{ $type === 'Injection Mould' ? 'selected' : '' }}>Injection Mould (કેપ/પાર્ટ્સ)</option>
                <option value="Extrusion Die" {{ $type === 'Extrusion Die' ? 'selected' : '' }}>Extrusion Die</option>
                <option value="Compression Mould" {{ $type === 'Compression Mould' ? 'selected' : '' }}>Compression Mould</option>
            </select>
        </div>
        <div>
            <select name="status" class="form-control" style="width:100%;">
                <option value="">All Statuses</option>
                <option value="Ready / In Storage" {{ $status === 'Ready / In Storage' ? 'selected' : '' }}>Ready / In Storage</option>
                <option value="Mounted on Machine" {{ $status === 'Mounted on Machine' ? 'selected' : '' }}>Mounted on Machine</option>
                <option value="Under Maintenance" {{ $status === 'Under Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                <option value="Scrapped" {{ $status === 'Scrapped' ? 'selected' : '' }}>Scrapped</option>
            </select>
        </div>
        <div>
            <select name="ownership_type" class="form-control" style="width:100%;">
                <option value="">All Ownerships</option>
                <option value="Company Owned" {{ $ownership === 'Company Owned' ? 'selected' : '' }}>Company Owned</option>
                <option value="Client Owned" {{ $ownership === 'Client Owned' ? 'selected' : '' }}>Client Owned</option>
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fa fa-filter"></i> Filter</button>
            <a href="{{ route('dyes.index') }}" class="btn btn-secondary" title="Reset Filters"><i class="fa fa-redo"></i></a>
        </div>
    </form>
</div>

<!-- Dyes & Moulds Table Card -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-shapes text-primary"></i> Dyes &amp; Moulds Master Directory ({{ $dyes->total() }})</h3>
    </div>
    <div class="card-body" style="padding:0;">
        @if($dyes->isEmpty())
        <div class="empty-state" style="padding:48px 20px; text-align:center;">
            <div class="empty-icon" style="font-size:40px; color:#cbd5e1; margin-bottom:12px;"><i class="fa-solid fa-shapes"></i></div>
            <p style="color:var(--text-muted); font-size:15px; margin-bottom:16px;">No Dyes or Moulds found matching the filter criteria.</p>
            <button class="btn btn-primary btn-sm" onclick="openCreateDyeModal()">Add First Dye / Mould</button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Dye / Mould Details</th>
                        <th>Type &amp; Cavities</th>
                        <th>Ownership &amp; Client</th>
                        <th>Rack &amp; Machine</th>
                        <th>Status</th>
                        <th>Shots &amp; PM Alert</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dyes as $dye)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; overflow:hidden; border:1px solid #e2e8f0;">
                                    @if($dye->image)
                                        <img src="{{ $dye->image_url }}" alt="{{ $dye->name }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <i class="fa-solid fa-shapes"></i>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('dyes.show', $dye) }}" style="font-weight:700; color:var(--text-dark, #0f172a); text-decoration:none; font-size:14px;">{{ $dye->name }}</a>
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:2px;">
                                        <code style="background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700;">{{ $dye->code }}</code>
                                        @if($dye->fabrication_date)
                                            <span style="font-size:11px; color:var(--text-muted);">Mfg: {{ $dye->fabrication_date->format('M Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13px; color:var(--text-dark);">{{ $dye->mould_type }}</div>
                            <span class="badge badge-purple" style="font-size:11px; margin-top:2px;">
                                {{ $dye->cavities }} {{ Str::plural('Cavity', $dye->cavities) }}
                            </span>
                        </td>
                        <td>
                            @if($dye->ownership_type === 'Company Owned')
                                <span class="badge badge-success" style="display:inline-flex; align-items:center; gap:4px;">
                                    <i class="fa fa-building"></i> Company Owned
                                </span>
                            @else
                                <span class="badge badge-purple" style="display:inline-flex; align-items:center; gap:4px;">
                                    <i class="fa fa-user-tag"></i> {{ $dye->customer?->name ?? 'Client Owned' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12px; font-weight:600; color:var(--text-dark); display:flex; align-items:center; gap:4px;">
                                <i class="fa fa-location-dot text-muted"></i> {{ $dye->rack_location ?: 'Tool Room' }}
                            </div>
                            @if($dye->compatible_machines)
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $dye->compatible_machines }}">
                                    <i class="fa fa-gears text-muted"></i> {{ $dye->compatible_machines }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($dye->status === 'Mounted on Machine')
                                <span class="badge badge-info"><i class="fa fa-cog fa-spin"></i> On Machine</span>
                            @elseif($dye->status === 'Ready / In Storage')
                                <span class="badge badge-success"><i class="fa fa-check"></i> Ready</span>
                            @elseif($dye->status === 'Under Maintenance')
                                <span class="badge badge-warning"><i class="fa fa-wrench"></i> Maintenance</span>
                            @else
                                <span class="badge badge-secondary">{{ $dye->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:700; font-size:13px; font-family:monospace; color:var(--text-dark);">
                                {{ number_format($dye->total_shots_count) }} Shots
                            </div>
                            @if($dye->is_maintenance_due)
                                <span class="badge badge-danger" style="margin-top:2px;">
                                    <i class="fa fa-triangle-exclamation"></i> PM Overdue!
                                </span>
                            @else
                                <span style="font-size:11px; color:var(--text-muted);">Rem: {{ number_format($dye->shots_remaining_for_service) }}</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:6px; justify-content:flex-end;">
                                <a href="{{ route('dyes.show', $dye) }}" class="btn btn-outline btn-sm btn-icon" title="View Specifications &amp; Maintenance Logs">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button class="btn btn-outline btn-sm btn-icon" title="Edit Dye Details" onclick='openEditDyeModal(@json($dye))'>
                                    <i class="fa fa-pen"></i>
                                </button>
                                <form action="{{ route('dyes.destroy', $dye) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this dye?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px; border-top:1px solid #f1f5f9;">
            {{ $dyes->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal-overlay" id="dyeModal">
    <div class="modal" style="max-width:680px;">
        <div class="modal-header">
            <h3 id="dyeModalTitle"><i class="fa-solid fa-shapes text-primary"></i> Add New Dye / Mould</h3>
            <button class="modal-close" onclick="closeModal('dyeModal')">✕</button>
        </div>
        <form id="dyeForm" method="POST" action="{{ route('dyes.store') }}" enctype="multipart/form-data">
            @csrf
            <div id="dyeMethodField"></div>
            <div class="modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Dye / Mould Code</label>
                        <input type="text" name="code" id="dye_code" placeholder="Auto-generated if blank (e.g. DIE-001)" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Dye / Mould Name</label>
                        <input type="text" name="name" id="dye_name" required placeholder="e.g. 500ml HDPE Bottle Mould" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Mould Type</label>
                        <select name="mould_type" id="dye_mould_type" required class="form-control">
                            <option value="Blow Mould">Blow Mould (બોટલ / જાર મોલ્ડ)</option>
                            <option value="Injection Mould">Injection Mould (ઢાંકણા / કેપ / મોલ્ડ)</option>
                            <option value="Extrusion Die">Extrusion Die (પાઈપ / પ્રોફાઈલ)</option>
                            <option value="Compression Mould">Compression Mould</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label required">Number of Cavities</label>
                        <input type="number" name="cavities" id="dye_cavities" min="1" value="1" required class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Ownership Type</label>
                        <select name="ownership_type" id="dye_ownership_type" onchange="toggleClientSelect()" required class="form-control">
                            <option value="Company Owned">Company Owned (ફેક્ટરીની પોતાની)</option>
                            <option value="Client Owned">Client Owned (પાર્ટી / ગ્રાહકની ડાઈ)</option>
                        </select>
                    </div>
                    <div id="clientSelectDiv" style="display:none;">
                        <label class="form-label">Select Client / Customer</label>
                        <select name="customer_id" id="dye_customer_id" class="form-control">
                            <option value="">-- Choose Client --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Rack / Shelf Location in Tool Room</label>
                        <input type="text" name="rack_location" id="dye_rack_location" placeholder="e.g. Tool Room Rack A - Bay 2" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Current Status</label>
                        <select name="status" id="dye_status" required class="form-control">
                            <option value="Ready / In Storage">Ready / In Storage</option>
                            <option value="Mounted on Machine">Mounted on Machine</option>
                            <option value="Under Maintenance">Under Maintenance</option>
                            <option value="Scrapped">Scrapped</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Compatible Machines / Press Tonnage</label>
                        <input type="text" name="compatible_machines" id="dye_compatible_machines" placeholder="e.g. 150T Windsor / ABM-01" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Current Shot Count</label>
                        <input type="number" name="total_shots_count" id="dye_total_shots_count" min="0" value="0" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">PM Service Interval (Shots)</label>
                        <input type="number" name="service_interval_shots" id="dye_service_interval_shots" min="100" value="50000" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Purchase / Asset Cost (₹)</label>
                        <input type="number" step="0.01" name="purchase_cost" id="dye_purchase_cost" placeholder="0.00" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Next Maintenance Due Date</label>
                        <input type="date" name="next_service_due_date" id="dye_next_service_due_date" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Mould Photo</label>
                        <input type="file" name="image" accept="image/*" class="form-control">
                    </div>
                </div>

                <div>
                    <label class="form-label">Technical Notes &amp; Core/Cavity Parameters</label>
                    <textarea name="notes" id="dye_notes" rows="2" placeholder="Steel grade, cooling channels, runner manifold details..." class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('dyeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Dye / Mould</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCreateDyeModal() {
    document.getElementById('dyeModalTitle').innerHTML = '<i class="fa-solid fa-shapes text-primary"></i> Add New Dye / Mould';
    document.getElementById('dyeForm').action = "{{ route('dyes.store') }}";
    document.getElementById('dyeMethodField').innerHTML = '';
    document.getElementById('dyeForm').reset();
    document.getElementById('dye_cavities').value = '1';
    document.getElementById('dye_service_interval_shots').value = '50000';
    toggleClientSelect();
    openModal('dyeModal');
}

function openEditDyeModal(dye) {
    document.getElementById('dyeModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-primary"></i> Edit Dye / Mould: ' + dye.code;
    document.getElementById('dyeForm').action = "/dyes/" + dye.id;
    document.getElementById('dyeMethodField').innerHTML = '@method("PUT")';
    
    document.getElementById('dye_code').value = dye.code || '';
    document.getElementById('dye_name').value = dye.name || '';
    document.getElementById('dye_mould_type').value = dye.mould_type || 'Blow Mould';
    document.getElementById('dye_cavities').value = dye.cavities || '1';
    document.getElementById('dye_ownership_type').value = dye.ownership_type || 'Company Owned';
    document.getElementById('dye_customer_id').value = dye.customer_id || '';
    document.getElementById('dye_rack_location').value = dye.rack_location || '';
    document.getElementById('dye_status').value = dye.status || 'Ready / In Storage';
    document.getElementById('dye_compatible_machines').value = dye.compatible_machines || '';
    document.getElementById('dye_total_shots_count').value = dye.total_shots_count || 0;
    document.getElementById('dye_service_interval_shots').value = dye.service_interval_shots || 50000;
    document.getElementById('dye_purchase_cost').value = dye.purchase_cost || '';
    document.getElementById('dye_next_service_due_date').value = dye.next_service_due_date ? dye.next_service_due_date.substring(0, 10) : '';
    document.getElementById('dye_notes').value = dye.notes || '';
    
    toggleClientSelect();
    openModal('dyeModal');
}

function toggleClientSelect() {
    const ownership = document.getElementById('dye_ownership_type').value;
    const clientDiv = document.getElementById('clientSelectDiv');
    if (ownership === 'Client Owned') {
        clientDiv.style.display = 'block';
    } else {
        clientDiv.style.display = 'none';
    }
}
</script>
@endpush
@endsection
