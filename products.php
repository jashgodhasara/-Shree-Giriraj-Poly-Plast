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
    <title>Products - Shree Giriraj Poly Plast</title>
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Products</h1>
            <button class="btn btn-primary" onclick="openModal('productModal')">+ Add Product</button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>HSN Code</th>
                            <th>GST Rate (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td>
                                <?php if(!empty($p['image'])): ?>
                                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="Photo" class="product-img-thumb">
                                <?php else: ?>
                                    <div class="product-img-none"><i class='bx bx-image'></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:600"><?= htmlspecialchars($p['name']) ?></td>
                            <td style="color:var(--primary-color); font-weight:bold;">₹<?= number_format($p['price'], 2) ?></td>
                            <td><?= htmlspecialchars($p['hsn_code']) ?></td>
                            <td><?= htmlspecialchars($p['gst_rate']) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div class="modal-backdrop" id="productModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="productForm" onsubmit="saveProduct(event)" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Plastic Soup Bowl">
                </div>
                <div class="form-group">
                    <label>Price (₹)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>HSN Code</label>
                    <input type="text" name="hsn_code" class="form-control">
                </div>
                <div class="form-group">
                    <label>GST Rate (%)</label>
                    <select name="gst_rate" class="form-control">
                        <option value="18">18%</option>
                        <option value="12">12%</option>
                        <option value="5">5%</option>
                        <option value="0">0%</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
    async function saveProduct(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const res = await fetch('api/save_product.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast('Product saved!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, 'error');
            }
        } catch(err) {
            showToast('Error saving product', 'error');
        }
    }
    </script>
</body>
</html>
