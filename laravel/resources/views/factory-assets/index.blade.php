@extends('layouts.app')
@section('title', 'Plant Machinery & Factory Assets — Shree Giriraj Poly Plast')
@section('page-title', 'Plant Machinery & Factory Assets')

@section('content')
<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-industry" style="color:var(--primary, #6366f1);"></i> Plant Machinery &amp; Factory Assets
        </h2>
        <p style="font-size:13px; color:var(--text-muted, #64748b); margin:4px 0 0 0;">Injection &amp; Blow moulding machines, chillers, screw compressors, power DG sets &amp; breakdown logs</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openCreateAssetModal()">
            <i class="fa fa-plus"></i> Register Machine / Asset
        </button>
    </div>
</div>

<!-- KPI Metric Cards Grid -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card s-indigo">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-industry"></i></div>
            <span class="badge badge-purple">Total Assets</span>
        </div>
        <div class="stat-label">Total Plant Machines</div>
        <div class="stat-value">{{ number_format($totalAssets) }}</div>
    </div>
    <div class="stat-card s-emerald">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-bolt"></i></div>
            <span class="badge badge-success">Running</span>
        </div>
        <div class="stat-label">Operational / Running</div>
        <div class="stat-value">{{ number_format($runningCount) }}</div>
    </div>
    <div class="stat-card s-blue">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-pause"></i></div>
            <span class="badge badge-info">Standby</span>
        </div>
        <div class="stat-label">Standby / Idle</div>
        <div class="stat-value">{{ number_format($standbyCount) }}</div>
    </div>
    <div class="stat-card s-red">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <span class="badge badge-danger">Breakdowns</span>
        </div>
        <div class="stat-label">Breakdown Repairs</div>
        <div class="stat-value">{{ number_format($breakdownCount) }}</div>
    </div>
    <div class="stat-card s-amber">
        <div class="stat-top">
            <div class="stat-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
            <span class="badge badge-warning">Valuation</span>
        </div>
        <div class="stat-label">Total Asset Capital</div>
        <div class="stat-value">₹{{ number_format((float)$totalAssetValue, 0) }}</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom:20px; padding:16px;">
    <form method="GET" action="{{ route('factory-assets.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px; gap:12px; align-items:center;">
        <div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search Code, Name, Location..." class="form-control" style="width:100%;">
        </div>
        <div>
            <select name="category" class="form-control" style="width:100%;">
                <option value="">All Categories</option>
                <option value="Moulding Machine" {{ $category === 'Moulding Machine' ? 'selected' : '' }}>Moulding Machine (ઇન્જેક્શન/બ્લોવ)</option>
                <option value="Compressor & Chiller" {{ $category === 'Compressor & Chiller' ? 'selected' : '' }}>Compressor &amp; Chiller (કમ્પ્રેસર/ચિલર)</option>
                <option value="Auxiliary Equipment" {{ $category === 'Auxiliary Equipment' ? 'selected' : '' }}>Auxiliary / Grinder (ગ્રાઇન્ડર/મિક્સર)</option>
                <option value="Electrical & Power" {{ $category === 'Electrical & Power' ? 'selected' : '' }}>Electrical &amp; DG Set (જનરેટર)</option>
                <option value="Material Handling" {{ $category === 'Material Handling' ? 'selected' : '' }}>Material Handling (ક્રેન/હોઈસ્ટ)</option>
            </select>
        </div>
        <div>
            <select name="status" class="form-control" style="width:100%;">
                <option value="">All Statuses</option>
                <option value="Operational" {{ $status === 'Operational' ? 'selected' : '' }}>Operational (ચાલુ / કાર્યરત)</option>
                <option value="Standby" {{ $status === 'Standby' ? 'selected' : '' }}>Standby (સ્ટેન્ડબાય)</option>
                <option value="Breakdown" {{ $status === 'Breakdown' ? 'selected' : '' }}>Breakdown (બગડેલ / રિપેર)</option>
                <option value="Maintenance / Overhaul" {{ $status === 'Maintenance / Overhaul' ? 'selected' : '' }}>Under Maintenance</option>
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fa fa-filter"></i> Filter</button>
            <a href="{{ route('factory-assets.index') }}" class="btn btn-secondary" title="Reset Filters"><i class="fa fa-redo"></i></a>
        </div>
    </form>
</div>

<!-- Assets Table Card -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-industry text-primary"></i> Factory Machinery &amp; Plant Assets Directory ({{ $assets->total() }})</h3>
    </div>
    <div class="card-body" style="padding:0;">
        @if($assets->isEmpty())
        <div class="empty-state" style="padding:48px 20px; text-align:center;">
            <div class="empty-icon" style="font-size:40px; color:#cbd5e1; margin-bottom:12px;"><i class="fa-solid fa-industry"></i></div>
            <p style="color:var(--text-muted); font-size:15px; margin-bottom:16px;">No Factory Machinery or Assets found matching criteria.</p>
            <button class="btn btn-primary btn-sm" onclick="openCreateAssetModal()">Register First Machine</button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Machine / Asset Details</th>
                        <th>Category &amp; Make</th>
                        <th>Capacity &amp; Power</th>
                        <th>Plant Location</th>
                        <th>Status</th>
                        <th>Next PM Schedule</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; overflow:hidden; border:1px solid #e2e8f0;">
                                    @if($asset->image)
                                        <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <i class="fa-solid fa-industry"></i>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('factory-assets.show', $asset) }}" style="font-weight:700; color:var(--text-dark, #0f172a); text-decoration:none; font-size:14px;">{{ $asset->name }}</a>
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:2px;">
                                        <code style="background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700;">{{ $asset->asset_code }}</code>
                                        @if($asset->model_number)
                                            <span style="font-size:11px; color:var(--text-muted);">Model: {{ $asset->model_number }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13px; color:var(--text-dark);">{{ $asset->category }}</div>
                            <span style="font-size:11px; color:var(--text-muted);">{{ $asset->make_brand ?: '—' }}</span>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:13px; color:var(--text-dark);">{{ $asset->tonnage_or_capacity ?: '—' }}</div>
                            @if($asset->power_rating_kw)
                                <div style="font-size:11px; color:var(--text-muted); font-family:monospace; margin-top:2px;"><i class="fa fa-bolt text-warning"></i> {{ $asset->power_rating_kw }} kW</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12px; font-weight:600; color:var(--text-dark); display:flex; align-items:center; gap:4px;">
                                <i class="fa fa-location-dot text-muted"></i> {{ $asset->plant_location ?: 'Shop Floor' }}
                            </div>
                            @if($asset->assigned_operator)
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><i class="fa fa-user text-muted"></i> {{ $asset->assigned_operator }}</div>
                            @endif
                        </td>
                        <td>
                            @if($asset->status === 'Operational')
                                <span class="badge badge-success"><i class="fa fa-circle-play"></i> Running</span>
                            @elseif($asset->status === 'Standby')
                                <span class="badge badge-info"><i class="fa fa-pause"></i> Standby</span>
                            @elseif($asset->status === 'Breakdown')
                                <span class="badge badge-danger"><i class="fa fa-circle-xmark"></i> Breakdown</span>
                            @else
                                <span class="badge badge-warning"><i class="fa fa-wrench"></i> Maintenance</span>
                            @endif
                        </td>
                        <td>
                            @if($asset->next_service_date)
                                <div style="font-size:12px; font-weight:700; color:{{ $asset->is_maintenance_due ? '#ef4444' : '#0f172a' }};">
                                    {{ $asset->next_service_date->format('d M Y') }}
                                </div>
                                @if($asset->is_maintenance_due)
                                    <span class="badge badge-danger" style="margin-top:2px;"><i class="fa fa-triangle-exclamation"></i> PM Overdue!</span>
                                @else
                                    <span style="font-size:11px; color:var(--text-muted);">Scheduled PM</span>
                                @endif
                            @else
                                <span style="font-size:11px; color:var(--text-muted);">Not Set</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:6px; justify-content:flex-end;">
                                <a href="{{ route('factory-assets.show', $asset) }}" class="btn btn-outline btn-sm btn-icon" title="View Details &amp; Service History">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button class="btn btn-outline btn-sm btn-icon" title="Edit Asset Details" onclick="openEditAssetModal({{ $asset->id }})">
                                    <i class="fa fa-pen"></i>
                                </button>
                                <form action="{{ route('factory-assets.destroy', $asset) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this machine?');">
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
            {{ $assets->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal-overlay" id="assetModal">
    <div class="modal" style="max-width:680px;">
        <div class="modal-header">
            <h3 id="assetModalTitle"><i class="fa-solid fa-industry text-primary"></i> Register New Plant Asset</h3>
            <button class="modal-close" onclick="closeModal('assetModal')">✕</button>
        </div>
        <form id="assetForm" method="POST" action="{{ route('factory-assets.store') }}" enctype="multipart/form-data">
            @csrf
            <div id="assetMethodField"></div>
            <div class="modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Asset Code / Machine Tag</label>
                        <input type="text" name="asset_code" id="asset_code" placeholder="Auto-generated if blank (e.g. MCH-01)" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Machine / Asset Name</label>
                        <input type="text" name="name" id="asset_name" required placeholder="e.g. 150T Injection Moulding Machine" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Asset Category</label>
                        <select name="category" id="asset_category" required class="form-control">
                            <option value="Moulding Machine">Moulding Machine (ઇન્જેક્શન/બ્લોવ મોલ્ડિંગ)</option>
                            <option value="Compressor & Chiller">Compressor &amp; Chiller (કમ્પ્રેસર/ચિલર)</option>
                            <option value="Auxiliary Equipment">Auxiliary Equipment (ગ્રાઇન્ડર/મિક્સર/હોપર)</option>
                            <option value="Electrical & Power">Electrical &amp; Power (જનરેટર/ટ્રાન્સફોર્મર)</option>
                            <option value="Material Handling">Material Handling (ક્રેન/હોઈસ્ટ)</option>
                            <option value="Quality & Lab">Quality &amp; Lab Testing</option>
                            <option value="Packaging & Tool Room">Packaging &amp; Tool Room</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Make / Manufacturer Brand</label>
                        <input type="text" name="make_brand" id="asset_make_brand" placeholder="e.g. Windsor / Ferromatik / Blue Star" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Model Number</label>
                        <input type="text" name="model_number" id="asset_model_number" placeholder="e.g. Sprint 150-V" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Serial Number</label>
                        <input type="text" name="serial_number" id="asset_serial_number" placeholder="e.g. WND-2022-7819" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Capacity / Tonnage</label>
                        <input type="text" name="tonnage_or_capacity" id="asset_tonnage_or_capacity" placeholder="e.g. 150 Ton / 10 TR" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Power Rating (kW)</label>
                        <input type="number" step="0.1" name="power_rating_kw" id="asset_power_rating_kw" placeholder="e.g. 28.5" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Plant Location / Bay</label>
                        <input type="text" name="plant_location" id="asset_plant_location" placeholder="e.g. Shop Floor Bay 1 - Line 1" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label required">Current Machine Status</label>
                        <select name="status" id="asset_status" required class="form-control">
                            <option value="Operational">Operational (ચાલુ / કાર્યરત)</option>
                            <option value="Standby">Standby (સ્ટેન્ડબાય)</option>
                            <option value="Breakdown">Breakdown (બગડેલ / રિપેર)</option>
                            <option value="Maintenance / Overhaul">Maintenance / Overhaul</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Assigned Operator / In-charge</label>
                        <input type="text" name="assigned_operator" id="asset_assigned_operator" placeholder="e.g. Ramesh Patel" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" id="asset_purchase_date" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Purchase Cost (₹)</label>
                        <input type="number" step="0.01" name="purchase_cost" id="asset_purchase_cost" placeholder="0.00" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" id="asset_warranty_expiry" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Next Scheduled PM Due Date</label>
                        <input type="date" name="next_service_date" id="asset_next_service_date" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Machine Photo</label>
                        <input type="file" name="image" accept="image/*" class="form-control">
                    </div>
                </div>

                <div>
                    <label class="form-label">Maintenance Notes &amp; Lubrication Specs</label>
                    <textarea name="notes" id="asset_notes" rows="2" placeholder="Hydraulic oil grade, lubrication specs..." class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('assetModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Asset</button>
            </div>
        </form>
    </div>
</div>

<script>
window.assetsData = @json($assets->items());
window.assetsMap = {};
window.assetsData.forEach(function(a) { window.assetsMap[a.id] = a; });

window.openCreateAssetModal = function() {
    document.getElementById('assetModalTitle').innerHTML = '<i class="fa-solid fa-industry text-primary"></i> Register New Plant Asset';
    document.getElementById('assetForm').action = "{{ route('factory-assets.store') }}";
    document.getElementById('assetMethodField').innerHTML = '';
    document.getElementById('assetForm').reset();
    openModal('assetModal');
};

window.openEditAssetModal = function(assetId) {
    const asset = window.assetsMap[assetId] || {};
    document.getElementById('assetModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-primary"></i> Edit Machine: ' + (asset.asset_code || '');
    document.getElementById('assetForm').action = "/factory-assets/" + (asset.id || assetId);
    document.getElementById('assetMethodField').innerHTML = '@method("PUT")';
    
    document.getElementById('asset_code').value = asset.asset_code || '';
    document.getElementById('asset_name').value = asset.name || '';
    document.getElementById('asset_category').value = asset.category || 'Moulding Machine';
    document.getElementById('asset_make_brand').value = asset.make_brand || '';
    document.getElementById('asset_model_number').value = asset.model_number || '';
    document.getElementById('asset_serial_number').value = asset.serial_number || '';
    document.getElementById('asset_tonnage_or_capacity').value = asset.tonnage_or_capacity || '';
    document.getElementById('asset_power_rating_kw').value = asset.power_rating_kw || '';
    document.getElementById('asset_plant_location').value = asset.plant_location || '';
    document.getElementById('asset_status').value = asset.status || 'Operational';
    document.getElementById('asset_assigned_operator').value = asset.assigned_operator || '';
    document.getElementById('asset_purchase_date').value = asset.purchase_date ? asset.purchase_date.substring(0, 10) : '';
    document.getElementById('asset_purchase_cost').value = asset.purchase_cost || '';
    document.getElementById('asset_warranty_expiry').value = asset.warranty_expiry ? asset.warranty_expiry.substring(0, 10) : '';
    document.getElementById('asset_next_service_date').value = asset.next_service_date ? asset.next_service_date.substring(0, 10) : '';
    document.getElementById('asset_notes').value = asset.notes || '';
    
    openModal('assetModal');
};
</script>
@endsection
