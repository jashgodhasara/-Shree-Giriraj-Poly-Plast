@extends('layouts.app')

@section('title', 'Create Purchase Order - Shree Giriraj Poly Plast')
@section('page-title', 'Create Purchase Order')

@section('content')
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;">New Vendor Purchase Order</h2>
        <p class="text-muted" style="font-size: 13px;">Generate an official PO bill for raw materials or additives from suppliers</p>
    </div>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline">
        <i class="fa fa-arrow-left"></i> Back to Orders
    </a>
</div>

<form id="poForm" onsubmit="savePurchaseOrder(event)">
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fa fa-truck-field"></i> Supplier & Order Details</h3>
        </div>
        <div class="card-body">
            <div class="form-row cols-3 mb-3">
                <div class="form-group">
                    <label>Select Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required onchange="calculateTotals()">
                        <option value="">-- Select Vendor / Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-state="{{ $s->state }}">
                                {{ $s->name }} {{ $s->gstin ? '('.$s->gstin.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>PO Date <span class="text-danger">*</span></label>
                    <input type="date" name="po_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" class="form-control">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label>Payment Terms (e.g. 30 Days Credit, Immediate)</label>
                    <input type="text" name="payment_terms" class="form-control" placeholder="e.g. 30 Days Credit">
                </div>
                <div class="form-group">
                    <label>Delivery / Shipping Address</label>
                    <input type="text" name="delivery_address" class="form-control" placeholder="Factory Address, Ahmedabad, Gujarat">
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-boxes-stacked"></i> Material Items</h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="addPoRow()">
                <i class="fa fa-plus"></i> Add Item Row
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Material / Component</th>
                            <th style="width: 120px;">HSN Code</th>
                            <th style="width: 100px;">Qty</th>
                            <th style="width: 130px;">Unit Price (₹)</th>
                            <th style="width: 100px;">GST %</th>
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
                    <textarea name="notes" class="form-control" rows="3" placeholder="Special instructions for the vendor..."></textarea>
                </div>
            </div>
        </div>

        <div class="card" style="width: 380px; margin-bottom: 0;">
            <div class="card-body">
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <strong>₹<span id="disp-subtotal">0.00</span></strong>
                </div>
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">CGST:</span>
                    <strong>₹<span id="disp-cgst">0.00</span></strong>
                </div>
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">SGST:</span>
                    <strong>₹<span id="disp-sgst">0.00</span></strong>
                </div>
                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">IGST:</span>
                    <strong>₹<span id="disp-igst">0.00</span></strong>
                </div>
                <div class="divider"></div>
                <div class="d-flex justify-between align-center">
                    <span style="font-size: 16px; font-weight: 700; color: var(--primary);">Grand Total:</span>
                    <span style="font-size: 22px; font-weight: 800; color: var(--primary);">₹<span id="disp-total">0.00</span></span>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary w-full" style="padding: 12px; font-size: 15px;">
                        <i class="fa fa-check-circle"></i> Save &amp; Generate Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const materialsList = @json($materials);
let rowIndex = 0;

function getMaterialOptions() {
    let html = '<option value="">Select Material...</option>';
    materialsList.forEach(m => {
        const unit = m.unit ? ` (${m.unit})` : '';
        html += `<option value="${m.id}" data-type="${m.type}" data-unit="${m.unit || ''}">${m.name} - ${m.type}${unit}</option>`;
    });
    return html;
}

function addPoRow() {
    const tbody = document.getElementById('po-items-body');
    const tr = document.createElement('tr');
    const id = rowIndex++;

    tr.innerHTML = `
        <td>
            <select name="items[${id}][material_id]" class="form-control material-select" required onchange="calculateTotals()">
                ${getMaterialOptions()}
            </select>
        </td>
        <td>
            <input type="text" name="items[${id}][hsn_code]" class="form-control hsn-input" placeholder="3901">
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
    const supplierState  = selectedOption ? (selectedOption.dataset.state || '').toLowerCase().trim() : '';
    const isIgst = supplierState && supplierState !== 'gujarat';

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
        tr.querySelector('.row-total').innerText = lineTotal.toFixed(2);

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
    const form = e.target;
    const formData = new FormData(form);

    try {
        const res = await fetch('{{ route("purchase-orders.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = `/purchase-orders/${data.po_id}/print`;
            }, 800);
        } else {
            showToast(data.message || 'Error creating Purchase Order', 'error');
        }
    } catch(err) {
        showToast('Server error while saving Purchase Order', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    addPoRow();
});
</script>
@endsection
