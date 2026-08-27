<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products Master - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .product-img-thumb {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid var(--border);
        }
        .product-img-none {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            background: #e2e8f0;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .category-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #e0e7ff;
            color: #4338ca;
        }
        .category-badge.meter {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .action-btns {
            display: flex;
            gap: 6px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Products Master</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">Manage Finished Goods, Moulded Items, Meter Category & Pricing</p>
            </div>
            <button class="btn btn-primary" onclick="openAddProductModal()">+ Add Product</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Category</th>
                            <th>Name & Details</th>
                            <th>Unit</th>
                            <th>Rate / Unit</th>
                            <th>Rate / KG</th>
                            <th>HSN Code</th>
                            <th>GST (%)</th>
                            <th>Current Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): 
                            $isMeter = (stripos($p['category_name'] ?? '', 'meter') !== false || stripos($p['unit'] ?? '', 'mtr') !== false || stripos($p['unit'] ?? '', 'meter') !== false);
                        ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td>
                                <?php if(!empty($p['image'])): ?>
                                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="Photo" class="product-img-thumb">
                                <?php else: ?>
                                    <div class="product-img-none"><i class='bx bx-image'></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="category-badge <?= $isMeter ? 'meter' : '' ?>">
                                    <?= htmlspecialchars($p['category_name'] ?: ($isMeter ? 'Meter Category' : 'Finished Goods')) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--text-main);"><?= htmlspecialchars($p['name']) ?></div>
                                <?php if (!empty($p['weight_per_piece']) && $p['weight_per_piece'] > 0): ?>
                                    <small style="color:var(--text-muted);">Weight: <?= number_format($p['weight_per_piece'], 2) ?> g/pc</small>
                                <?php endif; ?>
                            </td>
                            <td><span style="font-weight:600;"><?= htmlspecialchars($p['unit'] ?: 'PCS') ?></span></td>
                            <td style="color:var(--primary); font-weight:bold;">₹<?= number_format($p['price'], 2) ?></td>
                            <td style="color:#059669; font-weight:600;">
                                <?= (!empty($p['rate_per_kg']) && $p['rate_per_kg'] > 0) ? '₹' . number_format($p['rate_per_kg'], 2) : '—' ?>
                            </td>
                            <td><?= htmlspecialchars($p['hsn_code'] ?: '392690') ?></td>
                            <td><?= htmlspecialchars($p['gst_rate'] ?? 18) ?>%</td>
                            <td style="font-weight:bold; color: <?= ($p['stock_quantity'] > 0 ? '#16a34a' : '#dc2626') ?>;">
                                <?= number_format($p['stock_quantity'] ?? 0, 2) ?> <?= htmlspecialchars($p['unit'] ?: 'PCS') ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-secondary" style="padding: 5px 10px; font-size:0.78rem;" 
                                            onclick='openEditProductModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class='bx bx-edit'></i> Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="11" class="text-center" style="padding:30px; color:var(--text-muted);">No products registered yet. Click "+ Add Product" to create one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Product Modal (Add / Edit) -->
    <div class="modal-backdrop" id="productModal">
        <div class="modal" style="max-width: 540px;">
            <div class="modal-header">
                <h2 id="modalProductTitle">Add New Product</h2>
                <button class="close-btn" onclick="closeModal('productModal')">&times;</button>
            </div>
            <form id="productForm" onsubmit="saveProduct(event)" enctype="multipart/form-data">
                <input type="hidden" name="id" id="prod_id" value="">
                
                <div class="form-group">
                    <label>Product / Item Name *</label>
                    <input type="text" name="name" id="prod_name" class="form-control" required placeholder="e.g. Plastic Soup Bowl, PVC Meter Pipe">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_name" id="prod_category" class="form-control" onchange="onCategoryChange(this.value)">
                            <option value="Finished Goods">Finished Goods</option>
                            <option value="Meter Category">Meter Category (Length/Pipes)</option>
                            <option value="Moulded Products">Moulded Products</option>
                            <option value="Raw Materials">Raw Materials</option>
                            <option value="Additives & Masterbatch">Additives & Masterbatch</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unit of Measurement</label>
                        <select name="unit" id="prod_unit" class="form-control">
                            <option value="PCS">PCS (Pieces)</option>
                            <option value="KG">KG (Kilograms)</option>
                            <option value="MTR">MTR (Meters)</option>
                            <option value="Meter">Meter</option>
                            <option value="Roll">Roll</option>
                            <option value="Bag">Bag</option>
                            <option value="Bundle">Bundle</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Rate / Unit Price (₹) *</label>
                        <input type="number" step="0.01" name="price" id="prod_price" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Rate per KG (₹) <small style="color:var(--text-muted);">(Optional)</small></label>
                        <input type="number" step="0.01" name="rate_per_kg" id="prod_rate_per_kg" class="form-control" placeholder="0.00" onkeyup="calcPriceFromRateKg()">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Weight per Piece (Grams)</label>
                        <input type="number" step="0.01" name="weight_per_piece" id="prod_weight_per_piece" class="form-control" placeholder="0.00" onkeyup="calcPriceFromRateKg()">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" step="0.01" name="stock_quantity" id="prod_stock_quantity" class="form-control" placeholder="0.00" value="0">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>HSN / SAC Code</label>
                        <input type="text" name="hsn_code" id="prod_hsn" class="form-control" value="392690" placeholder="e.g. 392690">
                    </div>
                    <div class="form-group">
                        <label>GST Rate (%)</label>
                        <select name="gst_rate" id="prod_gst_rate" class="form-control">
                            <option value="18">18% (Standard GST)</option>
                            <option value="12">12%</option>
                            <option value="5">5%</option>
                            <option value="28">28%</option>
                            <option value="0">0% (Nil / Exempt)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description / Notes</label>
                    <textarea name="description" id="prod_description" class="form-control" rows="2" placeholder="Item specifications, grade, remarks..."></textarea>
                </div>

                <div class="form-group">
                    <label>Product Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="text-right mt-4" style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#334155;" onclick="closeModal('productModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveProduct">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    function openAddProductModal() {
        document.getElementById('productForm').reset();
        document.getElementById('prod_id').value = '';
        document.getElementById('prod_hsn').value = '392690';
        document.getElementById('prod_gst_rate').value = '18';
        document.getElementById('modalProductTitle').innerText = 'Add New Product';
        document.getElementById('btnSaveProduct').innerText = 'Save Product';
        openModal('productModal');
    }

    function openEditProductModal(p) {
        document.getElementById('productForm').reset();
        document.getElementById('prod_id').value = p.id;
        document.getElementById('prod_name').value = p.name || '';
        document.getElementById('prod_category').value = p.category_name || 'Finished Goods';
        document.getElementById('prod_unit').value = p.unit || 'PCS';
        document.getElementById('prod_price').value = p.price || '';
        document.getElementById('prod_rate_per_kg').value = (p.rate_per_kg && p.rate_per_kg > 0) ? p.rate_per_kg : '';
        document.getElementById('prod_weight_per_piece').value = (p.weight_per_piece && p.weight_per_piece > 0) ? p.weight_per_piece : '';
        document.getElementById('prod_stock_quantity').value = p.stock_quantity ?? 0;
        document.getElementById('prod_hsn').value = p.hsn_code || '392690';
        document.getElementById('prod_gst_rate').value = p.gst_rate || '18';
        document.getElementById('prod_description').value = p.description || '';
        document.getElementById('modalProductTitle').innerText = 'Edit Product #' + p.id;
        document.getElementById('btnSaveProduct').innerText = 'Update Product';
        openModal('productModal');
    }

    function onCategoryChange(cat) {
        const unitSelect = document.getElementById('prod_unit');
        if (cat === 'Meter Category') {
            unitSelect.value = 'MTR';
        } else if (cat === 'Raw Materials') {
            unitSelect.value = 'KG';
        }
    }

    function calcPriceFromRateKg() {
        const rateKg = parseFloat(document.getElementById('prod_rate_per_kg').value) || 0;
        const weightGrams = parseFloat(document.getElementById('prod_weight_per_piece').value) || 0;
        const priceInput = document.getElementById('prod_price');
        
        if (rateKg > 0 && weightGrams > 0 && (!priceInput.value || priceInput.value == '0' || priceInput.dataset.autoCalc === 'true')) {
            const calculatedPrice = (rateKg * (weightGrams / 1000)).toFixed(2);
            priceInput.value = calculatedPrice;
            priceInput.dataset.autoCalc = 'true';
        }
    }

    async function saveProduct(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSaveProduct');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';
        
        try {
            const res = await fetch('api/save_product.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Product saved successfully!');
                closeModal('productModal');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.message || 'Error saving product', 'error');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Product';
            }
        } catch(err) {
            showToast('Error connecting to server', 'error');
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Product';
        }
    }
    </script>
</body>
</html>

