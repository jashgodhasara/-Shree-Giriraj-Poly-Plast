@extends('layouts.app')
@section('title', 'Production Logs')
@section('page-title', 'Processing / Production Logs')

@section('content')

{{-- Date Filter --}}
@include('partials.date-filter', ['action' => route('production.index')])

{{-- Summary Cards --}}
@if($preset || $dateFrom)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:18px;">
    <div class="stat-card s-indigo"><div class="stat-top"><div class="stat-icon"><i class="fa fa-list-check"></i></div></div>
        <div class="stat-label">Log Entries</div><div class="stat-value">{{ $totalCount }}</div></div>
    <div class="stat-card s-violet"><div class="stat-top"><div class="stat-icon"><i class="fa fa-weight-scale"></i></div></div>
        <div class="stat-label">Raw Used (Kg)</div><div class="stat-value">{{ number_format($totalRawKg,1) }}</div></div>
    <div class="stat-card s-emerald"><div class="stat-top"><div class="stat-icon"><i class="fa fa-boxes-stacked"></i></div></div>
        <div class="stat-label">Output (Pcs)</div><div class="stat-value">{{ number_format($totalPieces) }}</div></div>
    <div class="stat-card s-red"><div class="stat-top"><div class="stat-icon"><i class="fa fa-trash-can"></i></div></div>
        <div class="stat-label">Salvage (Kg)</div><div class="stat-value">{{ number_format($totalSalvage,1) }}</div></div>
    @if($totalRawKg > 0)
    <div class="stat-card s-amber"><div class="stat-top"><div class="stat-icon"><i class="fa fa-percent"></i></div></div>
        <div class="stat-label">Avg Salvage %</div>
        <div class="stat-value">{{ number_format(($totalSalvage / $totalRawKg) * 100, 1) }}%</div></div>
    @endif
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
            <div class="empty-icon"><i class="fa fa-industry"></i></div>
            <p>No production logs yet.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th><th>Raw Material</th><th>Used (Kg)</th>
                        <th>Additive</th><th>Add. (Kg)</th>
                        <th>Final Product</th><th>Output (Pcs)</th>
                        <th>Salvage %</th><th>Salvage (Kg)</th><th>Yield (Kg)</th>
                        <th>Notes</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                @php
                    $pct = (float)($log->salvage_pct ?? 2);
                    $badgeColor = $pct <= 2 ? 'badge-green' : ($pct <= 3.5 ? 'badge-orange' : 'badge-red');
                @endphp
                <tr>
                    <td>{{ $log->date->format('d M Y') }}</td>
                    <td class="fw-600">{{ $log->rawMaterial->name ?? '—' }}</td>
                    <td>{{ number_format($log->raw_material_used_kg, 2) }}</td>
                    <td>{{ $log->additive->name ?? '—' }}</td>
                    <td>{{ $log->additive_used_kg ? number_format($log->additive_used_kg, 2) : '—' }}</td>
                    <td class="fw-bold">{{ $log->finalProduct->name ?? '—' }}</td>
                    <td class="fw-bold" style="color:var(--success)">{{ number_format($log->final_product_qty_pcs) }}</td>
                    <td><span class="badge {{ $badgeColor }}">{{ number_format($pct, 1) }}%</span></td>
                    <td style="color:var(--danger)">{{ number_format($log->salvage_qty_kg, 3) }}</td>
                    <td style="color:var(--primary);font-weight:700">
                        {{ $log->effective_yield_kg ? number_format($log->effective_yield_kg, 3) : '—' }}
                    </td>
                    <td style="font-size:12px;color:var(--text-muted)">{{ Str::limit($log->notes, 25) ?? '—' }}</td>
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

<!-- Add Production Modal -->
<div class="modal-overlay" id="addProductionModal">
    <div class="modal" style="max-width:620px">
        <div class="modal-header">
            <h3><i class="fa fa-industry"></i> Log Production Run</h3>
            <button class="modal-close" onclick="closeModal('addProductionModal')">✕</button>
        </div>
        <form id="addProductionForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Production Date *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}">
                </div>

                {{-- Raw Material --}}
                <div style="background:#f8faff;border:1px solid #e0e7ff;border-radius:8px;padding:14px;margin-bottom:12px;">
                    <div style="font-size:12px;font-weight:700;color:var(--primary);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-arrow-down"></i> Raw Material Input
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group" style="margin-bottom:0">
                            <label>Raw Material *</label>
                            <select name="raw_material_id" id="rm_select" required onchange="onRmChange()">
                                <option value="">Select material</option>
                                @foreach($rawMaterials as $rm)
                                <option value="{{ $rm->id }}" data-stock="{{ $rm->stock_quantity }}" data-unit="{{ $rm->unit }}">
                                    {{ $rm->name }} (Stock: {{ number_format($rm->stock_quantity, 1) }} {{ $rm->unit }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Used (Kg) *</label>
                            <input type="number" name="raw_material_used_kg" id="rm_used_kg"
                                step="0.01" min="0.01" required oninput="calcSalvage()">
                        </div>
                    </div>
                    <div id="rm_stock_info" style="margin-top:6px;font-size:12px;color:var(--text-muted);display:none">
                        <i class="fa fa-circle-info"></i> Available stock: <strong id="rm_stock_val"></strong>
                    </div>
                </div>

                {{-- Additive --}}
                <div style="background:#fffbf0;border:1px solid #fde68a;border-radius:8px;padding:14px;margin-bottom:12px;">
                    <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-flask"></i> Additive (Optional)
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group" style="margin-bottom:0">
                            <label>Additive</label>
                            <select name="additive_id">
                                <option value="">None</option>
                                @foreach($additives as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} (Stock: {{ number_format($a->stock_quantity, 1) }} {{ $a->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Additive Used (Kg)</label>
                            <input type="number" name="additive_used_kg" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                {{-- Salvage % Slider --}}
                <div style="background:#fff5f5;border:1px solid #fecaca;border-radius:8px;padding:14px;margin-bottom:12px;">
                    <div style="font-size:12px;font-weight:700;color:#991b1b;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-trash-can"></i> Salvage / Scrap
                    </div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:8px;">
                        <div style="flex:1">
                            <label style="display:flex;justify-content:space-between;align-items:center">
                                <span>Salvage %</span>
                                <span id="salvage_pct_display" style="font-size:16px;font-weight:800;color:#ef4444">2.0%</span>
                            </label>
                            <input type="range" name="salvage_pct" id="salvage_pct_slider"
                                min="0" max="10" step="0.5" value="2"
                                style="width:100%;height:6px;accent-color:#ef4444;cursor:pointer;"
                                oninput="onSalvageSlider()">
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:#94a3b8;margin-top:2px">
                                <span>0%</span><span style="color:#16a34a;font-weight:600">2% (min)</span>
                                <span style="color:#f59e0b;font-weight:600">5% (max)</span><span>10%</span>
                            </div>
                        </div>
                    </div>
                    {{-- Auto-calculated results --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                        <div style="background:#fff;border:1px solid #fecaca;border-radius:6px;padding:8px 12px;">
                            <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase">Salvage Kg</div>
                            <div id="salvage_kg_display" style="font-size:18px;font-weight:800;color:#ef4444">0.000 Kg</div>
                        </div>
                        <div style="background:#fff;border:1px solid #bbf7d0;border-radius:6px;padding:8px 12px;">
                            <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase">Net Yield (Kg)</div>
                            <div id="yield_kg_display" style="font-size:18px;font-weight:800;color:#16a34a">0.000 Kg</div>
                        </div>
                    </div>
                </div>

                {{-- Output --}}
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;margin-bottom:12px;">
                    <div style="font-size:12px;font-weight:700;color:#065f46;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-arrow-up"></i> Output
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group" style="margin-bottom:0">
                            <label>Final Product *</label>
                            <select name="final_product_id" required>
                                <option value="">Select product</option>
                                @foreach($finalProducts as $fp)
                                <option value="{{ $fp->id }}">{{ $fp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Output Qty (Pcs) *</label>
                            <input type="number" name="final_product_qty_pcs" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="Optional remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProductionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Production Log
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function onRmChange() {
    const sel = document.getElementById('rm_select');
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('rm_stock_info');
    const val  = document.getElementById('rm_stock_val');
    if (opt.value) {
        val.textContent = opt.dataset.stock + ' ' + opt.dataset.unit;
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }
    calcSalvage();
}

function onSalvageSlider() {
    const pct = parseFloat(document.getElementById('salvage_pct_slider').value);
    document.getElementById('salvage_pct_display').textContent = pct.toFixed(1) + '%';
    calcSalvage();
}

function calcSalvage() {
    const rawKg = parseFloat(document.getElementById('rm_used_kg').value) || 0;
    const pct   = parseFloat(document.getElementById('salvage_pct_slider').value) || 2;
    const salvKg = (rawKg * pct / 100);
    const yieldKg = rawKg - salvKg;
    document.getElementById('salvage_kg_display').textContent = salvKg.toFixed(3) + ' Kg';
    document.getElementById('yield_kg_display').textContent   = Math.max(0, yieldKg).toFixed(3) + ' Kg';
}

document.getElementById('addProductionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('production.store') }}', 'POST', function(res) {
        showToast(
            `Saved! Salvage: ${res.salvage_pct}% = ${res.salvage_kg} Kg | Yield: ${res.yield_kg} Kg`,
            'success'
        );
        setTimeout(() => location.reload(), 1500);
    });
});
</script>
@endsection
