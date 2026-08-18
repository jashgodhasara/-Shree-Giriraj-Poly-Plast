@extends('layouts.app')
@section('title', 'Production Logs')
@section('page-title', 'Processing / Production Logs')

@section('content')

{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('production.index')])

{{-- Summary Cards --}}
@if($preset || $dateFrom)
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:18px;">
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Log Entries</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--primary);">{{ number_format($totalCount) }}</div>
    </div>
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Raw Material Used</div>
        <div style="font-size:1.4rem;font-weight:800;color:#8b5cf6;">{{ number_format($totalRawKg, 1) }} <span style="font-size:0.9rem;font-weight:500">Kg</span></div>
    </div>
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Output Produced</div>
        <div style="font-size:1.4rem;font-weight:800;color:#10b981;">{{ number_format($totalPieces) }} <span style="font-size:0.9rem;font-weight:500">Pcs</span></div>
    </div>
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Salvage / Scrap</div>
        <div style="font-size:1.4rem;font-weight:800;color:#ef4444;">{{ number_format($totalSalvage, 1) }} <span style="font-size:0.9rem;font-weight:500">Kg</span></div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-industry"></i> Production Logs</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addProductionModal')">
            <i class="fa fa-plus"></i> Log Production
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($logs->isEmpty())
        <div class="empty-state">
            <i class="fa fa-industry"></i>
            <p>No production logs{{ $preset ? ' found for this period' : ' yet' }}.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Raw Material</th><th>Used (Kg)</th><th>Additive</th><th>Additive (Kg)</th><th>Final Product</th><th>Output (Pcs)</th><th>Salvage (Kg)</th><th>Notes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->date->format('d M Y') }}</td>
                    <td>{{ $log->rawMaterial->name ?? '—' }}</td>
                    <td>{{ $log->raw_material_used_kg }}</td>
                    <td>{{ $log->additive->name ?? '—' }}</td>
                    <td>{{ $log->additive_used_kg ?? '—' }}</td>
                    <td class="fw-bold">{{ $log->finalProduct->name ?? '—' }}</td>
                    <td class="fw-bold">{{ $log->final_product_qty_pcs }}</td>
                    <td>{{ $log->salvage_qty_kg ?? 0 }}</td>
                    <td>{{ Str::limit($log->notes, 30) ?? '—' }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-icon"
                            onclick="deleteRecord('{{ route('production.destroy', $log) }}', 'production log')">
                            <i class="fa fa-trash"></i>
                        </button>
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
<div class="modal-overlay" id="addProductionModal">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3>Log Production Run</h3>
            <button class="modal-close" onclick="closeModal('addProductionModal')">✕</button>
        </div>
        <form id="addProductionForm">
            <div class="modal-body">
                <div class="form-group"><label>Production Date *</label><input type="date" name="date" required value="{{ date('Y-m-d') }}"></div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Raw Material *</label>
                        <select name="raw_material_id" required>
                            <option value="">Select raw material</option>
                            @foreach($rawMaterials as $rm)
                            <option value="{{ $rm->id }}">{{ $rm->name }} (Stock: {{ $rm->stock_quantity }} {{ $rm->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Used (Kg) *</label><input type="number" name="raw_material_used_kg" step="0.01" min="0.01" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Additive (Optional)</label>
                        <select name="additive_id">
                            <option value="">None</option>
                            @foreach($additives as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} (Stock: {{ $a->stock_quantity }} {{ $a->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Additive Used (Kg)</label><input type="number" name="additive_used_kg" step="0.01" min="0"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Final Product *</label>
                        <select name="final_product_id" required>
                            <option value="">Select final product</option>
                            @foreach($finalProducts as $fp)
                            <option value="{{ $fp->id }}">{{ $fp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Output (Pcs) *</label><input type="number" name="final_product_qty_pcs" min="1" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Salvage/Scrap (Kg)</label><input type="number" name="salvage_qty_kg" step="0.01" value="0"></div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProductionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Log</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('addProductionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('production.store') }}', 'POST');
});
</script>
@endsection
