// Billing specific logic
let billingItems = [];
let itemIndex = 0;

function addBillingRow() {
    const tbody = document.getElementById('billing-items');
    const tr = document.createElement('tr');
    const currentIndex = itemIndex++;
    
    tr.innerHTML = `
        <td>
            <select class="form-control product-select" onchange="fetchProductDetails(this, ${currentIndex})">
                <option value="">Select Product...</option>
                ${window.productOptions}
            </select>
        </td>
        <td>
            <input type="number" class="form-control qty-input" value="1" min="1" onchange="calculateTotals()">
        </td>
        <td>
            <input type="number" class="form-control price-input" readonly>
        </td>
        <td>
            <input type="number" class="form-control gst-input" readonly>
        </td>
        <td>
            <span class="item-total">0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateTotals();
}

async function fetchProductDetails(selectElement, index) {
    const productId = selectElement.value;
    const tr = selectElement.closest('tr');
    const priceInput = tr.querySelector('.price-input');
    const gstInput = tr.querySelector('.gst-input');
    
    if (!productId) {
        priceInput.value = '';
        gstInput.value = '';
        calculateTotals();
        return;
    }
    
    try {
        const response = await fetch(`api/get_product.php?id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            priceInput.value = data.product.price;
            gstInput.value = data.product.gst_rate;
            calculateTotals();
        }
    } catch (error) {
        console.error('Error fetching product:', error);
    }
}

function calculateTotals() {
    let subtotal = 0;
    let totalCgst = 0;
    let totalSgst = 0;
    let totalIgst = 0;
    
    // Check customer state for IGST logic
    const customerSelect = document.getElementById('customer_id');
    const selectedOption = customerSelect.options[customerSelect.selectedIndex];
    const customerState = selectedOption ? (selectedOption.dataset.state || '').toLowerCase().trim() : '';
    const isIgst = customerState && customerState !== 'gujarat';

    const rows = document.querySelectorAll('#billing-items tr');
    
    rows.forEach(tr => {
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const gstRate = parseFloat(tr.querySelector('.gst-input').value) || 0;
        
        const itemTotal = qty * price;
        tr.querySelector('.item-total').innerText = itemTotal.toFixed(2);
        
        subtotal += itemTotal;
        const gstAmount = (itemTotal * gstRate) / 100;
        
        if (isIgst) {
            totalIgst += gstAmount;
        } else {
            totalCgst += gstAmount / 2;
            totalSgst += gstAmount / 2;
        }
    });
    
    const grandTotal = subtotal + totalCgst + totalSgst + totalIgst;
    
    document.getElementById('display-subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('display-cgst').innerText = totalCgst.toFixed(2);
    document.getElementById('display-sgst').innerText = totalSgst.toFixed(2);
    document.getElementById('display-igst').innerText = totalIgst.toFixed(2);
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
        const qty = tr.querySelector('.qty-input').value;
        
        if (productId && qty > 0) {
            items.push({ id: productId, quantity: qty });
        }
    });
    
    if (items.length === 0) {
        showToast('Please add at least one product', 'error');
        return;
    }
    
    const payload = {
        customer_id: customer_id,
        items: items
    };
    
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
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Server error', 'error');
    }
}
