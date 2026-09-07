@extends('layouts.app')

@section('title', 'Create Purchase Bill / Order - Shree Giriraj Poly Plast')
@section('page-title', 'Create Purchase Bill')

@section('content')
<div class="d-flex justify-between align-center mb-4" style="margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;"><i class="fa fa-cart-plus text-primary"></i> New Vendor Purchase Bill</h2>
        <p class="text-muted" style="font-size: 13px;">Generate an official Purchase Bill for raw materials, additives, or vendor goods with instant stock update</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline">
            <i class="fa fa-arrow-left"></i> All Purchase Bills
        </a>
    </div>
</div>

<form id="poForm" onsubmit="savePurchaseOrder(event)">
    <div class="card mb-4" style="overflow: visible !important;">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-truck-field"></i> Supplier &amp; Order Details</h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="openQuickAddSupplierModal()">
                <i class="fa fa-user-plus"></i> + Add New Supplier
            </button>
        </div>
        <div class="card-body">
            <div class="form-row cols-3 mb-3">
                <div class="form-group">
                    <label>Select Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required onchange="calculateTotals()">
                        <option value="">-- Select Vendor / Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-state="{{ $s->state ?? 'Gujarat' }}" data-country="{{ $s->country ?? 'India' }}">
                                {{ $s->name }} {{ $s->gstin ? '('.$s->gstin.')' : '' }} ({{ $s->state ?? 'Gujarat' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Purchase / PO Date <span class="text-danger">*</span></label>
                    <input type="date" name="po_date" id="po_date" class="form-control" value="{{ session('working_date', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Expected / Delivery Date</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control">
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="form-group">
                    <label>Payment Terms</label>
                    <input type="text" name="payment_terms" id="payment_terms" class="form-control" placeholder="e.g. Immediate / 30 Days Credit">
                </div>
                <div class="form-group">
                    <label>Delivery / Shipping Address</label>
                    <input type="text" name="delivery_address" id="delivery_address" class="form-control" placeholder="Factory Godown, Ahmedabad, Gujarat">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:4px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; color:#047857; margin-bottom:0; background:#ecfdf5; border:1.5px solid #a7f3d0; padding:9px 12px; border-radius:8px; width:100%;">
                        <input type="checkbox" name="auto_receive" id="auto_receive" value="1" checked style="width:18px; height:18px; accent-color:#10b981; cursor:pointer;">
                        <span><i class="fa fa-box-open"></i> Direct Purchase (Update Stock &amp; Ledger Now)</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-boxes-stacked"></i> Material &amp; Purchase Items</h3>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline btn-sm" onclick="openQuickAddMaterialModal()">
                    <i class="fa fa-cube"></i> + New Material
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="addPoRow()">
                    <i class="fa fa-plus"></i> Add Item Row
                </button>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="min-width: 240px;">Material / Component</th>
                            <th style="width: 120px;">HSN Code</th>
                            <th style="width: 110px;">Qty</th>
                            <th style="width: 130px;">Unit Price (₹)</th>
                            <th style="width: 110px;">GST %</th>
                            <th style="width: 140px; text-align: right;">Total (₹)</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="po-items-body">
                        <!-- Rows injected via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary & Totals -->
    <div class="d-flex justify-between align-center" style="gap: 20px; flex-wrap: wrap;">
        <div class="card" style="flex: 1; min-width: 300px; margin-bottom: 0;">
            <div class="card-body">
                <div class="form-group mb-0">
                    <label>Remarks / Purchase Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Special instructions for the vendor or transport remarks..."></textarea>
                </div>
            </div>
        </div>

        <div class="card" style="width: 380px; margin-bottom: 0;">
            <div class="card-body">
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">Subtotal (Taxable):</span>
                    <strong>₹<span id="disp-subtotal">0.00</span></strong>
                </div>
                <div class="d-flex justify-between mb-2" id="row-cgst">
                    <span class="text-muted">CGST:</span>
                    <strong>₹<span id="disp-cgst">0.00</span></strong>
                </div>
                <div class="d-flex justify-between mb-2" id="row-sgst">
                    <span class="text-muted">SGST:</span>
                    <strong>₹<span id="disp-sgst">0.00</span></strong>
                </div>
                <div class="d-flex justify-between mb-2" id="row-igst" style="display:none;">
                    <span class="text-muted">IGST:</span>
                    <strong>₹<span id="disp-igst">0.00</span></strong>
                </div>
                <div class="divider"></div>
                <div class="d-flex justify-between align-center">
                    <span style="font-size: 16px; font-weight: 700; color: var(--primary);">Grand Total:</span>
                    <span style="font-size: 22px; font-weight: 800; color: #059669;">₹<span id="disp-total">0.00</span></span>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary w-full" id="btnSavePo" style="padding: 12px; font-size: 15px; justify-content:center;">
                        <i class="fa fa-check-circle"></i> Save &amp; Generate Purchase Bill
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Quick Add Supplier Modal -->
<div class="modal-overlay" id="quickSupplierModal">
    <div class="modal" style="max-width:540px">
        <div class="modal-header">
            <h3><i class="fa fa-truck-field"></i> Quick Add Supplier</h3>
            <button class="modal-close" type="button" onclick="closeModal('quickSupplierModal')">✕</button>
        </div>
        <form id="quickSupplierForm" onsubmit="saveQuickSupplier(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label>Supplier / Vendor Company Name *</label>
                    <input type="text" id="qs_name" required placeholder="e.g. Supreme Petrochem Ltd.">
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Phone / Mobile</label>
                        <input type="text" id="qs_phone" placeholder="10-digit mobile">
                    </div>
                    <div class="form-group">
                        <label>Country *</label>
                        <select id="qs_country" onchange="handleSupplierCountryChange()" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;">
                            <option value="India" selected>🇮🇳 India</option>
                            <option value="United States">🇺🇸 United States</option>
                            <option value="United Arab Emirates">🇦🇪 United Arab Emirates</option>
                            <option value="United Kingdom">🇬🇧 United Kingdom</option>
                            <option value="Other">🌍 Other Country</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN (Auto-detects State)</label>
                        <input type="text" id="qs_gstin" maxlength="15" placeholder="15-digit GSTIN (e.g. 24..., 27...)" oninput="handleSupplierGstinAutoDetect(this.value)" style="text-transform:uppercase;">
                        <small id="qs_gstin_hint" style="display:block;margin-top:3px;font-size:11px;color:#059669;font-weight:600;"></small>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" id="qs_state" value="Gujarat" placeholder="e.g. Gujarat, Maharashtra">
                    </div>
                </div>
                <div class="form-group">
                    <label>Office / Godown Address</label>
                    <input type="text" id="qs_address" placeholder="Vendor address">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('quickSupplierModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="qs_btn"><i class="fa fa-check"></i> Add Supplier &amp; Select</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Material Modal -->
<div class="modal-overlay" id="quickMaterialModal">
    <div class="modal" style="max-width:540px">
        <div class="modal-header">
            <h3><i class="fa fa-cube"></i> Quick Add Material</h3>
            <button class="modal-close" type="button" onclick="closeModal('quickMaterialModal')">✕</button>
        </div>
        <form id="quickMaterialForm" onsubmit="saveQuickMaterial(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label>Material Name *</label>
                    <input type="text" id="qm_name" required placeholder="e.g. PP Granules MFI-20, Calcium Masterbatch...">
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Material Type *</label>
                        <select id="qm_type">
                            <option value="Raw Material" selected>Raw Material (Granules/Polymer)</option>
                            <option value="Additive">Additive / Masterbatch</option>
                            <option value="Final Product">Final Product</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Base Unit *</label>
                        <select id="qm_unit">
                            <option value="KG" selected>KG (Kilograms)</option>
                            <option value="PCS">PCS (Pieces)</option>
                            <option value="MTR">MTR (Meters)</option>
                            <option value="BAG">BAG (Bags)</option>
                            <option value="TON">TON (Metric Tons)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Default Rate / KG (₹)</label>
                        <input type="number" step="0.01" id="qm_price" value="0.00" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" id="qm_hsn" value="3901" placeholder="e.g. 3901, 3902">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('quickMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="qm_btn"><i class="fa fa-check"></i> Save Material &amp; Add to PO</button>
            </div>
        </form>
    </div>
</div>

<script>
let materialsList = @json($materials);
let rowIndex = 0;

const GST_STATE_MAP = {
    '01': 'Jammu and Kashmir', '02': 'Himachal Pradesh', '03': 'Punjab', '04': 'Chandigarh',
    '05': 'Uttarakhand', '06': 'Haryana', '07': 'Delhi', '08': 'Rajasthan',
    '09': 'Uttar Pradesh', '10': 'Bihar', '11': 'Sikkim', '12': 'Arunachal Pradesh',
    '13': 'Nagaland', '14': 'Manipur', '15': 'Mizoram', '16': 'Tripura',
    '17': 'Meghalaya', '18': 'Assam', '19': 'West Bengal', '20': 'Jharkhand',
    '21': 'Odisha', '22': 'Chhattisgarh', '23': 'Madhya Pradesh', '24': 'Gujarat',
    '26': 'Dadra and Nagar Haveli and Daman and Diu', '27': 'Maharashtra', '29': 'Karnataka',
    '30': 'Goa', '31': 'Lakshadweep', '32': 'Kerala', '33': 'Tamil Nadu',
    '34': 'Puducherry', '35': 'Andaman and Nicobar Islands', '36': 'Telangana',
    '37': 'Andhra Pradesh', '38': 'Ladakh'
};

function getMaterialOptions(selectedId = null) {
    let html = '<option value="">-- Choose Material / Item --</option>';
    materialsList.forEach(m => {
        const unit = m.unit ? ` (${m.unit})` : '';
        const isSel = (selectedId && m.id == selectedId) ? 'selected' : '';
        const price = m.price_per_unit || m.price || 0;
        html += `<option value="${m.id}" data-type="${m.type || 'Raw Material'}" data-unit="${m.unit || 'KG'}" data-price="${price}" ${isSel}>${escapeHtml(m.name)} - ${m.type || 'Material'}${unit}</option>`;
    });
    return html;
}

function addPoRow(prefillMaterialId = null) {
    const tbody = document.getElementById('po-items-body');
    const tr = document.createElement('tr');
    const id = rowIndex++;

    tr.innerHTML = `
        <td>
            <select name="items[${id}][material_id]" class="form-control material-select" required onchange="onMaterialChange(this)">
                ${getMaterialOptions(prefillMaterialId)}
            </select>
        </td>
        <td>
            <input type="text" name="items[${id}][hsn_code]" class="form-control hsn-input" value="3901" placeholder="3901">
        </td>
        <td>
            <input type="number" step="0.01" min="0.01" name="items[${id}][quantity]" class="form-control qty-input" value="1" required oninput="calculateTotals()">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="items[${id}][unit_price]" class="form-control price-input" value="0.00" required oninput="calculateTotals()">
        </td>
        <td>
            <select name="items[${id}][gst_rate]" class="form-control gst-select" onchange="calculateTotals()">
                <option value="18" selected>18%</option>
                <option value="12">12%</option>
                <option value="5">5%</option>
                <option value="28">28%</option>
                <option value="0">0%</option>
            </select>
        </td>
        <td style="text-align: right; font-weight: 700;">
            ₹<span class="row-total">0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-ghost btn-icon text-danger" onclick="removePoRow(this)">
                <i class="fa fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    if (prefillMaterialId) {
        const matSel = tr.querySelector('.material-select');
        onMaterialChange(matSel);
    }

    calculateTotals();
}

function onMaterialChange(selectEl) {
    const tr = selectEl.closest('tr');
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt || !selectEl.value) return;

    const price = parseFloat(opt.dataset.price || 0);
    if (price > 0) {
        tr.querySelector('.price-input').value = price.toFixed(2);
    }
    calculateTotals();
}

function removePoRow(btn) {
    const rows = document.querySelectorAll('#po-items-body tr');
    if (rows.length <= 1) {
        showToast('At least one item is required', 'error');
        return;
    }
    btn.closest('tr').remove();
    calculateTotals();
}

function calculateTotals() {
    const supplierSelect = document.getElementById('supplier_id');
    const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
    const supplierState  = selectedOption ? (selectedOption.dataset.state || 'Gujarat').toLowerCase().trim() : 'gujarat';
    const isIgst = supplierState && supplierState !== 'gujarat' && supplierState !== '24';

    const rowCgst = document.getElementById('row-cgst');
    const rowSgst = document.getElementById('row-sgst');
    const rowIgst = document.getElementById('row-igst');

    if (isIgst) {
        rowCgst.style.display = 'none';
        rowSgst.style.display = 'none';
        rowIgst.style.display = '';
    } else {
        rowCgst.style.display = '';
        rowSgst.style.display = '';
        rowIgst.style.display = 'none';
    }

    let subtotal = 0;
    let cgst = 0;
    let sgst = 0;
    let igst = 0;

    const rows = document.querySelectorAll('#po-items-body tr');
    rows.forEach(tr => {
        const qty      = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
        const price    = parseFloat(tr.querySelector('.price-input')?.value) || 0;
        const gstRate  = parseFloat(tr.querySelector('.gst-select')?.value) || 0;

        const lineTotal = qty * price;
        const totalSpan = tr.querySelector('.row-total');
        if (totalSpan) totalSpan.innerText = lineTotal.toFixed(2);

        subtotal += lineTotal;
        const gstAmt = (lineTotal * gstRate) / 100;

        if (isIgst) {
            igst += gstAmt;
        } else {
            cgst += gstAmt / 2;
            sgst += gstAmt / 2;
        }
    });

    const grandTotal = Math.round((subtotal + cgst + sgst + igst) * 100) / 100;

    document.getElementById('disp-subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('disp-cgst').innerText     = cgst.toFixed(2);
    document.getElementById('disp-sgst').innerText     = sgst.toFixed(2);
    document.getElementById('disp-igst').innerText     = igst.toFixed(2);
    document.getElementById('disp-total').innerText    = grandTotal.toFixed(2);
}

async function savePurchaseOrder(e) {
    e.preventDefault();
    const supplierId = document.getElementById('supplier_id').value;
    if (!supplierId) {
        showToast('Please select a supplier', 'error');
        return;
    }

    const items = [];
    let valid = true;
    document.querySelectorAll('#po-items-body tr').forEach(tr => {
        const matId = tr.querySelector('.material-select')?.value;
        const qty = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(tr.querySelector('.price-input')?.value) || 0;
        const gstRate = parseFloat(tr.querySelector('.gst-select')?.value) || 18;
        const hsn = tr.querySelector('.hsn-input')?.value || '3901';

        if (!matId) {
            valid = false;
            showToast('Please select a material for all rows', 'error');
            return;
        }
        if (qty <= 0) {
            valid = false;
            showToast('Quantity must be greater than 0', 'error');
            return;
        }

        items.push({
            material_id: matId,
            hsn_code: hsn,
            quantity: qty,
            unit_price: price,
            gst_rate: gstRate
        });
    });

    if (!valid || items.length === 0) return;

    const payload = {
        supplier_id: supplierId,
        po_date: document.getElementById('po_date').value,
        expected_delivery_date: document.getElementById('expected_delivery_date').value || null,
        payment_terms: document.getElementById('payment_terms').value || null,
        delivery_address: document.getElementById('delivery_address').value || null,
        auto_receive: document.getElementById('auto_receive').checked,
        notes: document.getElementById('notes').value || null,
        items: items
    };

    const saveBtn = document.getElementById('btnSavePo');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving Purchase Bill...';

    try {
        const res = await fetch('{{ route("purchase-orders.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-check-circle"></i> Save &amp; Generate Purchase Bill';

        if (data.success) {
            showToast(data.message || 'Purchase bill generated successfully!', 'success');
            setTimeout(() => {
                window.location.href = `/purchase-orders/${data.po_id}/print`;
            }, 800);
        } else {
            showToast(data.message || 'Error creating Purchase Bill', 'error');
        }
    } catch(err) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-check-circle"></i> Save &amp; Generate Purchase Bill';
        showToast('Server error while saving Purchase Bill', 'error');
    }
}

// Quick Modals
function openQuickAddSupplierModal() {
    document.getElementById('quickSupplierForm').reset();
    document.getElementById('qs_state').value = 'Gujarat';
    document.getElementById('qs_gstin_hint').innerText = '';
    openModal('quickSupplierModal');
}

function handleSupplierCountryChange() {
    const c = document.getElementById('qs_country').value;
    if (c !== 'India') {
        document.getElementById('qs_state').value = '';
    } else {
        document.getElementById('qs_state').value = 'Gujarat';
    }
}

function handleSupplierGstinAutoDetect(val) {
    const hint = document.getElementById('qs_gstin_hint');
    const stateInput = document.getElementById('qs_state');
    if (!val || val.trim().length < 2) {
        hint.innerText = '';
        return;
    }
    const clean = val.trim().toUpperCase();
    const prefix = clean.substring(0, 2);
    const state = GST_STATE_MAP[prefix];
    if (state) {
        stateInput.value = state;
        hint.innerHTML = `<i class="fa fa-check-circle"></i> Auto-detected state: <strong>${state}</strong>`;
    } else {
        hint.innerText = '';
    }
}

function saveQuickSupplier(e) {
    e.preventDefault();
    const btn = document.getElementById('qs_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    const formData = new FormData();
    const suppName = document.getElementById('qs_name').value;
    formData.append('name', suppName);
    formData.append('phone', document.getElementById('qs_phone').value);
    formData.append('country', document.getElementById('qs_country').value);
    formData.append('state', document.getElementById('qs_state').value || 'Gujarat');
    formData.append('gstin', document.getElementById('qs_gstin').value);
    formData.append('address', document.getElementById('qs_address').value);

    fetch('{{ route('suppliers.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Add Supplier &amp; Select';
        if (res.success) {
            showToast('Supplier added successfully!', 'success');
            closeModal('quickSupplierModal');
            // Fetch updated suppliers
            fetch('{{ route('suppliers.index') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(list => {
                    const sel = document.getElementById('supplier_id');
                    sel.innerHTML = '<option value="">-- Select Vendor / Supplier --</option>';
                    list.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.dataset.state = s.state || 'Gujarat';
                        opt.dataset.country = s.country || 'India';
                        opt.text = `${s.name} ${s.gstin ? '('+s.gstin+')' : ''} (${s.state || 'Gujarat'})`;
                        if (s.name === suppName) opt.selected = true;
                        sel.appendChild(opt);
                    });
                    calculateTotals();
                })
                .catch(() => location.reload());
        } else {
            showToast(res.message || 'Failed to add supplier', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Add Supplier &amp; Select';
        showToast('Error saving supplier', 'error');
    });
}

function openQuickAddMaterialModal() {
    document.getElementById('quickMaterialForm').reset();
    openModal('quickMaterialModal');
}

function saveQuickMaterial(e) {
    e.preventDefault();
    const btn = document.getElementById('qm_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    const formData = new FormData();
    const matName = document.getElementById('qm_name').value;
    formData.append('name', matName);
    formData.append('type', document.getElementById('qm_type').value);
    formData.append('unit', document.getElementById('qm_unit').value);
    formData.append('price_per_unit', document.getElementById('qm_price').value);
    formData.append('hsn_code', document.getElementById('qm_hsn').value);

    fetch('{{ route('materials.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Save Material &amp; Add to PO';
        if (res.success) {
            showToast('Material added successfully!', 'success');
            closeModal('quickMaterialModal');
            // Fetch updated materials
            fetch('{{ route('materials.index') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(list => {
                    materialsList = list;
                    // Update all row select options
                    document.querySelectorAll('.material-select').forEach(sel => {
                        const curVal = sel.value;
                        sel.innerHTML = getMaterialOptions(curVal);
                    });
                    const newMat = list.find(m => m.name === matName);
                    if (newMat) {
                        addPoRow(newMat.id);
                    }
                })
                .catch(() => location.reload());
        } else {
            showToast(res.message || 'Failed to add material', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Save Material &amp; Add to PO';
        showToast('Error saving material', 'error');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('#po-items-body tr').length === 0) {
        addPoRow();
    }
});
</script>
@endsection
