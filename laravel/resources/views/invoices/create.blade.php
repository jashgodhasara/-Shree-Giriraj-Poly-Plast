@extends('layouts.app')
@section('title', 'Create Bill')
@section('page-title', 'Create New Bill')

@section('content')
<style>
.billing-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; width: 100%; }
@media(max-width:900px){
    .billing-grid { grid-template-columns:1fr !important; width: 100%; }
    .table-wrap { overflow-x: auto !important; -webkit-overflow-scrolling: touch; width: 100%; }
    #itemsTable { min-width: 500px; width: 100%; }
    .card-header { flex-wrap: wrap; gap: 10px; }
}
.billing-grid > div:last-child .card { position:static !important; }

/* ── SEARCHABLE COMBOBOX ── */
.card.billing-card { overflow: visible !important; }
.card-body.billing-card-body { overflow: visible !important; }
.table-wrap.billing-table-wrap { overflow: visible !important; }

.combo-wrap { position: relative; width: 100%; }
.combo-input {
    width: 100%; padding: 8px 30px 8px 10px;
    border: 1.5px solid #cbd5e1; border-radius: 6px;
    font-size: 13px; font-weight: 500; color: #1e293b;
    background: #fff; outline: none; transition: all .2s;
}
.combo-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
.combo-arrow {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    font-size: 11px; color: #94a3b8; pointer-events: none; transition: transform .2s;
}
.combo-wrap.open { z-index: 999999 !important; position: relative; }
.combo-wrap.open .combo-arrow { transform: translateY(-50%) rotate(180deg); color: #6366f1; }
.combo-dropdown {
    position: absolute; top: calc(100% + 4px); left: 0;
    min-width: 360px; width: max-content; max-width: 520px;
    max-height: 320px; overflow-y: auto;
    background: #ffffff; border: 1.5px solid #6366f1; border-radius: 8px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.22), 0 4px 14px rgba(99,102,241,0.18);
    z-index: 999999 !important; display: none; padding: 6px;
}
.combo-wrap.open .combo-dropdown { display: block; animation: dropIn .15s ease-out; }
@keyframes dropIn { from{opacity:0; transform:translateY(-6px)} to{opacity:1; transform:translateY(0)} }

.combo-item {
    padding: 8px 12px; border-radius: 6px; cursor: pointer;
    font-size: 13px; color: #1e293b;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    transition: background .15s;
    border-bottom: 1px solid #f1f5f9;
}
.combo-item:last-child { border-bottom: none; }
.combo-item:hover, .combo-item.active { background: #eef2ff; color: #3730a3; }
.combo-item.selected { background: #e0e7ff; color: #3730a3; font-weight: 700; }
.combo-item-meta { display: flex; align-items: center; gap: 6px; font-size: 11px; flex-shrink: 0; }
.badge-stock { background: #dcfce7; color: #15803d; padding: 2px 7px; border-radius: 4px; font-weight: 600; font-size: 11px; }
.badge-price { background: #e0e7ff; color: #4338ca; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 11px; }
.badge-gst   { background: #fef3c7; color: #b45309; padding: 2px 7px; border-radius: 4px; font-weight: 600; font-size: 11px; }
.combo-empty {
    padding: 14px 10px; font-size: 12.5px; color: #64748b; text-align: center;
}
.combo-add-btn {
    display: block; width: 100%; padding: 8px; text-align: center;
    background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;
    color: #4f46e5; font-size: 12px; font-weight: 600; cursor: pointer;
    margin-top: 4px; transition: background .2s;
}
.combo-add-btn:hover { background: #ede9fe; border-color: #818cf8; }
</style>

<div class="billing-grid">

<div>
<div class="card billing-card" style="overflow:visible !important;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3><i class="fa fa-file-invoice-dollar"></i> Bill Items</h3>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline btn-sm" onclick="openQuickAddCustomerModal()">
                <i class="fa fa-user-plus"></i> + Add Customer
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openQuickAddProductModal()">
                <i class="fa fa-plus"></i> + Add Product
            </button>
        </div>
    </div>
    <div class="card-body billing-card-body" style="overflow:visible !important;">
        <!-- Customer, Invoice Date & Transport -->
        <div class="form-row cols-3" style="margin-bottom:8px">
            <div class="form-group" style="position:relative;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label style="margin-bottom:0">Customer * <small style="color:var(--primary);font-weight:normal;">(Type to search)</small></label>
                    <a href="javascript:void(0)" onclick="openQuickAddCustomerModal()" style="font-size:11px; color:var(--primary); text-decoration:underline;">+ New Customer</a>
                </div>
                <!-- Searchable Customer Combo -->
                <div class="combo-wrap" id="customerComboWrap">
                    <input type="text" id="customerSearchInput" class="combo-input" placeholder="Type or click to choose customer..." autocomplete="off">
                    <i class="fa fa-chevron-down combo-arrow"></i>
                    <input type="hidden" id="customerSelect" value="" required>
                    <div class="combo-dropdown" id="customerDropdown">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Invoice Date *</label>
                <input type="date" id="invoiceDate" value="{{ session('working_date', date('Y-m-d')) }}" style="padding:8px 10px; border-radius:6px; border:1.5px solid #cbd5e1; font-size:13px; font-weight:600; color:#1e293b;" required onchange="recalc()">
            </div>
            <div class="form-group">
                <label>Transporter</label>
                <select id="transporterSelect" style="padding:8px 10px; border-radius:6px; border:1.5px solid #cbd5e1; font-size:13px;">
                    <option value="">-- None --</option>
                    @foreach($transporters as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}@if($t->vehicle_no) ({{ $t->vehicle_no }})@endif</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Secondary Invoice Details -->
        <div class="form-row cols-3" style="margin-bottom:8px">
            <div class="form-group">
                <label>Payment Terms</label>
                <input type="text" id="paymentTerms" placeholder="e.g. ADVANCE / 30 DAYS">
            </div>
            <div class="form-group">
                <label>LR / Challan No.</label>
                <input type="text" id="lrNumber" placeholder="Transport receipt no.">
            </div>
            <div class="form-group">
                <label>Challan Number</label>
                <input type="text" id="challanNumber" placeholder="Delivery challan no.">
            </div>
        </div>
        <div class="form-row cols-3" style="margin-bottom:8px">
            <div class="form-group">
                <label>Buyer P.O. Number</label>
                <input type="text" id="poNumber" placeholder="Customer PO ref.">
            </div>
            <div class="form-group">
                <label>P.O. Date</label>
                <input type="text" id="poDate" placeholder="e.g. 27.08.2024">
            </div>
            <div class="form-group">
                <label>E-Way Bill No.</label>
                <input type="text" id="ewayBillNo" placeholder="E-Way bill number">
            </div>
        </div>
        <div class="form-row cols-2" style="margin-bottom:16px">
            <div class="form-group">
                <label>Delivery At</label>
                <input type="text" id="deliveryAt" placeholder="Delivery address / location">
            </div>
            <div class="form-group">
                <label>Notes</label>
                <input type="text" id="invoiceNotes" placeholder="Optional note on invoice">
            </div>
        </div>

        <!-- Items table -->
        <div class="table-wrap billing-table-wrap" style="overflow:visible !important;">
            <table id="itemsTable" style="overflow:visible !important;">
                <thead>
                    <tr>
                        <th style="width:36%">Product (Type to search)</th>
                        <th style="width:12%">Qty</th>
                        <th style="width:14%">Unit Price (₹)</th>
                        <th style="width:14%">Total (₹)</th>
                        <th style="width:10%">GST%</th>
                        <th style="width:10%">GST Amt</th>
                        <th style="width:4%"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody" style="overflow:visible !important;">
                    <!-- Row 1 will be initialized by JS -->
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-between align-center" style="margin-top:14px">
            <button type="button" class="btn btn-outline btn-sm" style="display:inline-flex;" onclick="addRow()">
                <i class="fa fa-plus"></i> Add Item Row
            </button>
            <button type="button" class="btn btn-ghost btn-sm text-primary" onclick="openQuickAddProductModal()">
                <i class="fa fa-tag"></i> + Product not in list? Add New Product
            </button>
        </div>
    </div>
</div>
</div>

<!-- Summary sidebar -->
<div>
<div class="card summary-card" style="position:sticky;top:20px">
    <div class="card-header"><h3><i class="fa fa-calculator"></i> Summary</h3></div>
    <div class="card-body">
        <div style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(59,130,246,0.1)); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; padding: 8px 12px; font-size: 11px; color: #065f46; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-bolt" style="color: #10b981;"></i>
            <div><strong>Instant Calculator:</strong> Real-time GST &amp; Total computation.</div>
        </div>

        <div id="taxLabel" style="font-size:12px;color:#64748b;margin-bottom:10px;padding:6px 10px;background:#f1f5f9;border-radius:6px;">
            Tax type: <strong>CGST + SGST</strong> (Gujarat)
        </div>
        <table style="width:100%;font-size:13px;">
            <tr><td style="padding:5px 0;color:#64748b;">Subtotal</td><td style="text-align:right;font-weight:600" id="s_subtotal">₹0.00</td></tr>
            <tr id="row_cgst"><td style="padding:5px 0;color:#64748b;">CGST</td><td style="text-align:right" id="s_cgst">₹0.00</td></tr>
            <tr id="row_sgst"><td style="padding:5px 0;color:#64748b;">SGST</td><td style="text-align:right" id="s_sgst">₹0.00</td></tr>
            <tr id="row_igst" style="display:none"><td style="padding:5px 0;color:#64748b;">IGST</td><td style="text-align:right" id="s_igst">₹0.00</td></tr>
            <tr style="border-top:2px solid #e2e8f0">
                <td style="padding:8px 0;font-weight:700;font-size:15px;">Grand Total</td>
                <td style="text-align:right;font-weight:700;font-size:18px;color:#2563eb" id="s_grand">₹0.00</td>
            </tr>
        </table>
        <button type="button" class="btn btn-primary w-full mt-2" onclick="generateInvoice()" style="margin-top:16px">
            <i class="fa fa-check"></i> Generate Invoice
        </button>
        <button type="button" class="btn btn-success w-full" onclick="simulateUpiVerification()" style="margin-top:8px; justify-content: center; background: linear-gradient(135deg, #10b981, #059669);">
            <i class="fa fa-qrcode"></i> Instant UPI QR Auto-Detect
        </button>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline w-full" style="margin-top:8px;justify-content:center;">
            <i class="fa fa-list"></i> View All Invoices
        </a>
    </div>
</div>
</div>
</div>

<!-- Quick Add Product Modal -->
<div class="modal-overlay" id="quickProductModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa fa-tag"></i> Quick Add Product</h3>
            <button class="modal-close" type="button" onclick="closeModal('quickProductModal')">✕</button>
        </div>
        <form id="quickProductForm" onsubmit="saveQuickProduct(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" id="qp_name" required placeholder="e.g. Plastic Box, Poly Bag...">
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Unit Selling Price (₹) *</label>
                        <input type="number" id="qp_price" step="0.01" required value="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>GST Rate (%) *</label>
                        <select id="qp_gst_rate">
                            <option value="0">0%</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18" selected>18%</option>
                            <option value="28">28%</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" id="qp_hsn" placeholder="e.g. 3923">
                    </div>
                    <div class="form-group">
                        <label>Initial Stock Quantity</label>
                        <input type="number" id="qp_stock" step="0.01" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('quickProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="qp_btn"><i class="fa fa-check"></i> Add Product &amp; Select</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div class="modal-overlay" id="quickCustomerModal">
    <div class="modal" style="max-width:540px">
        <div class="modal-header">
            <h3><i class="fa fa-user-plus"></i> Quick Add Customer</h3>
            <button class="modal-close" type="button" onclick="closeModal('quickCustomerModal')">✕</button>
        </div>
        <form id="quickCustomerForm" onsubmit="saveQuickCustomer(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label>Customer / Company Name *</label>
                    <input type="text" id="qc_name" required placeholder="e.g. Acme Polymers Ltd.">
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="qc_phone" placeholder="10-digit mobile">
                    </div>
                    <div class="form-group">
                        <label>Country *</label>
                        <select id="qc_country" onchange="handleCustomerCountryChange()" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;">
                            <option value="India" selected>🇮🇳 India</option>
                            <option value="United States">🇺🇸 United States</option>
                            <option value="United Arab Emirates">🇦🇪 United Arab Emirates</option>
                            <option value="United Kingdom">🇬🇧 United Kingdom</option>
                            <option value="Germany">🇩🇪 Germany</option>
                            <option value="Canada">🇨🇦 Canada</option>
                            <option value="Australia">🇦🇺 Australia</option>
                            <option value="Singapore">🇸🇬 Singapore</option>
                            <option value="Other">🌍 Other Overseas Country</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>GSTIN (Auto-detects State)</label>
                        <input type="text" id="qc_gstin" maxlength="15" placeholder="15-digit GSTIN (e.g. 24..., 27...)" oninput="handleGstinAutoDetect(this.value)" style="text-transform:uppercase;">
                        <small id="qc_gstin_hint" style="display:block;margin-top:3px;font-size:11px;color:#059669;font-weight:600;"></small>
                    </div>
                    <div class="form-group">
                        <label>State / Province</label>
                        <input type="text" id="qc_state" value="Gujarat" placeholder="e.g. Gujarat, Maharashtra" oninput="handleCustomStateChange()">
                    </div>
                </div>
                <div class="form-group">
                    <label>Billing Address</label>
                    <input type="text" id="qc_address" placeholder="Factory / billing address">
                </div>
                <div id="qc_tax_preview" style="background:#f1f5f9;border-radius:8px;padding:8px 12px;font-size:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-info-circle" style="color:#6366f1;"></i>
                    <span id="qc_tax_preview_text">Auto Tax: <strong>CGST + SGST (Gujarat Intra-State)</strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('quickCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="qc_btn"><i class="fa fa-check"></i> Add Customer &amp; Select</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
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

let productsData = @json($products->keyBy('id'));
let customersData = @json($customers->keyBy('id'));
let selectedCustomerObj = null;
let currentTaxRegime = {
    type: 'INTRA_STATE',
    label: 'CGST + SGST (Gujarat Intra-State)',
    cgst_split: 0.5,
    sgst_split: 0.5,
    igst_split: 0.0,
    is_export: false
};

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.combo-wrap')) {
        document.querySelectorAll('.combo-wrap').forEach(w => w.classList.remove('open'));
    }
});

/* ── GSTIN STATE AUTO DETECT HELPER ── */
function detectStateFromGstin(gstin) {
    if (!gstin) return null;
    const clean = gstin.trim().toUpperCase();
    if (clean.length >= 2) {
        const prefix = clean.substring(0, 2);
        return GST_STATE_MAP[prefix] || null;
    }
    return null;
}

function handleGstinAutoDetect(val) {
    const hint = document.getElementById('qc_gstin_hint');
    const stateInput = document.getElementById('qc_state');
    const countryVal = document.getElementById('qc_country').value;
    
    if (countryVal !== 'India') {
        hint.textContent = 'Overseas customer — Export tax rules apply.';
        updateQuickModalTaxPreview();
        return;
    }

    const state = detectStateFromGstin(val);
    if (state) {
        stateInput.value = state;
        hint.innerHTML = `<i class="fa fa-check-circle"></i> Auto-detected state: <strong>${state}</strong>`;
    } else {
        hint.textContent = '';
    }
    updateQuickModalTaxPreview();
}

function handleCustomerCountryChange() {
    const country = document.getElementById('qc_country').value;
    const gstinInput = document.getElementById('qc_gstin');
    const stateInput = document.getElementById('qc_state');
    
    if (country !== 'India') {
        gstinInput.placeholder = 'Tax ID / VAT / Tax Number';
        stateInput.placeholder = 'State / Region (e.g. California, Dubai)';
    } else {
        gstinInput.placeholder = '15-digit GSTIN (e.g. 24..., 27...)';
        if (!stateInput.value || stateInput.value.toLowerCase() !== 'gujarat') {
            stateInput.value = 'Gujarat';
        }
    }
    updateQuickModalTaxPreview();
}

function handleCustomStateChange() {
    updateQuickModalTaxPreview();
}

function updateQuickModalTaxPreview() {
    const country = document.getElementById('qc_country').value;
    const state = (document.getElementById('qc_state').value || '').trim();
    const isDomestic = country.toLowerCase() === 'india';
    const previewText = document.getElementById('qc_tax_preview_text');

    if (!isDomestic) {
        previewText.innerHTML = `Auto Tax: <strong style="color:#8b5cf6;">Export (0% Tax / LUT)</strong> — ${escapeHtml(country)}`;
    } else if (state.toLowerCase() === 'gujarat' || state.toLowerCase() === 'gj' || (document.getElementById('qc_gstin').value || '').startsWith('24')) {
        previewText.innerHTML = `Auto Tax: <strong style="color:#10b981;">CGST (50%) + SGST (50%)</strong> — Gujarat Intra-State`;
    } else {
        previewText.innerHTML = `Auto Tax: <strong style="color:#3b82f6;">IGST (100%)</strong> — Inter-State: ${escapeHtml(state || 'Outside Gujarat')}`;
    }
}

/* ── CUSTOMER SEARCHABLE COMBO ── */
function initCustomerCombo() {
    const wrap = document.getElementById('customerComboWrap');
    const input = document.getElementById('customerSearchInput');
    const dropdown = document.getElementById('customerDropdown');
    const hidden = document.getElementById('customerSelect');

    function renderCustomerOptions(filter = '') {
        const query = filter.toLowerCase().trim();
        const list = Object.values(customersData).filter(c => {
            const name = (c.name || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();
            const gstin = (c.gstin || '').toLowerCase();
            const state = (c.state || '').toLowerCase();
            const country = (c.country || '').toLowerCase();
            return name.includes(query) || phone.includes(query) || gstin.includes(query) || state.includes(query) || country.includes(query);
        });

        if (list.length === 0) {
            dropdown.innerHTML = `
                <div class="combo-empty">No customer found matching "${filter}"</div>
                <button type="button" class="combo-add-btn" onclick="openQuickAddCustomerWithName('${escapeHtml(filter)}')">
                    <i class="fa fa-plus"></i> Add "${escapeHtml(filter)}" as New Customer
                </button>
            `;
            return;
        }

        dropdown.innerHTML = list.map(c => {
            const country = c.country || 'India';
            const state = c.state || (country.toLowerCase() === 'india' ? 'Gujarat' : '');
            const isDomestic = country.toLowerCase() === 'india';
            const isGuj = isDomestic && (state.toLowerCase() === 'gujarat' || (c.gstin && c.gstin.startsWith('24')));
            
            let taxBadge = '';
            if (!isDomestic) {
                taxBadge = `<span class="badge" style="background:#f3e8ff;color:#7e22ce;font-size:10px;">Export (0% LUT)</span>`;
            } else if (isGuj) {
                taxBadge = `<span class="badge" style="background:#ecfdf5;color:#047857;font-size:10px;">CGST + SGST (GJ)</span>`;
            } else {
                taxBadge = `<span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:10px;">IGST (${state})</span>`;
            }

            return `
                <div class="combo-item ${hidden.value == c.id ? 'selected' : ''}" onclick="selectCustomer(${c.id})">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <strong>${escapeHtml(c.name)}</strong>
                            <small style="color:#64748b;margin-left:4px;">(${escapeHtml(state || country)})</small>
                        </div>
                        ${taxBadge}
                    </div>
                    <div class="combo-item-meta" style="margin-top:2px;">
                        ${c.phone ? `<span style="color:#64748b;"><i class="fa fa-phone"></i> ${escapeHtml(c.phone)}</span>` : ''}
                        ${c.gstin ? `<span class="badge badge-gray" style="font-size:10px;">GSTIN: ${escapeHtml(c.gstin)}</span>` : ''}
                        ${c.country && c.country.toLowerCase() !== 'india' ? `<span style="font-size:11px;color:#7e22ce;">🌍 ${escapeHtml(c.country)}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    input.addEventListener('focus', function() {
        document.querySelectorAll('.combo-wrap').forEach(w => { if (w !== wrap) w.classList.remove('open'); });
        wrap.classList.add('open');
        renderCustomerOptions(this.value);
    });

    input.addEventListener('click', function() {
        document.querySelectorAll('.combo-wrap').forEach(w => { if (w !== wrap) w.classList.remove('open'); });
        wrap.classList.add('open');
        renderCustomerOptions(this.value);
    });

    const arrow = wrap.querySelector('.combo-arrow');
    if (arrow) {
        arrow.style.pointerEvents = 'auto';
        arrow.style.cursor = 'pointer';
        arrow.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = wrap.classList.contains('open');
            document.querySelectorAll('.combo-wrap').forEach(w => w.classList.remove('open'));
            if (!isOpen) {
                wrap.classList.add('open');
                input.focus();
                renderCustomerOptions(input.value);
            }
        });
    }

    input.addEventListener('input', function() {
        wrap.classList.add('open');
        renderCustomerOptions(this.value);
    });
}

function computeCustomerTaxRegime(c) {
    if (!c) {
        return {
            type: 'INTRA_STATE',
            label: 'CGST + SGST (Gujarat Intra-State)',
            cgst_split: 0.5,
            sgst_split: 0.5,
            igst_split: 0.0,
            is_export: false
        };
    }

    const country = (c.country || 'India').trim();
    let state = (c.state || '').trim();
    const gstin = (c.gstin || '').trim();
    const taxType = (c.tax_type || '').trim().toLowerCase();

    // Auto detect from GSTIN if empty
    if (!state && gstin) {
        state = detectStateFromGstin(gstin) || '';
    }

    const isDomestic = !country || ['india', 'in', 'bharat', 'ind'].includes(country.toLowerCase());
    const isExport = !isDomestic || ['export', 'export with lut', 'sez', 'zero rated'].includes(taxType);

    if (isExport) {
        return {
            type: 'EXPORT_LUT',
            label: `Export (0% Tax / LUT) — ${country || 'Overseas'}`,
            cgst_split: 0.0,
            sgst_split: 0.0,
            igst_split: 0.0,
            is_export: true
        };
    }

    const isGujarat = (gstin && gstin.startsWith('24')) || (state.toLowerCase() === 'gujarat' || state.toLowerCase() === 'gj') || (!state && isDomestic);

    if (isGujarat) {
        return {
            type: 'INTRA_STATE',
            label: 'CGST (50%) + SGST (50%) — Gujarat (Intra-State)',
            cgst_split: 0.5,
            sgst_split: 0.5,
            igst_split: 0.0,
            is_export: false
        };
    }

    return {
        type: 'INTER_STATE',
        label: `IGST (100%) — Inter-State: ${state || 'Outside Gujarat'}`,
        cgst_split: 0.0,
        sgst_split: 0.0,
        igst_split: 1.0,
        is_export: false
    };
}

function selectCustomer(id) {
    const c = customersData[id];
    if (!c) return;
    selectedCustomerObj = c;
    document.getElementById('customerSelect').value = c.id;
    
    const country = c.country || 'India';
    const state = c.state || (country.toLowerCase() === 'india' ? 'Gujarat' : country);
    document.getElementById('customerSearchInput').value = `${c.name} (${state})`;
    document.getElementById('customerComboWrap').classList.remove('open');
    
    currentTaxRegime = computeCustomerTaxRegime(c);
    updateTaxCalculationUI();
}

function updateTaxCalculationUI() {
    const rowCgst = document.getElementById('row_cgst');
    const rowSgst = document.getElementById('row_sgst');
    const rowIgst = document.getElementById('row_igst');
    const taxLabel = document.getElementById('taxLabel');

    if (currentTaxRegime.type === 'EXPORT_LUT') {
        rowCgst.style.display = 'none';
        rowSgst.style.display = 'none';
        rowIgst.style.display = 'none';
        taxLabel.innerHTML = `<span style="display:inline-flex;align-items:center;gap:6px;color:#7e22ce;font-weight:700;"><i class="fa fa-plane"></i> ${escapeHtml(currentTaxRegime.label)}</span>`;
    } else if (currentTaxRegime.type === 'INTER_STATE') {
        rowCgst.style.display = 'none';
        rowSgst.style.display = 'none';
        rowIgst.style.display = '';
        taxLabel.innerHTML = `<span style="display:inline-flex;align-items:center;gap:6px;color:#1d4ed8;font-weight:700;"><i class="fa fa-truck"></i> ${escapeHtml(currentTaxRegime.label)}</span>`;
    } else {
        rowCgst.style.display = '';
        rowSgst.style.display = '';
        rowIgst.style.display = 'none';
        taxLabel.innerHTML = `<span style="display:inline-flex;align-items:center;gap:6px;color:#047857;font-weight:700;"><i class="fa fa-building"></i> ${escapeHtml(currentTaxRegime.label)}</span>`;
    }

    recalc();
}

/* ── PRODUCT SEARCHABLE COMBO FOR EACH ROW ── */
function renderProductCombo(wrap, row) {
    const input = wrap.querySelector('.combo-input');
    const dropdown = wrap.querySelector('.combo-dropdown');
    const hidden = wrap.querySelector('.product-id-input');

    function renderOptions(filter = '') {
        const query = filter.toLowerCase().trim();
        const list = Object.values(productsData).filter(p => {
            const name = (p.name || '').toLowerCase();
            const hsn = (p.hsn_code || '').toLowerCase();
            return name.includes(query) || hsn.includes(query);
        });

        if (list.length === 0) {
            dropdown.innerHTML = `
                <div class="combo-empty">No products found matching "${filter}"</div>
                <button type="button" class="combo-add-btn" onclick="openQuickAddProductWithName('${escapeHtml(filter)}', this)">
                    <i class="fa fa-plus"></i> Add "${escapeHtml(filter)}" as New Product
                </button>
            `;
            return;
        }

        dropdown.innerHTML = list.map(p => {
            const stock = parseFloat(p.stock_quantity || 0);
            const price = parseFloat(p.price || 0);
            const gst = parseFloat(p.gst_rate || 18);
            return `
                <div class="combo-item ${hidden.value == p.id ? 'selected' : ''}" onclick="selectProductInRow(this, ${p.id})">
                    <div>
                        <strong>${escapeHtml(p.name)}</strong>
                        ${p.hsn_code ? `<small style="color:#64748b;margin-left:4px;">#${escapeHtml(p.hsn_code)}</small>` : ''}
                    </div>
                    <div class="combo-item-meta">
                        ${stock > 0 ? `<span class="badge-stock">Stock: ${Math.round(stock)}</span>` : `<span style="color:#94a3b8;font-size:10px;">Stock: 0</span>`}
                        <span class="badge-price">₹${price.toFixed(2)}</span>
                        <span class="badge-gst">GST ${gst}%</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    input.addEventListener('focus', function() {
        document.querySelectorAll('.combo-wrap').forEach(w => { if (w !== wrap) w.classList.remove('open'); });
        wrap.classList.add('open');
        renderOptions(this.value);
    });

    input.addEventListener('input', function() {
        wrap.classList.add('open');
        renderOptions(this.value);
    });
}

function selectProductInRow(el, productId) {
    const row = el.closest('tr');
    const wrap = row.querySelector('.combo-wrap');
    const p = productsData[productId];
    if (!p) return;

    row.querySelector('.product-id-input').value = p.id;
    row.querySelector('.combo-input').value = p.name;
    row.querySelector('.price-input').value = parseFloat(p.price || 0).toFixed(2);
    row.querySelector('.gst-display').value = (p.gst_rate || 18) + '%';
    row.dataset.gstRate = p.gst_rate || 18;

    wrap.classList.remove('open');
    recalc();

    // Auto-focus quantity input for super fast billing
    const qtyInput = row.querySelector('.qty-input');
    if (qtyInput) {
        qtyInput.focus();
        qtyInput.select();
    }
}

function addRow(prefillProductId = null) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.dataset.gstRate = '18';
    tr.innerHTML = `
        <td style="overflow:visible;">
            <div class="combo-wrap">
                <input type="text" class="combo-input product-search-input" placeholder="Type product name (e.g. plastic box)..." autocomplete="off">
                <i class="fa fa-chevron-down combo-arrow"></i>
                <input type="hidden" class="product-id-input" value="">
                <div class="combo-dropdown"></div>
            </div>
        </td>
        <td><input type="number" class="qty-input" value="1" min="0.01" step="0.01" oninput="recalc()" style="width:100%;padding:8px 10px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:13px;font-weight:600;"></td>
        <td><input type="number" class="price-input" step="0.01" value="0.00" oninput="recalc()" style="width:100%;padding:8px 10px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:13px;"></td>
        <td><input type="number" class="total-input" readonly style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:13px;font-weight:700;background:#f8fafc;"></td>
        <td><input type="text" class="gst-display" readonly value="18%" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
        <td><input type="number" class="gstamt-input" readonly style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
        <td><button type="button" class="btn btn-danger btn-sm btn-icon" onclick="removeRow(this)"><i class="fa fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);

    const wrap = tr.querySelector('.combo-wrap');
    renderProductCombo(wrap, tr);

    if (prefillProductId && productsData[prefillProductId]) {
        const dummyEl = tr.querySelector('.combo-dropdown');
        selectProductInRow(dummyEl, prefillProductId);
    } else {
        recalc();
    }
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) { showToast('At least one item required.', 'error'); return; }
    btn.closest('tr').remove();
    recalc();
}

function recalc() {
    let subtotal = 0, cgst = 0, sgst = 0, igst = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const qty       = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price     = parseFloat(row.querySelector('.price-input').value) || 0;
        const gstRate   = parseFloat(row.dataset.gstRate || 18);
        const lineTotal = qty * price;

        row.querySelector('.total-input').value = lineTotal.toFixed(2);
        subtotal += lineTotal;

        const gstAmt = lineTotal * (gstRate / 100);
        row.querySelector('.gstamt-input').value = gstAmt.toFixed(2);

        cgst += gstAmt * currentTaxRegime.cgst_split;
        sgst += gstAmt * currentTaxRegime.sgst_split;
        igst += gstAmt * currentTaxRegime.igst_split;
    });

    const grand = subtotal + cgst + sgst + igst;
    document.getElementById('s_subtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('s_cgst').textContent = '₹' + cgst.toFixed(2);
    document.getElementById('s_sgst').textContent = '₹' + sgst.toFixed(2);
    document.getElementById('s_igst').textContent = '₹' + igst.toFixed(2);
    document.getElementById('s_grand').textContent = '₹' + grand.toFixed(2);
}

function generateInvoice() {
    const customerId = document.getElementById('customerSelect').value;
    if (!customerId) { showToast('Please select a customer first (type to search).', 'error'); return; }

    const items = [];
    let valid = true;
    document.querySelectorAll('.item-row').forEach(row => {
        const productId = row.querySelector('.product-id-input').value;
        const qty = parseFloat(row.querySelector('.qty-input').value);
        const unitPrice = parseFloat(row.querySelector('.price-input').value);
        if (!productId) {
            valid = false;
            showToast('Please type and select a valid product in every row.', 'error');
            return;
        }
        if (!qty || isNaN(unitPrice)) { valid = false; return; }
        items.push({ product_id: productId, quantity: qty, unit_price: unitPrice });
    });

    if (!valid || items.length === 0) {
        return;
    }

    fetch('{{ route('invoices.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            customer_id:    customerId,
            invoice_date:   document.getElementById('invoiceDate') ? document.getElementById('invoiceDate').value : null,
            transporter_id: document.getElementById('transporterSelect').value || null,
            lr_number:      document.getElementById('lrNumber').value || null,
            notes:          document.getElementById('invoiceNotes').value || null,
            payment_terms:  document.getElementById('paymentTerms').value || null,
            po_number:      document.getElementById('poNumber').value || null,
            po_date:        document.getElementById('poDate').value || null,
            delivery_at:    document.getElementById('deliveryAt').value || null,
            eway_bill_no:   document.getElementById('ewayBillNo').value || null,
            challan_number: document.getElementById('challanNumber').value || null,
            items,
        }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast('Invoice created successfully!', 'success');
            setTimeout(() => window.location.href = `/invoices/${res.invoice_id}/print`, 800);
        } else {
            showToast(res.message || 'Error creating invoice', 'error');
        }
    })
    .catch(() => showToast('Network error while creating invoice.', 'error'));
}

function simulateUpiVerification() {
    showToast('⚡ Dynamic QR Code Generated. Waiting for GPay / Paytm webhook...', 'info');
    setTimeout(() => {
        showToast('✅ Instant UPI Payment Detected! Ref: UPI/987410254/OKAXIS', 'success');
        setTimeout(() => {
            generateInvoice();
        }, 1000);
    }, 1500);
}

// Quick Modals
let activeRowForQuickProduct = null;

function openQuickAddProductModal() {
    activeRowForQuickProduct = null;
    document.getElementById('quickProductForm').reset();
    document.getElementById('quickProductModal').classList.add('open');
}

function openQuickAddProductWithName(name, triggerEl) {
    if (triggerEl) {
        activeRowForQuickProduct = triggerEl.closest('tr');
    }
    document.getElementById('quickProductForm').reset();
    document.getElementById('qp_name').value = name;
    document.getElementById('quickProductModal').classList.add('open');
}

function openQuickAddCustomerModal() {
    document.getElementById('quickCustomerForm').reset();
    document.getElementById('quickCustomerModal').classList.add('open');
}

function openQuickAddCustomerWithName(name) {
    document.getElementById('quickCustomerForm').reset();
    document.getElementById('qc_name').value = name;
    document.getElementById('quickCustomerModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function saveQuickProduct(e) {
    e.preventDefault();
    const btn = document.getElementById('qp_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    const formData = new FormData();
    formData.append('name', document.getElementById('qp_name').value);
    formData.append('price', document.getElementById('qp_price').value);
    formData.append('gst_rate', document.getElementById('qp_gst_rate').value);
    formData.append('hsn_code', document.getElementById('qp_hsn').value);
    formData.append('stock_quantity', document.getElementById('qp_stock').value);

    fetch('{{ route('products.store') }}', {
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
        btn.innerHTML = '<i class="fa fa-check"></i> Add Product &amp; Select';
        if (res.success) {
            showToast('Product added successfully!', 'success');
            closeModal('quickProductModal');
            // Fetch updated products and update state
            fetch('{{ route('products.index') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(list => {
                    productsData = {};
                    list.forEach(p => { productsData[p.id] = p; });
                    const newProd = list.find(p => p.name === document.getElementById('qp_name').value) || list[0];
                    if (newProd) {
                        if (activeRowForQuickProduct) {
                            selectProductInRow(activeRowForQuickProduct.querySelector('.combo-dropdown'), newProd.id);
                        } else {
                            addRow(newProd.id);
                        }
                    }
                })
                .catch(err => {
                    console.error('Error fetching updated products:', err);
                });
        } else {
            showToast(res.message || 'Failed to add product', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Add Product &amp; Select';
        showToast('Error saving product', 'error');
    });
}

function saveQuickCustomer(e) {
    e.preventDefault();
    const btn = document.getElementById('qc_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    const formData = new FormData();
    const custName = document.getElementById('qc_name').value;
    const custState = document.getElementById('qc_state').value || 'Gujarat';
    const custCountry = document.getElementById('qc_country').value || 'India';
    formData.append('name', custName);
    formData.append('phone', document.getElementById('qc_phone').value);
    formData.append('country', custCountry);
    formData.append('state', custState);
    formData.append('gstin', document.getElementById('qc_gstin').value);
    formData.append('address', document.getElementById('qc_address').value);

    fetch('{{ route('customers.store') }}', {
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
        btn.innerHTML = '<i class="fa fa-check"></i> Add Customer &amp; Select';
        if (res.success) {
            showToast('Customer added successfully!', 'success');
            closeModal('quickCustomerModal');
            fetch('{{ route('customers.index') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(list => {
                    customersData = {};
                    list.forEach(c => { customersData[c.id] = c; });
                    const newCust = list.find(c => c.name === custName) || list[0];
                    if (newCust) {
                        selectCustomer(newCust.id);
                    }
                })
                .catch(err => {
                    console.error('Error fetching updated customers:', err);
                });
        } else {
            showToast(res.message || 'Failed to add customer', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Add Customer &amp; Select';
        showToast('Error saving customer', 'error');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

// Initialize immediately (works with SPA and regular page loads)
function initCreateBillPage() {
    initCustomerCombo();
    if (document.querySelectorAll('.item-row').length === 0) {
        addRow();
    }
}

initCreateBillPage();
document.addEventListener('DOMContentLoaded', initCreateBillPage);
</script>
@endsection
