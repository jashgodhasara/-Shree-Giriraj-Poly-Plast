let billingItemIndex = 0;

function onCustomerChange(select) {
    const opt = select.options[select.selectedIndex];
    const infoCard = document.getElementById('customerInfoCard');
    if (!opt || !select.value) {
        infoCard.style.display = 'none';
        calculateTotals();
        return;
    }

    const name = opt.dataset.name || '—';
    const phone = opt.dataset.phone || '—';
    const gstin = opt.dataset.gstin || 'Unregistered';
    const state = opt.dataset.state || 'Gujarat';
    const address = opt.dataset.address || '—';

    document.getElementById('dispCustName').innerText = name;
    document.getElementById('dispCustGstin').innerText = gstin;
    document.getElementById('dispCustPhone').innerText = phone;
    document.getElementById('dispCustState').innerText = state;
    document.getElementById('dispCustAddress').innerText = address;

    const isIgst = state.toLowerCase().trim() !== 'gujarat' && state.toLowerCase().trim() !== '24';
    const badge = document.getElementById('dispCustGstBadge');
    if (isIgst) {
        badge.innerText = 'Inter-State (IGST Applicable)';
        badge.className = 'badge-gst-type badge-inter';
        document.getElementById('row-cgst').style.display = 'none';
        document.getElementById('row-sgst').style.display = 'none';
        document.getElementById('row-igst').style.display = 'flex';
    } else {
        badge.innerText = 'Intra-State (CGST + SGST Applicable)';
        badge.className = 'badge-gst-type badge-intra';
        document.getElementById('row-cgst').style.display = 'flex';
        document.getElementById('row-sgst').style.display = 'flex';
        document.getElementById('row-igst').style.display = 'none';
    }

    infoCard.style.display = 'block';
    calculateTotals();
}

function buildProductOptions() {
    let html = '<option value="">-- Select Product --</option>';
    if (window.productsList && window.productsList.length > 0) {
        window.productsList.forEach(p => {
            const safeName = p.name.replace(/</g, "&lt;").replace(/>/g, "&gt;");
            html += `<option value="${p.id}" data-price="${p.price||0}" data-rate-kg="${p.rate_per_kg||0}" data-unit="${p.unit||'PCS'}" data-gst="${p.gst_rate||18}" data-hsn="${p.hsn_code||'392690'}">${safeName} (Stock: ${p.stock_quantity||0} ${p.unit||'PCS'})</option>`;
        });
    }
    return html;
}

function addBillingRow() {
    const tbody = document.getElementById('billing-items');
    const currentIndex = billingItemIndex++;
    const tr = document.createElement('tr');
    tr.id = `bill_row_${currentIndex}`;
    
    tr.innerHTML = `
        <td>
            <select class="form-control product-select" required onchange="onProductSelectChange(this, ${currentIndex})">
                ${buildProductOptions()}
            </select>
        </td>
        <td>
            <select class="form-control unit-select">
                <option value="PCS">PCS</option>
                <option value="KG">KG</option>
                <option value="MTR">MTR</option>
                <option value="Meter">Meter</option>
                <option value="Bag">Bag</option>
                <option value="Roll">Roll</option>
                <option value="Bundle">Bundle</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control qty-input" value="1" min="0.01" required oninput="calculateTotals()">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control rate-kg-input" placeholder="0.00" oninput="onBillRateKgChange(${currentIndex})">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control price-input" placeholder="0.00" required oninput="calculateTotals()">
        </td>
        <td>
            <select class="form-control gst-input" onchange="calculateTotals()">
                <option value="18">18%</option>
                <option value="12">12%</option>
                <option value="5">5%</option>
                <option value="28">28%</option>
                <option value="0">0%</option>
            </select>
        </td>
        <td style="font-weight:700; color:var(--text-main); vertical-align:middle;">
            ₹<span class="item-total">0.00</span>
        </td>
        <td style="text-align:center; vertical-align:middle;">
            <button type="button" class="btn btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="removeRow(this)">&times;</button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateTotals();
}

function onProductSelectChange(selectElement, index) {
    const tr = selectElement.closest('tr');
    const opt = selectElement.options[selectElement.selectedIndex];
    if (!opt || !selectElement.value) {
        tr.querySelector('.price-input').value = '';
        tr.querySelector('.rate-kg-input').value = '';
        calculateTotals();
        return;
    }

    const price = opt.dataset.price || '0';
    const rateKg = opt.dataset.rateKg || '';
    const unit = opt.dataset.unit || 'PCS';
    const gst = opt.dataset.gst || '18';

    tr.querySelector('.price-input').value = price > 0 ? price : '';
    tr.querySelector('.rate-kg-input').value = rateKg > 0 ? rateKg : '';
    tr.querySelector('.unit-select').value = unit;
    tr.querySelector('.gst-input').value = gst;

    calculateTotals();
}

function onBillRateKgChange(index) {
    const tr = document.getElementById(`bill_row_${index}`);
    if (!tr) return;
    const rateKg = parseFloat(tr.querySelector('.rate-kg-input').value) || 0;
    const unit = tr.querySelector('.unit-select').value;
    if (rateKg > 0 && (unit.toLowerCase() === 'kg' || !tr.querySelector('.price-input').value)) {
        tr.querySelector('.price-input').value = rateKg;
    }
    calculateTotals();
}

function round2(val) {
    return Math.round((val + Number.EPSILON) * 100) / 100;
}

function calculateTotals() {
    let subtotal = 0;
    let totalCgst = 0;
    let totalSgst = 0;
    let totalIgst = 0;
    
    // Check customer state for IGST logic
    const customerSelect = document.getElementById('customer_id');
    const selectedOption = customerSelect ? customerSelect.options[customerSelect.selectedIndex] : null;
    const customerState = selectedOption ? (selectedOption.dataset.state || 'Gujarat').toLowerCase().trim() : 'gujarat';
    const isIgst = customerState !== 'gujarat' && customerState !== '24';

    const rows = document.querySelectorAll('#billing-items tr');
    
    rows.forEach(tr => {
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const gstRate = parseFloat(tr.querySelector('.gst-input').value) || 0;
        
        const lineTaxable = round2(qty * price);
        const gstAmount = round2((lineTaxable * gstRate) / 100);
        const lineTotal = lineTaxable + gstAmount;

        tr.querySelector('.item-total').innerText = lineTotal.toFixed(2);
        
        subtotal += lineTaxable;
        
        if (isIgst) {
            totalIgst += gstAmount;
        } else {
            totalCgst += round2(gstAmount / 2);
            totalSgst += round2(gstAmount / 2);
        }
    });
    
    const exactTotal = subtotal + totalCgst + totalSgst + totalIgst;
    const grandTotal = Math.round(exactTotal);
    const roundOff = round2(grandTotal - exactTotal);
    
    document.getElementById('display-subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('display-cgst').innerText = totalCgst.toFixed(2);
    document.getElementById('display-sgst').innerText = totalSgst.toFixed(2);
    document.getElementById('display-igst').innerText = totalIgst.toFixed(2);
    document.getElementById('display-round-off').innerText = roundOff.toFixed(2);
    document.getElementById('display-total').innerText = grandTotal.toFixed(2);
}

async function saveInvoice() {
    const customer_id = document.getElementById('customer_id').value;
    if (!customer_id) {
        showToast('Please select a customer', 'error');
        return;
    }
    
    const items = [];
    document.querySelectorAll('#billing-items tr').forEach(tr => {
        const productId = tr.querySelector('.product-select').value;
        const unit = tr.querySelector('.unit-select').value;
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const rateKg = parseFloat(tr.querySelector('.rate-kg-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const gstRate = parseFloat(tr.querySelector('.gst-input').value) || 0;
        
        if (productId && qty > 0) {
            items.push({
                id: productId,
                unit: unit,
                quantity: qty,
                rate_per_kg: rateKg,
                unit_price: price,
                gst_rate: gstRate
            });
        }
    });
    
    if (items.length === 0) {
        showToast('Please add at least one product with valid quantity', 'error');
        return;
    }
    
    const payload = {
        customer_id: customer_id,
        date: document.getElementById('invoice_date').value,
        po_number: document.getElementById('po_number').value,
        delivery_at: document.getElementById('delivery_at').value,
        vehicle_number: document.getElementById('vehicle_number').value,
        challan_number: document.getElementById('challan_number').value,
        payment_terms: document.getElementById('payment_terms').value,
        notes: document.getElementById('invoice_notes').value,
        items: items
    };

    const submitBtn = document.getElementById('btnGenerateInvoice');
    submitBtn.disabled = true;
    submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Generating Invoice...";
    
    try {
        const response = await fetch('api/save_invoice.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('Invoice generated successfully!');
            setTimeout(() => {
                window.location.href = `print-invoice.php?id=${data.invoice_id}`;
            }, 800);
        } else {
            showToast(data.message || 'Error generating invoice', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = "<i class='bx bx-receipt'></i> Generate &amp; Print Invoice";
        }
    } catch (error) {
        showToast('Server connection error', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = "<i class='bx bx-receipt'></i> Generate &amp; Print Invoice";
    }
}

