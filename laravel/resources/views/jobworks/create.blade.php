@extends('layouts.app')
@section('title', 'New Job Work Entry')
@section('page-title', 'Create Job Work Entry')

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
            <i class="fa fa-circle-plus text-primary"></i> New Job Work Automatic Weight &amp; Quantity Entry
        </h2>
        <p class="text-muted" style="font-size: 12.5px;">Enter client received raw material in KG / Grams. Produced pieces &amp; wastage are calculated automatically.</p>
    </div>
    <a href="{{ route('jobworks.index') }}" class="btn btn-outline btn-sm">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
</div>

<form id="createJobWorkForm" method="POST" action="{{ route('jobworks.store') }}">
    @csrf

    {{-- Header Information Card --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-file-invoice"></i> Order Information</h3>
            <span class="badge badge-purple"><i class="fa fa-bolt"></i> Real-time Auto Calculation</span>
        </div>
        <div class="card-body" style="padding: 16px;">
            <div class="form-row cols-4">
                <div class="form-group">
                    <label>Job Work No. *</label>
                    <input type="text" name="job_work_number" value="{{ (!empty($duplicateFrom)) ? $nextJobWorkNumber : ($nextJobWorkNumber ?? 'JW-'.date('Ym').'-0001') }}" required class="jw-readonly" readonly>
                </div>

                <div class="form-group">
                    <label>Client / Party *</label>
                    <div style="display:flex; gap:6px;">
                        <select name="client_id" id="client_select" required class="jw-input" style="flex:1;">
                            <option value="">-- Select Client / Party --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ (isset($duplicateFrom) && $duplicateFrom->client_id == $c->id) ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company_name ? "({$c->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('addClientModal')" title="Add New Client" style="padding: 0 10px;">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Order Date *</label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="jw-input">
                </div>

                <div class="form-group">
                    <label>Due / Delivery Date</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="jw-input">
                </div>
            </div>

            <div class="form-row cols-3" style="margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Client Ref / Challan No.</label>
                    <input type="text" name="reference_number" value="{{ $duplicateFrom->reference_number ?? '' }}" placeholder="e.g. CH-98234" class="jw-input">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Initial Status *</label>
                    <select name="status" class="jw-input">
                        <option value="Material Received" selected>Material Received</option>
                        <option value="In Production">In Production</option>
                        <option value="Draft">Draft</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Quantity Rounding Mode *</label>
                    <select name="rounding_method" id="rounding_method" class="jw-input" onchange="recalculateAll()">
                        <option value="floor" selected>Floor (Truncate Decimals - Standard e.g. 33.3 → 33 PCS)</option>
                        <option value="round">Round to Nearest Integer (e.g. 33.6 → 34 PCS)</option>
                        <option value="ceil">Ceiling (Round Up e.g. 33.1 → 34 PCS)</option>
                        <option value="decimal">Allow 4 Decimals (Exact)</option>
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
                        {{-- Product Rows dynamically injected here --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Bottom Summary & Financials --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 20px;">
        {{-- Material & Pieces Summary Box --}}
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

        {{-- Financial Settlement Box --}}
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
                        <input type="number" name="additional_charges" id="inp_additional" step="0.01" min="0" value="0.00" class="jw-input" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label style="font-size: 11px;">Discount (₹)</label>
                        <input type="number" name="discount" id="inp_discount" step="0.01" min="0" value="0.00" class="jw-input" oninput="recalculateAll()">
                    </div>
                </div>
                <div class="form-row cols-2" style="margin-bottom: 8px;">
                    <div>
                        <label style="font-size: 11px;">Tax / GST (₹)</label>
                        <input type="number" name="tax" id="inp_tax" step="0.01" min="0" value="0.00" class="jw-input" oninput="recalculateAll()">
                    </div>
                    <div>
                        <label style="font-size: 11px;">Advance Paid (₹)</label>
                        <input type="number" name="paid_amount" id="inp_paid" step="0.01" min="0" value="0.00" class="jw-input" oninput="recalculateAll()">
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
                <textarea name="remarks" rows="2" placeholder="e.g. Return leftover scrap to client, dispatch in 500 pcs bags..." class="jw-input"></textarea>
            </div>
        </div>
    </div>

    <div class="d-flex justify-between align-center mb-5">
        <a href="{{ route('jobworks.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" style="padding: 10px 28px; font-size: 15px;">
            <i class="fa fa-save"></i> Save Job Work Order
        </button>
    </div>
</form>

{{-- Add Client Modal --}}
<div class="modal-overlay" id="addClientModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa fa-user-plus"></i> Add Job Work Client / Party</h3>
            <button class="modal-close" onclick="closeModal('addClientModal')">✕</button>
        </div>
        <form id="ajaxClientForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Client / Contact Name *</label>
                    <input type="text" id="ac_name" name="name" required placeholder="e.g. Rajeshbhai Patel">
                </div>
                <div class="form-group">
                    <label>Company / Firm Name</label>
                    <input type="text" id="ac_company" name="company_name" placeholder="e.g. Shree Radhey Polymers">
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" id="ac_phone" name="phone" placeholder="+91 98250 XXXXX">
                    </div>
                    <div class="form-group">
                        <label>GST Number</label>
                        <input type="text" id="ac_gstin" name="gstin" placeholder="24AAAAA0000A1Z5">
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea id="ac_address" name="address" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addClientModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Client</button>
            </div>
        </form>
    </div>
</div>

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
@endphp

@endsection

@section('scripts')
<script>
const productsCatalog = @json($productsJson);
let rowIndex = 0;

function convertToGrams(weight, unit) {
    weight = parseFloat(weight) || 0;
    unit = (unit || 'Gram').toUpperCase();
    if (unit === 'KG' || unit === 'KILOGRAM') return weight * 1000;
    if (unit === 'TON' || unit === 'METRIC TON') return weight * 1000000;
    if (unit === 'MILLIGRAM' || unit === 'MG') return weight / 1000;
    return weight; // Grams
}

function applyRounding(val, method) {
    val = parseFloat(val) || 0;
    if (method === 'round') return Math.round(val);
    if (method === 'ceil') return Math.ceil(val);
    if (method === 'decimal') return parseFloat(val.toFixed(4));
    return Math.floor(val); // Standard floor
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
            <input type="text" name="items[${idx}][remarks]" placeholder="Item remarks / batch notes..." class="jw-input" style="margin-top: 4px; font-size: 11px;">
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
            <div id="grams_hint_${idx}" style="font-size: 10.5px; color: #64748b; margin-top: 3px;">= 500,000 g</div>
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
                <option value="per_piece" selected>₹ / Per Piece</option>
                <option value="per_kg">₹ / Per KG</option>
                <option value="fixed">₹ Fixed Amount</option>
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

    // If initial data or single product, calculate immediately
    if (initialData && initialData.product_id) {
        onProductSelect(idx, false);
    } else {
        calculateRow(idx);
    }
}

function removeRow(idx) {
    const row = document.getElementById(`row_${idx}`);
    if (row) {
        row.remove();
        recalculateAll();
    }
}

function onProductSelect(idx, shouldResetRate = true) {
    const row = document.getElementById(`row_${idx}`);
    if (!row) return;

    const select = row.querySelector('.product-picker');
    const productId = select.value;
    const prod = productsCatalog.find(p => p.id == productId);

    if (prod) {
        document.getElementById(`pw_${idx}`).value = prod.weight_per_piece || 10;
        document.getElementById(`pwu_${idx}`).value = prod.weight_unit || 'Gram';
        document.getElementById(`wp_${idx}`).value = prod.wastage_percentage ?? 2;
        if (shouldResetRate && prod.price > 0) {
            document.getElementById(`rate_${idx}`).value = prod.price;
        }
    }
    calculateRow(idx);
}

function calculateRow(idx) {
    const rWeight = parseFloat(document.getElementById(`rw_${idx}`)?.value) || 0;
    const rUnit   = document.getElementById(`rwu_${idx}`)?.value || 'KG';
    const rGrams  = convertToGrams(rWeight, rUnit);

    const hint = document.getElementById(`grams_hint_${idx}`);
    if (hint) {
        hint.innerText = `= ${rGrams.toLocaleString()} g`;
    }

    const pWeight = parseFloat(document.getElementById(`pw_${idx}`)?.value) || 0;
    const pUnit   = document.getElementById(`pwu_${idx}`)?.value || 'Gram';
    const pGrams  = convertToGrams(pWeight, pUnit);

    const roundingMethod = document.getElementById('rounding_method')?.value || 'floor';

    // 1. Gross Pieces = Received Grams ÷ Product Piece Grams
    let grossPieces = 0;
    if (rGrams > 0 && pGrams > 0) {
        grossPieces = applyRounding(rGrams / pGrams, roundingMethod);
    }
    document.getElementById(`gross_${idx}`).value = grossPieces.toLocaleString();

    // 2. Wastage
    const wastageType = document.getElementById(`wt_${idx}`)?.value || 'percentage';
    const wastageVal  = parseFloat(document.getElementById(`wp_${idx}`)?.value) || 0;
    let wastagePieces = 0;

    if (wastageType === 'percentage') {
        wastagePieces = applyRounding(grossPieces * (wastageVal / 100), roundingMethod);
    } else if (wastageType === 'fixed') {
        wastagePieces = Math.min(grossPieces, applyRounding(wastageVal, roundingMethod));
    }
    document.getElementById(`waste_qty_${idx}`).value = `${wastagePieces.toLocaleString()} PCS`;

    // 3. Net Pieces = Gross - Wastage
    const netPieces = Math.max(0, grossPieces - wastagePieces);
    document.getElementById(`net_${idx}`).value = netPieces.toLocaleString();

    // 4. Rate & Amount
    const rateType = document.getElementById(`rt_${idx}`)?.value || 'per_piece';
    const rate     = parseFloat(document.getElementById(`rate_${idx}`)?.value) || 0;
    let amount     = 0;

    if (rateType === 'per_kg') {
        const weightKg = rGrams / 1000;
        amount = weightKg * rate;
    } else if (rateType === 'fixed') {
        amount = rate;
    } else { // per_piece
        amount = netPieces * rate;
    }

    amount = Math.round(amount * 100) / 100;
    document.getElementById(`amt_${idx}`).value = `₹${amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

    // Store unformatted for order totals
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

    // Financial calculations
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

// Client Ajax submission
document.getElementById('ajaxClientForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const name    = document.getElementById('ac_name').value;
    const company = document.getElementById('ac_company').value;
    const phone   = document.getElementById('ac_phone').value;
    const gstin   = document.getElementById('ac_gstin').value;
    const address = document.getElementById('ac_address').value;

    try {
        const res = await fetch('{{ route("jobworks.clients.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, company_name: company, phone, gstin, address })
        });
        const data = await res.json();
        if (data.success) {
            const select = document.getElementById('client_select');
            const opt = document.createElement('option');
            opt.value = data.client.id;
            opt.text  = `${data.client.name} ${data.client.company_name ? '('+data.client.company_name+')' : ''}`;
            opt.selected = true;
            select.appendChild(opt);
            closeModal('addClientModal');
            document.getElementById('ajaxClientForm').reset();
            showToast('Client added successfully!', 'success');
        } else {
            showToast(data.message || 'Error adding client', 'error');
        }
    } catch(err) {
        showToast('Failed to add client', 'error');
    }
});

// Initialize first row
document.addEventListener('DOMContentLoaded', () => {
    addProductRow();
});
</script>
@endsection
