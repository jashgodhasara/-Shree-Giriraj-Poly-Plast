@extends('layouts.app')
@section('title', 'Stock Adjustments & Physical Reconciliation')
@section('page-title', 'Stock Adjustments')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-sliders text-primary"></i> Physical Stock Adjustments ({{ $adjustments->total() }})</h3>
        <button class="btn btn-primary btn-sm" onclick="openAddAdjustmentModal()">
            <i class="fa fa-plus"></i> New Adjustment
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($adjustments->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-sliders"></i></div>
            <p>No stock adjustments recorded.</p>
            <small class="text-muted">Click "+ New Adjustment" when physical audit differs from system inventory.</small>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Adjustment #</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th style="text-align:right;">System Stock</th>
                        <th style="text-align:right;">Physical Count</th>
                        <th style="text-align:right;">Discrepancy</th>
                        <th>Reason</th>
                        <th>User</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($adjustments as $adj)
                <tr>
                    <td><code>{{ $adj->adjustment_number }}</code></td>
                    <td>{{ $adj->adjustment_date ? $adj->adjustment_date->format('d-m-Y') : '—' }}</td>
                    <td>
                        @if($adj->product)
                            <a href="{{ route('products.show', $adj->product) }}" class="fw-bold text-primary" style="text-decoration:none;">
                                {{ $adj->product->name }}
                            </a>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $adj->product->sku }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $adj->warehouse->name ?? 'Main Warehouse' }}</td>
                    <td style="text-align:right;">{{ number_format($adj->system_stock, 2) }}</td>
                    <td style="text-align:right;" class="fw-bold">{{ number_format($adj->physical_stock, 2) }}</td>
                    <td style="text-align:right;" class="fw-bold {{ $adj->difference_quantity >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $adj->difference_quantity >= 0 ? '+' : '' }}{{ number_format($adj->difference_quantity, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $adj->difference_quantity >= 0 ? 'badge-success' : 'badge-danger' }}" style="font-size:10.5px;">
                            {{ $adj->reason }}
                        </span>
                    </td>
                    <td style="font-size:12px;">{{ $adj->creator->name ?? 'Admin' }}</td>
                    <td style="font-size:11.5px; color:var(--text-muted); max-width:180px;">{{ $adj->remarks ?: '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px;">
            {{ $adjustments->links() }}
        </div>
        @endif
    </div>
</div>

<!-- New Adjustment Modal -->
<div class="modal-overlay" id="addAdjustmentModal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3><i class="fa fa-sliders text-primary"></i> Record Stock Adjustment</h3>
            <button class="modal-close" onclick="closeModal('addAdjustmentModal')">✕</button>
        </div>
        <form id="addAdjustmentForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Product *</label>
                    <select name="product_id" id="adj_product_id" required onchange="onProductSelectForAdj(this)">
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-stock="{{ $p->stock_quantity }}" data-unit="{{ $p->unit ?: 'PCS' }}">
                                {{ $p->name }} ({{ $p->sku }}) — Current: {{ $p->stock_quantity }} {{ $p->unit ?: 'PCS' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Current System Stock</label>
                        <input type="text" id="adj_system_stock" readonly style="background:#f1f5f9; font-weight:700;">
                    </div>
                    <div class="form-group">
                        <label>Actual Physical Count *</label>
                        <input type="number" step="0.01" name="physical_stock" id="adj_physical_stock" required placeholder="Counted quantity" oninput="calcAdjDiff()">
                    </div>
                </div>

                <div class="form-group" style="padding:10px; background:#fafbff; border-radius:6px; border:1px solid var(--border);">
                    <label style="font-size:11px; margin-bottom:2px;">Discrepancy / Adjustment Difference</label>
                    <div id="adj_diff_display" style="font-size:16px; font-weight:800; color:var(--primary);">0.00</div>
                </div>

                <div class="form-group">
                    <label>Reason for Adjustment *</label>
                    <select name="reason" required>
                        <option value="Physical Count Discrepancy">Physical Count Discrepancy</option>
                        <option value="Damaged Goods / Scrap">Damaged Goods / Scrap</option>
                        <option value="Defective Mold Run">Defective Mold Run</option>
                        <option value="Sample / Testing Usage">Sample / Testing Usage</option>
                        <option value="Pilferage / Missing">Pilferage / Missing</option>
                        <option value="Opening Balance Correction">Opening Balance Correction</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Remarks / Notes</label>
                    <textarea name="remarks" rows="2" placeholder="Auditor notes, batch details..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addAdjustmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Apply Adjustment</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentSysStock = 0;

function openAddAdjustmentModal() {
    document.getElementById('addAdjustmentForm').reset();
    document.getElementById('adj_system_stock').value = '';
    document.getElementById('adj_diff_display').innerText = '0.00';
    currentSysStock = 0;
    openModal('addAdjustmentModal');
}

function onProductSelectForAdj(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        currentSysStock = parseFloat(opt.dataset.stock || 0);
        document.getElementById('adj_system_stock').value = `${currentSysStock} ${opt.dataset.unit || 'PCS'}`;
    } else {
        currentSysStock = 0;
        document.getElementById('adj_system_stock').value = '';
    }
    calcAdjDiff();
}

function calcAdjDiff() {
    const physicalVal = parseFloat(document.getElementById('adj_physical_stock').value || 0);
    const diff = physicalVal - currentSysStock;
    const disp = document.getElementById('adj_diff_display');
    if (diff > 0) {
        disp.style.color = '#10b981';
        disp.innerText = `+${diff.toFixed(2)} (Stock Increase)`;
    } else if (diff < 0) {
        disp.style.color = '#ef4444';
        disp.innerText = `${diff.toFixed(2)} (Stock Decrease)`;
    } else {
        disp.style.color = 'var(--primary)';
        disp.innerText = `0.00 (No Change)`;
    }
}

document.getElementById('addAdjustmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('inventory.adjustments.store') }}', 'POST');
});
</script>
@endsection
