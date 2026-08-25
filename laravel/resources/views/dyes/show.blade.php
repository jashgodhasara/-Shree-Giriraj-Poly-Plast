@extends('layouts.app')
@section('title', $dye->name . ' — Dye Specifications & Maintenance')
@section('page-title', 'Dye Details: ' . $dye->name)

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <a href="{{ route('dyes.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa fa-arrow-left"></i> Back to Dyes Inventory
        </a>
        <h2 style="font-size:22px; font-weight:800; color:var(--text-dark, #0f172a); margin:0; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-shapes text-primary"></i> {{ $dye->name }}
        </h2>
        <div style="display:flex; align-items:center; gap:8px; margin-top:6px;">
            <code style="background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:4px; font-weight:700; font-size:12px;">{{ $dye->code }}</code>
            <span class="badge badge-purple">{{ $dye->mould_type }}</span>
            <span class="badge badge-indigo">{{ $dye->cavities }} {{ Str::plural('Cavity', $dye->cavities) }}</span>
            @if($dye->status === 'Mounted on Machine')
                <span class="badge badge-info"><i class="fa fa-cog fa-spin"></i> On Machine</span>
            @elseif($dye->status === 'Ready / In Storage')
                <span class="badge badge-success"><i class="fa fa-check"></i> Ready</span>
            @elseif($dye->status === 'Under Maintenance')
                <span class="badge badge-warning"><i class="fa fa-wrench"></i> Under Maintenance</span>
            @endif
        </div>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openModal('maintenanceModal')">
            <i class="fa-solid fa-screwdriver-wrench"></i> Log Service / Maintenance
        </button>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:24px; margin-bottom:24px;">
    {{-- Left Card: Specs --}}
    <div class="card">
        <div class="card-header">
            <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-circle-info text-primary"></i> Tooling Specifications</h3>
        </div>
        <div class="card-body">
            @if($dye->image)
            <div style="margin-bottom:16px; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; max-height:220px; display:flex; align-items:center; justify-content:center; background:#f8fafc;">
                <img src="{{ $dye->image_url }}" alt="{{ $dye->name }}" style="max-height:200px; width:100%; object-fit:contain;">
            </div>
            @endif

            <table style="width:100%; font-size:13px;">
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Ownership:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">
                        {{ $dye->ownership_type }}
                        @if($dye->customer)
                            ({{ $dye->customer->name }})
                        @endif
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Tool Room Location:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;"><i class="fa fa-location-dot text-muted"></i> {{ $dye->rack_location ?: 'Not Assigned' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Compatible Machine:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $dye->compatible_machines ?: 'Universal' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Total Shots Run:</td>
                    <td style="padding:8px 0; font-weight:700; font-family:monospace; text-align:right;">{{ number_format($dye->total_shots_count) }} Shots</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">PM Service Cycle:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">Every {{ number_format($dye->service_interval_shots) }} Shots</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Last Serviced:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">{{ $dye->last_serviced_date ? $dye->last_serviced_date->format('d M Y') : 'Never' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Next PM Due:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:{{ $dye->is_maintenance_due ? '#ef4444' : '#0f172a' }};">
                        {{ $dye->next_service_due_date ? $dye->next_service_due_date->format('d M Y') : 'Not Set' }}
                    </td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:var(--text-muted);">Asset / Tool Cost:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right;">₹{{ number_format((float)$dye->purchase_cost, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:var(--text-muted);">Total Maintenance Spent:</td>
                    <td style="padding:8px 0; font-weight:700; text-align:right; color:var(--primary);">₹{{ number_format((float)$totalMaintenanceCost, 2) }}</td>
                </tr>
            </table>

            @if($dye->notes)
            <div style="margin-top:16px; padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; font-size:12.5px; color:#475569;">
                <strong style="display:block; margin-bottom:4px; color:var(--text-dark);">Technical Notes:</strong>
                {{ $dye->notes }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right Card: Service Logs --}}
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-wrench text-warning"></i> Tool Room Servicing &amp; Repair History ({{ $dye->maintenanceLogs->count() }})</h3>
        </div>
        <div class="card-body" style="padding:0;">
            @if($dye->maintenanceLogs->isEmpty())
            <div class="empty-state" style="padding:36px; text-align:center;">
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:12px;">No maintenance service logs recorded yet.</p>
                <button class="btn btn-primary btn-sm" onclick="openModal('maintenanceModal')">Log First Maintenance</button>
            </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date &amp; Service Type</th>
                            <th>Work Description</th>
                            <th>Technician</th>
                            <th>Cost</th>
                            <th>Next Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dye->maintenanceLogs as $log)
                        <tr>
                            <td>
                                <div style="font-weight:700; color:var(--text-dark);">{{ $log->maintenance_date->format('d M Y') }}</div>
                                <span class="badge badge-warning" style="font-size:11px; margin-top:2px;">{{ $log->maintenance_type }}</span>
                            </td>
                            <td>
                                <div style="font-size:13px; color:var(--text-dark);">{{ $log->work_description ?: 'Routine Maintenance' }}</div>
                                <div style="font-size:11px; color:var(--text-muted); font-family:monospace;">Recorded at {{ number_format($log->shots_at_service) }} shots</div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $log->performed_by ?: 'In-house Tool Room' }}</div>
                                @if($log->vendor_name)
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $log->vendor_name }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:700; color:var(--text-dark);">₹{{ number_format((float)$log->cost, 2) }}</span>
                            </td>
                            <td>
                                <span style="font-size:12px; font-weight:600; color:var(--text-dark);">{{ $log->next_due_date ? $log->next_due_date->format('d M Y') : '—' }}</span>
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
<div class="modal-overlay" id="maintenanceModal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-wrench text-warning"></i> Log Maintenance: {{ $dye->code }}</h3>
            <button class="modal-close" onclick="closeModal('maintenanceModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('dyes.log-maintenance', $dye) }}">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Maintenance Date</label>
                    <input type="date" name="maintenance_date" value="{{ date('Y-m-d') }}" required class="form-control">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label required">Maintenance Type</label>
                    <select name="maintenance_type" required class="form-control">
                        <option value="Preventive Cleaning & Inspection">Preventive Cleaning &amp; Inspection</option>
                        <option value="Cavity Polishing / Deburring">Cavity Polishing / Deburring</option>
                        <option value="Ejector Pin / Spring Replacement">Ejector Pin / Spring Replacement</option>
                        <option value="Cooling Channel Flush / Descaling">Cooling Channel Flush / Descaling</option>
                        <option value="Breakdown / Parting Line Repair">Breakdown / Parting Line Repair</option>
                        <option value="Complete Overhaul">Complete Overhaul</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Shots at Service</label>
                        <input type="number" name="shots_at_service" value="{{ $dye->total_shots_count }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Service Cost (₹)</label>
                        <input type="number" step="0.01" name="cost" placeholder="0.00" class="form-control">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label">Performed By</label>
                        <input type="text" name="performed_by" placeholder="e.g. Tool Room Staff" class="form-control">
                    </div>
                    <div>
                        <label class="form-label required">Status After Service</label>
                        <select name="status_after" required class="form-control">
                            <option value="Ready / In Storage">Ready / In Storage</option>
                            <option value="Mounted on Machine">Mounted on Machine</option>
                            <option value="Under Maintenance">Under Maintenance</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label">Work Performed Details</label>
                    <textarea name="work_description" rows="2" placeholder="Describe work done..." class="form-control"></textarea>
                </div>
                <div>
                    <label class="form-label">Next Scheduled Service Due</label>
                    <input type="date" name="next_due_date" value="{{ date('Y-m-d', strtotime('+3 months')) }}" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('maintenanceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
