<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireAuth();

$currentUser = getCurrentUser();
$role = strtolower($currentUser['role'] ?? '');

// Ensure Admin / Partner access
if ($role !== 'admin' && $role !== 'partner' && $role !== 'owner') {
    die("
    <div style='font-family:Arial,sans-serif; text-align:center; padding:50px;'>
        <h2 style='color:#ef4444;'>Access Restricted</h2>
        <p>This Data Export & Import Center is restricted to <strong>Admin</strong> users only.</p>
        <a href='index.php' style='display:inline-block; padding:8px 16px; background:#4f46e5; color:#fff; text-decoration:none; border-radius:6px;'>Return to Dashboard</a>
    </div>");
}

// Fetch record counts for overview cards
$countProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$countMaterials = (int)$pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$countCustomers = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$countSuppliers = (int)$pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$countInvoices = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$countPurchases = (int)$pdo->query("SELECT COUNT(*) FROM purchases")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Export &amp; Import - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .export-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform .2s, box-shadow .2s;
        }
        .export-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .export-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }
        .export-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .export-card-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }
        .export-card-count {
            font-size: 0.8rem;
            color: #64748b;
        }
        .export-card-desc {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.4;
            margin-bottom: 16px;
        }
        .btn-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background: #0f172a;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: background .2s;
        }
        .btn-export:hover { background: #334155; }
        
        .tab-bar {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
        }
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            font-size: 0.95rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .upload-dropzone:hover {
            border-color: #4f46e5;
            background: #eef2ff;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h1>Data Export &amp; Import Center</h1>
                <p style="color:var(--text-muted); font-size:0.85rem;">Admin Data Management, CSV Bulk Operations &amp; Database Backups</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="api/export_data.php?type=full_sql" class="btn btn-primary" style="background:#0f172a; box-shadow:none;">
                    <i class='bx bx-cloud-download'></i> Full Database SQL Backup
                </a>
            </div>
        </div>

        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('tabExport')"><i class='bx bx-export'></i> Export Data (CSV / Excel)</button>
            <button class="tab-btn" onclick="switchTab('tabImport')"><i class='bx bx-import'></i> Bulk Import (CSV)</button>
            <button class="tab-btn" onclick="switchTab('tabBackup')"><i class='bx bx-server'></i> Full Database Backup &amp; Restore</button>
        </div>

        <!-- TAB 1: EXPORT DATA -->
        <div id="tabExport" class="tab-content active">
            <h3 style="font-size:1.1rem; color:#1e293b; margin-bottom:6px;">Select Data Module to Export (CSV / Excel Format)</h3>
            <p style="font-size:0.85rem; color:#64748b; margin-bottom:16px;">Download formatted CSV files compatible with Microsoft Excel, Google Sheets, or ERP software.</p>

            <div class="export-grid">
                <!-- Products -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#e0e7ff; color:#4f46e5;"><i class='bx bx-package'></i></div>
                            <div>
                                <div class="export-card-title">Products Master</div>
                                <div class="export-card-count"><?= number_format($countProducts) ?> Records</div>
                            </div>
                        </div>
                        <div class="export-card-desc">Includes product names, categories, units, prices, rates per kg, weights, HSN codes, and stock quantities.</div>
                    </div>
                    <a href="api/export_data.php?type=products" class="btn-export">
                        <i class='bx bx-download'></i> Export Products CSV
                    </a>
                </div>

                <!-- Materials -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#ecfdf5; color:#059669;"><i class='bx bx-cube'></i></div>
                            <div>
                                <div class="export-card-title">Raw Materials &amp; Stock</div>
                                <div class="export-card-count"><?= number_format($countMaterials) ?> Records</div>
                            </div>
                        </div>
                        <div class="export-card-desc">Includes raw materials, masterbatch, additives, categories, units, rate/KG, HSN, and inventory stock balances.</div>
                    </div>
                    <a href="api/export_data.php?type=materials" class="btn-export" style="background:#059669;">
                        <i class='bx bx-download'></i> Export Materials CSV
                    </a>
                </div>

                <!-- Customers -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#eff6ff; color:#2563eb;"><i class='bx bx-user-voice'></i></div>
                            <div>
                                <div class="export-card-title">Customers Master</div>
                                <div class="export-card-count"><?= number_format($countCustomers) ?> Records</div>
                            </div>
                        </div>
                        <div class="export-card-desc">Includes client names, mobile numbers, registered states, GSTIN codes, and delivery addresses.</div>
                    </div>
                    <a href="api/export_data.php?type=customers" class="btn-export" style="background:#2563eb;">
                        <i class='bx bx-download'></i> Export Customers CSV
                    </a>
                </div>

                <!-- Suppliers -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#fef3c7; color:#d97706;"><i class='bx bx-shopping-bag'></i></div>
                            <div>
                                <div class="export-card-title">Suppliers Directory</div>
                                <div class="export-card-count"><?= number_format($countSuppliers) ?> Records</div>
                            </div>
                        </div>
                        <div class="export-card-desc">Includes vendor names, contact numbers, email addresses, registered states, GSTINs, and addresses.</div>
                    </div>
                    <a href="api/export_data.php?type=suppliers" class="btn-export" style="background:#d97706;">
                        <i class='bx bx-download'></i> Export Suppliers CSV
                    </a>
                </div>

                <!-- Sales Invoices -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#fee2e2; color:#dc2626;"><i class='bx bx-receipt'></i></div>
                            <div>
                                <div class="export-card-title">Sales Invoices</div>
                                <div class="export-card-count"><?= number_format($countInvoices) ?> Invoices</div>
                            </div>
                        </div>
                        <div class="export-card-desc">Complete sales register with invoice numbers, customer names, subtotals, CGST, SGST, IGST, and grand totals.</div>
                    </div>
                    <a href="api/export_data.php?type=invoices" class="btn-export" style="background:#dc2626;">
                        <i class='bx bx-download'></i> Export Invoices CSV
                    </a>
                </div>

                <!-- Purchase Bills -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#fae8ff; color:#a855f7;"><i class='bx bx-cart-download'></i></div>
                            <div>
                                <div class="export-card-title">Purchase Bills</div>
                                <div class="export-card-count"><?= number_format($countPurchases) ?> Bills</div>
                            </div>
                        </div>
                        <div class="export-card-desc">Inward purchases register with bill numbers, supplier details, tax breakdowns, and total amounts.</div>
                    </div>
                    <a href="api/export_data.php?type=purchases" class="btn-export" style="background:#a855f7;">
                        <i class='bx bx-download'></i> Export Purchases CSV
                    </a>
                </div>

                <!-- Ledgers -->
                <div class="export-card">
                    <div>
                        <div class="export-card-header">
                            <div class="export-icon-box" style="background:#f1f5f9; color:#334155;"><i class='bx bx-money'></i></div>
                            <div>
                                <div class="export-card-title">Payments &amp; Ledgers</div>
                                <div class="export-card-count">Financial Register</div>
                            </div>
                        </div>
                        <div class="export-card-desc">All customer debit/credit transactions, supplier payments, voucher notes, and transaction timestamps.</div>
                    </div>
                    <a href="api/export_data.php?type=ledgers" class="btn-export" style="background:#334155;">
                        <i class='bx bx-download'></i> Export Ledgers CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- TAB 2: IMPORT CSV -->
        <div id="tabImport" class="tab-content">
            <div style="display:grid; grid-template-columns: 1.3fr 1fr; gap:24px;">
                <div class="glass-card" style="padding:24px;">
                    <h3 style="margin-bottom:6px; font-size:1.1rem; color:#1e293b;">Upload CSV File for Bulk Import</h3>
                    <p style="font-size:0.85rem; color:#64748b; margin-bottom:20px;">Upload data to quickly add or update multiple records simultaneously.</p>

                    <form id="csvImportForm" onsubmit="handleCSVImport(event)">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700; font-size:0.85rem; color:#334155;">Select Module to Import *</label>
                            <select name="import_type" id="import_type" class="form-control" required style="font-size:0.9rem;">
                                <option value="products">📦 Products Master</option>
                                <option value="materials">🧪 Raw Materials &amp; Additives</option>
                                <option value="customers">👥 Customers Master</option>
                                <option value="suppliers">🏭 Suppliers Directory</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700; font-size:0.85rem; color:#334155;">Duplicate Handling Mode</label>
                            <select name="mode" class="form-control" style="font-size:0.9rem;">
                                <option value="insert_or_update">Update existing records &amp; insert new ones (Recommended)</option>
                                <option value="skip_existing">Skip existing records (Insert only new)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="font-weight:700; font-size:0.85rem; color:#334155;">Choose .CSV File *</label>
                            <div class="upload-dropzone" onclick="document.getElementById('import_file').click()">
                                <i class='bx bx-cloud-upload' style="font-size:2.5rem; color:#4f46e5; margin-bottom:8px;"></i>
                                <div style="font-weight:700; color:#1e293b;" id="fileNameDisplay">Click or Drag &amp; Drop CSV File Here</div>
                                <div style="font-size:0.8rem; color:#64748b; margin-top:4px;">Supported formats: .CSV (Max 10MB)</div>
                                <input type="file" name="import_file" id="import_file" accept=".csv" required style="display:none;" onchange="updateFileName(this)">
                            </div>
                        </div>

                        <button type="submit" id="btnSubmitImport" class="btn btn-primary" style="width:100%; padding:12px; font-size:0.95rem;">
                            <i class='bx bx-upload'></i> Start Import Process
                        </button>
                    </form>
                </div>

                <div>
                    <div class="glass-card" style="padding:24px;">
                        <h4 style="font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:12px;">
                            <i class='bx bx-file'></i> Download Sample CSV Templates
                        </h4>
                        <p style="font-size:0.85rem; color:#64748b; line-height:1.4; margin-bottom:16px;">
                            Download sample template files with pre-filled headers and examples to ensure your data imports smoothly:
                        </p>

                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <a href="api/export_data.php?type=sample_products" class="btn btn-secondary" style="justify-content:flex-start; font-size:0.85rem;">
                                <i class='bx bx-download'></i> Sample Products Template (.csv)
                            </a>
                            <a href="api/export_data.php?type=sample_materials" class="btn btn-secondary" style="justify-content:flex-start; font-size:0.85rem;">
                                <i class='bx bx-download'></i> Sample Materials Template (.csv)
                            </a>
                            <a href="api/export_data.php?type=sample_customers" class="btn btn-secondary" style="justify-content:flex-start; font-size:0.85rem;">
                                <i class='bx bx-download'></i> Sample Customers Template (.csv)
                            </a>
                            <a href="api/export_data.php?type=sample_suppliers" class="btn btn-secondary" style="justify-content:flex-start; font-size:0.85rem;">
                                <i class='bx bx-download'></i> Sample Suppliers Template (.csv)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: FULL DATABASE BACKUP & RESTORE -->
        <div id="tabBackup" class="tab-content">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
                <!-- Backup Download Card -->
                <div class="glass-card" style="padding:24px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        <div style="width:40px; height:40px; border-radius:10px; background:#e0e7ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
                            <i class='bx bx-download'></i>
                        </div>
                        <div>
                            <h3 style="font-size:1.1rem; color:#1e293b;">Download Full Database Backup</h3>
                            <div style="font-size:0.8rem; color:#64748b;">Instant complete ERP snapshot</div>
                        </div>
                    </div>
                    <p style="font-size:0.85rem; color:#475569; line-height:1.45; margin-bottom:20px;">
                        Generates a full, comprehensive backup of all tables (Products, Materials, Invoices, Purchases, Ledgers, Customers, Suppliers, Production Logs).
                    </p>

                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <a href="api/export_data.php?type=full_sql" class="btn btn-primary" style="background:#0f172a; padding:12px; justify-content:center;">
                            <i class='bx bx-file'></i> Download Full SQL Backup (.sql)
                        </a>
                        <a href="api/export_data.php?type=full_json" class="btn btn-secondary" style="padding:12px; justify-content:center;">
                            <i class='bx bx-code-curly'></i> Download Full JSON Backup (.json)
                        </a>
                    </div>
                </div>

                <!-- Restore Backup Card -->
                <div class="glass-card" style="padding:24px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        <div style="width:40px; height:40px; border-radius:10px; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
                            <i class='bx bx-history'></i>
                        </div>
                        <div>
                            <h3 style="font-size:1.1rem; color:#1e293b;">Restore Database from Backup</h3>
                            <div style="font-size:0.8rem; color:#dc2626; font-weight:700;">Admin Operation</div>
                        </div>
                    </div>
                    <p style="font-size:0.85rem; color:#475569; line-height:1.45; margin-bottom:16px;">
                        Upload a previously downloaded <strong>.sql</strong> or <strong>.json</strong> backup file to restore database tables.
                    </p>

                    <form id="restoreBackupForm" onsubmit="handleRestoreBackup(event)">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700; font-size:0.85rem;">Backup File Format *</label>
                            <select name="import_type" id="restore_type" class="form-control" required>
                                <option value="full_sql">Full SQL Backup (.sql)</option>
                                <option value="full_json">Full JSON Backup (.json)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-weight:700; font-size:0.85rem;">Choose Backup File *</label>
                            <input type="file" name="import_file" id="restore_file" class="form-control" required accept=".sql,.json">
                        </div>

                        <button type="submit" id="btnSubmitRestore" class="btn btn-primary" style="background:#dc2626; width:100%; padding:12px; justify-content:center;">
                            <i class='bx bx-refresh'></i> Restore Database Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
    <script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileNameDisplay').innerText = 'Selected: ' + input.files[0].name;
        }
    }

    async function handleCSVImport(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSubmitImport');
        submitBtn.disabled = true;
        submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Importing Data...";

        try {
            const res = await fetch('api/import_data.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                form.reset();
                document.getElementById('fileNameDisplay').innerText = 'Click or Drag & Drop CSV File Here';
            } else {
                showToast(data.message || 'Import failed', 'error');
            }
        } catch(err) {
            showToast('Error connecting to server', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = "<i class='bx bx-upload'></i> Start Import Process";
        }
    }

    async function handleRestoreBackup(e) {
        e.preventDefault();
        if (!confirm("CAUTION: Restoring a full database backup will overwrite existing tables with the backup contents. Do you want to proceed?")) {
            return;
        }

        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSubmitRestore');
        submitBtn.disabled = true;
        submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Restoring Database...";

        try {
            const res = await fetch('api/import_data.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                alert(data.message || "Database restored successfully!");
                location.reload();
            } else {
                showToast(data.message || 'Restore failed', 'error');
            }
        } catch(err) {
            showToast('Error connecting to server', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = "<i class='bx bx-refresh'></i> Restore Database Now";
        }
    }
    </script>
</body>
</html>
