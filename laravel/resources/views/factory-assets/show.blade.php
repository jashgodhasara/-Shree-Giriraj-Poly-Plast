@extends('layouts.app')
@section('title', $factoryAsset->name . ' — Machine Specifications & History')
@section('page-title', 'Machine Details: ' . $factoryAsset->name)

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <a href="{{ route('factory-assets.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-arrow-left"></i> Back to Plant Machinery
        </a>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-industry text-primary"></i> {{ $factoryAsset->name }}
        </h2>
        <div style="display:flex; align-items:center; gap:8px; margin-top:6px;">
            <code style="background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:4px; font-weight:700; font-size:12px;">{{ $factoryAsset->asset_code }}</code>
            <span class="badge badge-purple">{{ $factoryAsset->category }}</span>
            @if($factoryAsset->status === 'Operational')
                <span class="badge badge-success"><i class="fa fa-circle-play"></i> Running</span>
            @elseif($factoryAsset->status === 'Standby')
                <span class="badge badge-info"><i class="fa fa-pause"></i> Standby</span>
            @elseif($factoryAsset->status === 'Breakdown')
                <span class="badge badge-danger"><i class="fa fa-circle-xmark"></i> Breakdown</span>
            @else
                <span class="badge badge-warning"><i class="fa fa-wrench"></i> Under Maintenance</span>
            @endif
        </div>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openModal('serviceModal')">
            <i class="fa-solid fa-screwdriver-wrench"></i> Log Service / Breakdown
        </button>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:24px; margin-bottom:24px;">
    {{-- Left Card: Machine Specs --}}
    <div class="card">
        <div class="card-header">
            <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-circle-info text-primary"></i> Machine Specifications</h3>
        </div>
        <div class="card-body">
            @if($factoryAsset->image)
            <div style="margin-bottom:16px; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; max-height:220px; display:flex; align-items:center; justify-content:center; background:#f8fafc;">
                <img src="{{ $factoryAsset->image_url }}" alt="{{ $factoryAsset->name }}" style="max-height:200px; width:100%; object-fit:contain;">
            </div>
            @endif

            <table style="width:100%; font-size:13px;">
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Make / Brand:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $factoryAsset->make_brand ?: '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Model Number:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $factoryAsset->model_number ?: '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Serial Number:</td>
                    <td style="padding:8px 0; font-mono; font-weight:700; text-align:right;">{{ $factoryAsset->serial_number ?: '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Capacity / Tonnage:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $factoryAsset->tonnage_or_capacity ?: '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Connected Power:</td>
                    <td style="padding:8px 0; font-weight:700; font-family:monospace; text-align:right;">{{ $factoryAsset->power_rating_kw ? $factoryAsset->power_rating_kw . ' kW' : '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Plant Location:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;"><i class="fa fa-location-dot text-muted"></i> {{ $factoryAsset->plant_location ?: 'Shop Floor' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Assigned Operator:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $factoryAsset->assigned_operator ?: 'Unassigned' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Purchase Date:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $factoryAsset->purchase_date ? $factoryAsset->purchase_date->format('d M Y') : '—' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Machine Capital Cost:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">₹{{ number_format((float)$factoryAsset->purchase_cost, 2) }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Warranty Status:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:{{ $factoryAsset->is_under_warranty ? '#10b981' : '#64748b' }};">
                        {{ $factoryAsset->is_under_warranty ? 'Active (' . $factoryAsset->warranty_expiry->format('d M Y') . ')' : 'Expired' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:var(--text-muted);">Total Maintenance Spent:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:var(--primary);">₹{{ number_format((float)$totalMaintenanceCost, 2) }}</td>
                </tr>
            </table>

            @if($factoryAsset->notes)
            <div style="margin-top:16px; padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; font-size:12.5px; color:#475569;">
                <strong style="display:block; margin-bottom:4px; color:var(--text-dark);">Operating Guidelines:</strong>
                {{ $factoryAsset->notes }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right Card: Service & Maintenance Logs --}}
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-wrench text-warning"></i> Service, Breakdown &amp; Repair History ({{ $factoryAsset->maintenanceLogs->count() }})</h3>
        </div>
        <div class="card-body" style="padding:0;">
            @if($factoryAsset->maintenanceLogs->isEmpty())
            <div class="empty-state" style="padding:36px; text-align:center;">
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:12px;">No service or maintenance records logged yet.</p>
                <button class="btn btn-primary btn-sm" onclick="openModal('serviceModal')">Log First Service</button>
            </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date &amp; Type</th>
                            <th>Problem &amp; Action Taken</th>
                            <th>Technician &amp; Parts</th>
                            <th>Cost</th>
                            <th>Next Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factoryAsset->maintenanceLogs as $log)
                        <tr>
                            <td>
                                <div style="font-weight:700; color:var(--text-dark);">{{ $log->service_date->format('d M Y') }}</div>
                                <span class="badge badge-info" style="font-size:11px; margin-top:2px;">{{ $log->service_type }}</span>
                            </td>
                            <td>
                                @if($log->problem_reported)
                                    <div style="font-size:12px; font-weight:700; color:var(--danger, #ef4444);">Issue: {{ $log->problem_reported }}</div>
                                @endif
                                <div style="font-size:13px; color:var(--text-dark);">{{ $log->action_taken ?: 'Routine scheduled servicing' }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $log->technician_name ?: 'Internal Tech' }}</div>
                                @if($log->parts_replaced)
                                    <div style="font-size:11px; color:var(--text-muted);">Parts: {{ $log->parts_replaced }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:700; color:var(--text-dark);">₹{{ number_format((float)$log->cost, 2) }}</span>
                            </td>
                            <td>
                                <span style="font-size:12px; font-weight:600; color:var(--text-dark);">{{ $log->next_service_due ? $log->next_service_due->format('d M Y') : '—' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Log Service Modal -->
<div class="modal-overlay" id="serviceModal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-wrench text-warning"></i> Log Machine Service: {{ $factoryAsset->asset_code }}</h3>
            <button class="modal-close" onclick="closeModal('serviceModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('factory-assets.log-maintenance', $factoryAsset) }}">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Service Date</label>
                    <input type="date" name="service_date" value="{{ date('Y-m-d') }}" required class="form-control">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Service Type</label>
                    <select name="service_type" required class="form-control">
                        <option value="Preventive Maintenance (PM)">Preventive Maintenance (PM)</option>
                        <option value="Hydraulic Oil & Filter Change">Hydraulic Oil &amp; Filter Change</option>
                        <option value="Electrical & PLC Breakdown Repair">Electrical &amp; PLC Breakdown Repair</option>
                        <option value="Heater Band / Thermocouple Replacement">Heater Band / Thermocouple Replacement</option>
                        <option value="Pneumatic Valve / Cylinder Repair">Pneumatic Valve / Cylinder Repair</option>
                        <option value="Complete Plant Overhaul">Complete Plant Overhaul</option>
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label">Problem Reported (if breakdown)</label>
                    <input type="text" name="problem_reported" placeholder="e.g. Clamping cylinder pressure low" class="form-control">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label">Action Taken &amp; Work Performed</label>
                    <textarea name="action_taken" rows="2" placeholder="Replaced seal kit and tested..." class="form-control"></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Parts Replaced</label>
                        <input type="text" name="parts_replaced" placeholder="e.g. 1x Seal Kit, 2x Filter" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Service Cost (₹)</label>
                        <input type="number" step="0.01" name="cost" placeholder="0.00" class="form-control">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Technician Name / Vendor</label>
                        <input type="text" name="technician_name" placeholder="e.g. Vikram Solanki" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Machine Status After</label>
                        <select name="status_after_service" required class="form-control">
                            <option value="Operational">Operational (ચાલુ / Ready)</option>
                            <option value="Standby">Standby</option>
                            <option value="Breakdown">Still in Breakdown</option>
                            <option value="Maintenance / Overhaul">Under Maintenance</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Next Scheduled PM Due Date</label>
                    <input type="date" name="next_service_due" value="{{ date('Y-m-d', strtotime('+3 months')) }}" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('serviceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
