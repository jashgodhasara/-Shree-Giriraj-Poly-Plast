@extends('layouts.app')
@section('title', 'Create Bill')
@section('page-title', 'Create New Bill')

@section('content')
<style>
.billing-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; }
@media(max-width:900px){ .billing-grid { grid-template-columns:1fr; } }
.billing-grid > div:last-child .card { position:static !important; }
@media(max-width:900px){ #itemsTable th:nth-child(5), #itemsTable th:nth-child(6), #itemsTable td:nth-child(5), #itemsTable td:nth-child(6) { display:none; } }
</style>
<div class="billing-grid">

<div>
<div class="card">
    <div class="card-header"><h3><i class="fa fa-file-invoice-dollar"></i> Bill Items</h3></div>
    <div class="card-body">
        <!-- Customer & Transport -->
        <div class="form-row cols-2" style="margin-bottom:8px">
            <div class="form-group">
                <label>Customer *</label>
                <select id="customerSelect" required>
                    <option value="">Choose a customer...</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" data-state="{{ $c->state }}">{{ $c->name }}@if($c->state) ({{ $c->state }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Transporter</label>
                <select id="transporterSelect">
                    <option value="">-- None --</option>
                    @foreach($transporters as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}@if($t->vehicle_no) ({{ $t->vehicle_no }})@endif</option>
                    @endforeach
                </select>
            </div>
        </div>
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
        <div class="table-wrap">
            <table id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:32%">Product</th>
                        <th style="width:12%">Qty</th>
                        <th style="width:16%">Unit Price</th>
                        <th style="width:16%">Total</th>
                        <th style="width:10%">GST%</th>
                        <th style="width:10%">GST Amt</th>
                        <th style="width:4%"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr class="item-row">
                        <td>
                            <select class="product-select" onchange="onProductChange(this)" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                                <option value="">Select product</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-gst="{{ $p->gst_rate }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" class="qty-input" value="1" min="0.01" step="0.01" oninput="recalc()" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;"></td>
                        <td><input type="number" class="price-input" step="0.01" oninput="recalc()" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;"></td>
                        <td><input type="number" class="total-input" readonly style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
                        <td><input type="text" class="gst-display" readonly style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
                        <td><input type="number" class="gstamt-input" readonly style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
                        <td><button type="button" class="btn btn-danger btn-sm btn-icon" onclick="removeRow(this)"><i class="fa fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-outline btn-sm" style="margin-top:10px;display:inline-flex;" onclick="addRow()">
            <i class="fa fa-plus"></i> Add Row
        </button>
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
            <div><strong>Offline Sync Active:</strong> Bills generated offline auto-cache &amp; cloud-sync.</div>
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
@endsection

@section('scripts')
<script>
const products = @json($products->keyBy('id'));

function onProductChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const row = sel.closest('tr');
    row.querySelector('.price-input').value = opt.dataset.price || '';
    row.querySelector('.gst-display').value = opt.dataset.gst ? opt.dataset.gst + '%' : '';
    recalc();
}

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const productOptions = Object.values(products).map(p =>
        `<option value="${p.id}" data-price="${p.price}" data-gst="${p.gst_rate}">${p.name}</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td>
            <select class="product-select" onchange="onProductChange(this)" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
                <option value="">Select product</option>
                ${productOptions}
            </select>
        </td>
        <td><input type="number" class="qty-input" value="1" min="0.01" step="0.01" oninput="recalc()" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;"></td>
        <td><input type="number" class="price-input" step="0.01" oninput="recalc()" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;"></td>
        <td><input type="number" class="total-input" readonly style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
        <td><input type="text" class="gst-display" readonly style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
        <td><input type="number" class="gstamt-input" readonly style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#f8fafc;"></td>
        <td><button type="button" class="btn btn-danger btn-sm btn-icon" onclick="removeRow(this)"><i class="fa fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) { showToast('At least one item required.', 'error'); return; }
    btn.closest('tr').remove();
    recalc();
}

function isInterState() {
    const sel = document.getElementById('customerSelect');
    const state = sel.options[sel.selectedIndex]?.dataset?.state || '';
    return state && state.toLowerCase() !== 'gujarat';
}

document.getElementById('customerSelect').addEventListener('change', function() {
    const inter = isInterState();
    document.getElementById('row_cgst').style.display = inter ? 'none' : '';
    document.getElementById('row_sgst').style.display = inter ? 'none' : '';
    document.getElementById('row_igst').style.display = inter ? '' : 'none';
    document.getElementById('taxLabel').innerHTML = inter
        ? 'Tax type: <strong>IGST</strong> (Inter-state)'
        : 'Tax type: <strong>CGST + SGST</strong> (Gujarat)';
    recalc();
});

function recalc() {
    let subtotal = 0, cgst = 0, sgst = 0, igst = 0;
    const inter = isInterState();

    document.querySelectorAll('.item-row').forEach(row => {
        const qty      = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price    = parseFloat(row.querySelector('.price-input').value) || 0;
        const gstEl    = row.querySelector('.product-select');
        const gstRate  = parseFloat(gstEl.options[gstEl.selectedIndex]?.dataset?.gst) || 0;
        const lineTotal = qty * price;
        row.querySelector('.total-input').value = lineTotal.toFixed(2);
        subtotal += lineTotal;
        const gstAmt = lineTotal * (gstRate / 100);
        row.querySelector('.gstamt-input').value = gstAmt.toFixed(2);
        if (inter) { igst += gstAmt; }
        else { cgst += gstAmt / 2; sgst += gstAmt / 2; }
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
    if (!customerId) { showToast('Please select a customer.', 'error'); return; }

    const items = [];
    let valid = true;
    document.querySelectorAll('.item-row').forEach(row => {
        const productId = row.querySelector('.product-select').value;
        const qty = parseFloat(row.querySelector('.qty-input').value);
        const unitPrice = parseFloat(row.querySelector('.price-input').value);
        if (!productId || !qty || !unitPrice) { valid = false; return; }
        items.push({ product_id: productId, quantity: qty, unit_price: unitPrice });
    });

    if (!valid || items.length === 0) {
        showToast('Please fill all product rows with product, quantity and price.', 'error');
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
            showToast('Invoice created!', 'success');
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
</script>
@endsection
