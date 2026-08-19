@extends('layouts.app')
@section('title', 'Edit Job Work ' . $jobWorkOrder->job_work_number)
@section('page-title', 'Edit Job Work Order')

@section('content')
<style>
    .jw-table th { background: #f8fafc; font-size: 12px; font-weight: 700; color: #475569; padding: 10px 8px; }
    .jw-table td { padding: 8px 6px; vertical-align: top; }
    .jw-input { width: 100%; padding: 7px 8px; font-size: 12.5px; border: 1px solid var(--border); border-radius: 6px; }
    .jw-readonly { background: #f1f5f9; color: #1e293b; font-weight: 700; border-color: #cbd5e1; }
    .jw-unit-select { padding: 7px 4px; font-size: 12px; border: 1px solid var(--border); border-radius: 6px; background: #fff; }
</style>

<div class="d-flex justify-between align-center mb-3">
    <div>
        <h2 style="font-size: 19px; font-weight: 700; color: var(--text);">
            <i class="fa fa-pen-to-square text-primary"></i> Edit Job Work {{ $jobWorkOrder->job_work_number }}
        </h2>
        <p class="text-muted" style="font-size: 12.5px;">Update received weight, product piece weight snapshots, wastage percentage or pricing</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('jobworks.show', $jobWorkOrder) }}" class="btn btn-outline btn-sm">
            <i class="fa fa-eye"></i> View Order
        </a>
        <a href="{{ route('jobworks.index') }}" class="btn btn-outline btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<form id="editJobWorkForm" method="POST" action="{{ route('jobworks.update', $jobWorkOrder) }}">
    @csrf
    @method('PUT')

    {{-- Header Information Card --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-file-invoice"></i> Order Information</h3>
            <span class="badge badge-purple"><i class="fa fa-bolt"></i> Auto Calculation Active</span>
        </div>
        <div class="card-body" style="padding: 16px;">
            <div class="form-row cols-4">
                <div class="form-group">
                    <label>Job Work No. *</label>
                    <input type="text" name="job_work_number" value="{{ $jobWorkOrder->job_work_number }}" required class="jw-readonly" readonly>
                </div>

                <div class="form-group">
                    <label>Client / Party *</label>
                    <select name="client_id" id="client_select" required class="jw-input">
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ $jobWorkOrder->client_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->company_name ? "({$c->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Order Date *</label>
                    <input type="date" name="order_date" value="{{ $jobWorkOrder->order_date->format('Y-m-d') }}" required class="jw-input">
                </div>

                <div class="form-group">
                    <label>Due / Delivery Date</label>
                    <input type="date" name="due_date" value="{{ $jobWorkOrder->due_date ? $jobWorkOrder->due_date->format('Y-m-d') : '' }}" class="jw-input">
                </div>
            </div>

            <div class="form-row cols-3" style="margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Client Ref / Challan No.</label>
                    <input type="text" name="reference_number" value="{{ $jobWorkOrder->reference_number }}" class="jw-input">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Status *</label>
                    <select name="status" class="jw-input">
                        @foreach(['Draft', 'Material Received', 'In Production', 'Partially Completed', 'Completed', 'Delivered', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ $jobWorkOrder->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Quantity Rounding Mode *</label>
                    <select name="rounding_method" id="rounding_method" class="jw-input" onchange="recalculateAll()">
                        <option value="floor" {{ $jobWorkOrder->rounding_method === 'floor' ? 'selected' : '' }}>Floor (Truncate Decimals)</option>
                        <option value="round" {{ $jobWorkOrder->rounding_method === 'round' ? 'selected' : '' }}>Round to Nearest Integer</option>
                        <option value="ceil" {{ $jobWorkOrder->rounding_method === 'ceil' ? 'selected' : '' }}>Ceiling (Round Up)</option>
                        <option value="decimal" {{ $jobWorkOrder->rounding_method === 'decimal' ? 'selected' : '' }}>Allow 4 Decimals</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Material & Products Entry Table --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-between align-center flex-wrap gap-2">
            <h3><i class="fa fa-cubes-stacked"></i> Material Intake &amp; Product Piece Calculation</h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="addProductRow()">
                <i class="fa fa-plus"></i> Add Another Product
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrap">
                <table class="jw-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Product *</th>
                            <th style="width: 14%;">Received Weight *</th>
                            <th style="width: 14%;">Product Weight / Piece *</th>
                            <th style="width: 11%;">Gross Pieces</th>
                            <th style="width: 13%;">Wastage (%)</th>
                            <th style="width: 11%; color:#059669;">Net Pieces</th>
                            <th style="width: 12%;">Rate &amp; Pricing</th>
                            <th style="width: 10%;">Amount (₹)</th>
                            <th style="width: 3%; text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        {{-- Injected via JS from existing items --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Summary & Financials --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card" style="margin: 0; background: #f8fafc; border: 1.5px solid #e2e8f0;">
            <div class="card-header" style="background: transparent;">
                <h3 style="font-size: 14px;"><i class="fa fa-calculator text-primary"></i> Production Material Summary</h3>
            </div>
            <div class="card-body" style="padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span class="text-muted">Total Received Weight:</span>
                    <strong id="summary_received_weight" style="font-size: 14px; color: var(--primary);">0.00 KG</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span class="text-muted">Total Gross Pieces:</span>
                    <strong id="summary_gross_pieces" style="font-size: 14px;">0 PCS</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span class="text-muted">Total Wastage Allowed:</span>
                    <strong id="summary_wastage_pieces" style="font-size: 14px; color: #ea580c;">0 PCS</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px dashed #cbd5e1; font-size: 14px;">
                    <strong style="color: #059669;">Total Net Finished Pieces:</strong>
                    <strong id="summary_net_pieces" style="font-size: 16px; color: #059669;">0 PCS</strong>
                </div>
            </div>
        </div>

        <div class="card" style="margin: 0;">
            <div class="card-header">
                <h3 style="font-size: 14px;"><i class="fa fa-coins text-primary"></i> Billing &amp; Settlement</h3>
            </div>
            <div class="card-body" style="padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span class="text-muted">Job Work Subtotal:</span>
                    <strong id="summary_subtotal">₹0.00</strong>
                </div>
                <div class="form-row cols-2" style="margin-bottom: 8px;">
                    <div>
                        <label style="font-size: 11px;">Additional / Freight (₹)</label>
                        <input type="number" name="additional_charges" id="inp_additional" step="0.01" min="0" value="{{ $jobWorkOrder->additional_charges }}" class="jw-input" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label style="font-size: 11px;">Discount (₹)</label>
                        <input type="number" name="discount" id="inp_discount" step="0.01" min="0" value="{{ $jobWorkOrder->discount }}" class="jw-input" oninput="recalculateAll()">
                    </div>
                </div>
                <div class="form-row cols-2" style="margin-bottom: 8px;">
                    <div>
                        <label style="font-size: 11px;">Tax / GST (₹)</label>
                        <input type="number" name="tax" id="inp_tax" step="0.01" min="0" value="{{ $jobWorkOrder->tax }}" class="jw-input" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label style="font-size: 11px;">Advance Paid (₹)</label>
                        <input type="number" name="paid_amount" id="inp_paid" step="0.01" min="0" value="{{ $jobWorkOrder->paid_amount }}" class="jw-input" oninput="recalculateAll()">
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid #e2e8f0; font-size: 15px;">
                    <strong style="color: var(--text);">Grand Total:</strong>
                    <strong id="summary_grand_total" style="color: var(--primary); font-size: 17px;">₹0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 13px;">
                    <span class="text-muted">Balance Receivable:</span>
                    <strong id="summary_balance" style="color: #ef4444;">₹0.00</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding: 14px 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Job Work Notes / Delivery Instructions</label>
                <textarea name="remarks" rows="2" class="jw-input">{{ $jobWorkOrder->remarks }}</textarea>
            </div>
        </div>
    </div>

    <div class="d-flex justify-between align-center mb-5">
        <a href="{{ route('jobworks.show', $jobWorkOrder) }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg" style="padding: 10px 28px; font-size: 15px;">
            <i class="fa fa-save"></i> Update Job Work Order
        </button>
    </div>
</form>

@php
    $productsJson = $products->map(function($p) {
        return [
            'id'                  => $p->id,
            'name'                => $p->name,
            'sku'                 => $p->sku,
            'weight_per_piece'    => (float) ($p->weight_per_piece ?: 10),
            'weight_unit'         => $p->weight_unit ?: 'Gram',
            'wastage_percentage'  => (float) ($p->wastage_percentage ?: 0),
            'fixed_wastage'       => (float) ($p->fixed_wastage ?: 0),
            'price'               => (float) ($p->price ?: 0),
        ];
    });

    $existingItemsJson = $jobWorkOrder->items->map(function($i) {
        return [
            'product_id'          => $i->product_id,
            'received_weight'     => (float) $i->received_weight,
            'received_weight_unit'=> $i->received_weight_unit,
            'product_weight'      => (float) $i->product_weight,
            'product_weight_unit' => $i->product_weight_unit,
            'wastage_percentage'  => (float) $i->wastage_percentage,
            'rate'                => (float) $i->rate,
            'rate_type'           => $i->rate_type,
            'remarks'             => $i->remarks,
        ];
    });
@endphp

@endsection

@section('scripts')
<script>
const productsCatalog = @json($productsJson);
const existingItems   = @json($existingItemsJson);
let rowIndex = 0;

function convertToGrams(weight, unit) {
    weight = parseFloat(weight) || 0;
    unit = (unit || 'Gram').toUpperCase();
    if (unit === 'KG' || unit === 'KILOGRAM') return weight * 1000;
    if (unit === 'TON' || unit === 'METRIC TON') return weight * 1000000;
    if (unit === 'MILLIGRAM' || unit === 'MG') return weight / 1000;
    return weight;
}

function applyRounding(val, method) {
    val = parseFloat(val) || 0;
    if (method === 'round') return Math.round(val);
    if (method === 'ceil') return Math.ceil(val);
    if (method === 'decimal') return parseFloat(val.toFixed(4));
    return Math.floor(val);
}

function addProductRow(initialData = null) {
    const tbody = document.getElementById('itemsTableBody');
    const idx = rowIndex++;

    let productOptions = '<option value="">-- Choose Product --</option>';
    productsCatalog.forEach(p => {
        const sel = (initialData && initialData.product_id == p.id) ? 'selected' : '';
        productOptions += `<option value="${p.id}" ${sel}>${p.name} ${p.sku ? '('+p.sku+')' : ''} [${p.weight_per_piece} ${p.weight_unit}]</option>`;
    });

    const tr = document.createElement('tr');
    tr.id = `row_${idx}`;
    tr.innerHTML = `
        <td>
            <select name="items[${idx}][product_id]" class="jw-input product-picker" required onchange="onProductSelect(${idx})">
                ${productOptions}
            </select>
            <input type="text" name="items[${idx}][remarks]" value="${initialData?.remarks || ''}" placeholder="Item remarks / batch notes..." class="jw-input" style="margin-top: 4px; font-size: 11px;">
        </td>
        <td>
            <div style="display:flex; gap:4px;">
                <input type="number" name="items[${idx}][received_weight]" id="rw_${idx}" step="0.0001" min="0.0001" value="${initialData?.received_weight || 500}" required class="jw-input" oninput="calculateRow(${idx})">
                <select name="items[${idx}][received_weight_unit]" id="rwu_${idx}" class="jw-unit-select" onchange="calculateRow(${idx})">
                    <option value="KG" ${(!initialData || initialData.received_weight_unit === 'KG') ? 'selected' : ''}>KG</option>
                    <option value="Gram" ${(initialData && initialData.received_weight_unit === 'Gram') ? 'selected' : ''}>Gram</option>
                    <option value="Ton" ${(initialData && initialData.received_weight_unit === 'Ton') ? 'selected' : ''}>Ton</option>
                    <option value="Milligram" ${(initialData && initialData.received_weight_unit === 'Milligram') ? 'selected' : ''}>mg</option>
                </select>
            </div>
            <div id="grams_hint_${idx}" style="font-size: 10.5px; color: #64748b; margin-top: 3px;">= 0 g</div>
        </td>
        <td>
            <div style="display:flex; gap:4px;">
                <input type="number" name="items[${idx}][product_weight]" id="pw_${idx}" step="0.0001" min="0.0001" value="${initialData?.product_weight || 10}" required class="jw-input" oninput="calculateRow(${idx})">
                <select name="items[${idx}][product_weight_unit]" id="pwu_${idx}" class="jw-unit-select" onchange="calculateRow(${idx})">
                    <option value="Gram" ${(!initialData || initialData.product_weight_unit === 'Gram') ? 'selected' : ''}>Gram</option>
                    <option value="KG" ${(initialData && initialData.product_weight_unit === 'KG') ? 'selected' : ''}>KG</option>
                    <option value="Milligram" ${(initialData && initialData.product_weight_unit === 'Milligram') ? 'selected' : ''}>mg</option>
                    <option value="Ton" ${(initialData && initialData.product_weight_unit === 'Ton') ? 'selected' : ''}>Ton</option>
                </select>
            </div>
            <div style="font-size: 10.5px; color: #64748b; margin-top: 3px;">Per piece weight</div>
        </td>
        <td>
            <input type="text" id="gross_${idx}" class="jw-input jw-readonly" readonly value="0">
            <div style="font-size: 10.5px; color: #64748b; margin-top: 3px;">Gross Pieces</div>
        </td>
        <td>
            <div style="display:flex; gap:4px; margin-bottom:3px;">
                <input type="number" name="items[${idx}][wastage_percentage]" id="wp_${idx}" step="0.01" min="0" max="100" value="${initialData?.wastage_percentage ?? 2}" class="jw-input" style="width:55%;" placeholder="%" oninput="calculateRow(${idx})">
                <select name="items[${idx}][wastage_type]" id="wt_${idx}" class="jw-unit-select" style="width:45%;" onchange="calculateRow(${idx})">
                    <option value="percentage" selected>%</option>
                    <option value="fixed">Fixed</option>
                    <option value="none">None</option>
                </select>
            </div>
            <input type="text" id="waste_qty_${idx}" class="jw-input jw-readonly" style="font-size: 11px; color:#ea580c;" readonly value="0 PCS">
        </td>
        <td>
            <input type="text" id="net_${idx}" class="jw-input jw-readonly" style="color: #059669; font-size: 13.5px; font-weight:800;" readonly value="0">
            <div style="font-size: 10.5px; color: #059669; margin-top: 3px; font-weight:600;">Net Finished PCS</div>
        </td>
        <td>
            <div style="display:flex; gap:4px; margin-bottom:3px;">
                <input type="number" name="items[${idx}][rate]" id="rate_${idx}" step="0.0001" min="0" value="${initialData?.rate ?? 0.50}" class="jw-input" oninput="calculateRow(${idx})">
            </div>
            <select name="items[${idx}][rate_type]" id="rt_${idx}" class="jw-unit-select" style="width:100%; font-size:11px;" onchange="calculateRow(${idx})">
                <option value="per_piece" ${(!initialData || initialData.rate_type === 'per_piece') ? 'selected' : ''}>₹ / Per Piece</option>
                <option value="per_kg" ${(initialData && initialData.rate_type === 'per_kg') ? 'selected' : ''}>₹ / Per KG</option>
                <option value="fixed" ${(initialData && initialData.rate_type === 'fixed') ? 'selected' : ''}>₹ Fixed Amount</option>
            </select>
        </td>
        <td>
            <input type="text" id="amt_${idx}" class="jw-input jw-readonly" style="font-weight: 800; color: var(--primary);" readonly value="₹0.00">
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn btn-outline btn-sm btn-icon" style="color:#ef4444;" onclick="removeRow(${idx})" title="Remove Row">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    calculateRow(idx);
}

function removeRow(idx) {
    const row = document.getElementById(`row_${idx}`);
    if (row) {
        row.remove();
        recalculateAll();
    }
}

function onProductSelect(idx) {
    const row = document.getElementById(`row_${idx}`);
    if (!row) return;
    const select = row.querySelector('.product-picker');
    const prod = productsCatalog.find(p => p.id == select.value);
    if (prod) {
        document.getElementById(`pw_${idx}`).value = prod.weight_per_piece || 10;
        document.getElementById(`pwu_${idx}`).value = prod.weight_unit || 'Gram';
        document.getElementById(`wp_${idx}`).value = prod.wastage_percentage ?? 2;
        if (prod.price > 0) document.getElementById(`rate_${idx}`).value = prod.price;
    }
    calculateRow(idx);
}

function calculateRow(idx) {
    const rWeight = parseFloat(document.getElementById(`rw_${idx}`)?.value) || 0;
    const rUnit   = document.getElementById(`rwu_${idx}`)?.value || 'KG';
    const rGrams  = convertToGrams(rWeight, rUnit);

    const hint = document.getElementById(`grams_hint_${idx}`);
    if (hint) hint.innerText = `= ${rGrams.toLocaleString()} g`;

    const pWeight = parseFloat(document.getElementById(`pw_${idx}`)?.value) || 0;
    const pUnit   = document.getElementById(`pwu_${idx}`)?.value || 'Gram';
    const pGrams  = convertToGrams(pWeight, pUnit);

    const roundingMethod = document.getElementById('rounding_method')?.value || 'floor';

    let grossPieces = 0;
    if (rGrams > 0 && pGrams > 0) {
        grossPieces = applyRounding(rGrams / pGrams, roundingMethod);
    }
    document.getElementById(`gross_${idx}`).value = grossPieces.toLocaleString();

    const wastageType = document.getElementById(`wt_${idx}`)?.value || 'percentage';
    const wastageVal  = parseFloat(document.getElementById(`wp_${idx}`)?.value) || 0;
    let wastagePieces = 0;

    if (wastageType === 'percentage') {
        wastagePieces = applyRounding(grossPieces * (wastageVal / 100), roundingMethod);
    } else if (wastageType === 'fixed') {
        wastagePieces = Math.min(grossPieces, applyRounding(wastageVal, roundingMethod));
    }
    document.getElementById(`waste_qty_${idx}`).value = `${wastagePieces.toLocaleString()} PCS`;

    const netPieces = Math.max(0, grossPieces - wastagePieces);
    document.getElementById(`net_${idx}`).value = netPieces.toLocaleString();

    const rateType = document.getElementById(`rt_${idx}`)?.value || 'per_piece';
    const rate     = parseFloat(document.getElementById(`rate_${idx}`)?.value) || 0;
    let amount     = 0;

    if (rateType === 'per_kg') {
        amount = (rGrams / 1000) * rate;
    } else if (rateType === 'fixed') {
        amount = rate;
    } else {
        amount = netPieces * rate;
    }

    amount = Math.round(amount * 100) / 100;
    document.getElementById(`amt_${idx}`).value = `₹${amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

    document.getElementById(`amt_${idx}`).dataset.rawAmount = amount;
    document.getElementById(`rw_${idx}`).dataset.rawGrams   = rGrams;
    document.getElementById(`gross_${idx}`).dataset.rawGross = grossPieces;
    document.getElementById(`waste_qty_${idx}`).dataset.rawWaste = wastagePieces;
    document.getElementById(`net_${idx}`).dataset.rawNet   = netPieces;

    recalculateAll();
}

function recalculateAll() {
    let totalReceivedGrams = 0;
    let totalGross         = 0;
    let totalWastage       = 0;
    let totalNet           = 0;
    let subtotal           = 0;

    const rows = document.querySelectorAll('#itemsTableBody tr');
    rows.forEach(tr => {
        const id = tr.id.replace('row_', '');
        totalReceivedGrams += parseFloat(document.getElementById(`rw_${id}`)?.dataset.rawGrams || 0);
        totalGross         += parseFloat(document.getElementById(`gross_${id}`)?.dataset.rawGross || 0);
        totalWastage       += parseFloat(document.getElementById(`waste_qty_${id}`)?.dataset.rawWaste || 0);
        totalNet           += parseFloat(document.getElementById(`net_${id}`)?.dataset.rawNet || 0);
        subtotal           += parseFloat(document.getElementById(`amt_${id}`)?.dataset.rawAmount || 0);
    });

    const totalReceivedKg = totalReceivedGrams / 1000;
    document.getElementById('summary_received_weight').innerText = `${totalReceivedKg.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
    document.getElementById('summary_gross_pieces').innerText   = `${totalGross.toLocaleString()} PCS`;
    document.getElementById('summary_wastage_pieces').innerText = `${totalWastage.toLocaleString()} PCS`;
    document.getElementById('summary_net_pieces').innerText     = `${totalNet.toLocaleString()} PCS`;

    const additional = parseFloat(document.getElementById('inp_additional')?.value) || 0;
    const discount   = parseFloat(document.getElementById('inp_discount')?.value) || 0;
    const tax        = parseFloat(document.getElementById('inp_tax')?.value) || 0;
    const paid       = parseFloat(document.getElementById('inp_paid')?.value) || 0;

    const grandTotal = Math.max(0, subtotal + additional - discount + tax);
    const balance    = Math.max(0, grandTotal - paid);

    document.getElementById('summary_subtotal').innerText    = `₹${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('summary_grand_total').innerText = `₹${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('summary_balance').innerText     = `₹${balance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

document.addEventListener('DOMContentLoaded', () => {
    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => addProductRow(item));
    } else {
        addProductRow();
    }
});
</script>
@endsection
