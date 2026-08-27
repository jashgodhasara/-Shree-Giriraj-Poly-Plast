<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sidebarUser = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest');
$sidebarRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';
$sidebarInitial = strtoupper(substr($sidebarUser, 0, 1));
?>
<aside class="sidebar">
    <div class="logo">
        Giriraj<span>ERP Solutions</span>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div style="padding: 12px 15px; margin: 10px 15px; background: rgba(255,255,255,0.06); border-radius: 8px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #3b82f6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">
                <?= $sidebarInitial ?>
            </div>
            <div style="overflow: hidden; flex: 1;">
                <div style="color: #fff; font-size: 0.85rem; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    <?= htmlspecialchars($sidebarUser) ?>
                </div>
                <div style="color: #94a3b8; font-size: 0.72rem;">
                    <?= htmlspecialchars($sidebarRole) ?>
                </div>
            </div>
            <a href="logout.php" title="Logout" style="color: #ef4444; font-size: 1.2rem; display: flex; align-items: center; text-decoration: none; padding: 4px;" onclick="return confirm('Are you sure you want to log out?');">
                <i class='bx bx-log-out'></i>
            </a>
        </div>
    <?php endif; ?>

    <ul class="nav-links">
        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase;">Core & Overview</li>
        <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li><a href="billing.php" class="<?= basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : '' ?>"><i class='bx bx-store-alt'></i> POS</a></li>
        <li><a href="activity_logs.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : '' ?>"><i class='bx bx-history'></i> Activity Logs</a></li>
        <li><a href="#"><i class='bx bx-bar-chart-alt-2'></i> Reports</a></li>
        
        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Sales Module</li>
        <li><a href="customers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>"><i class='bx bx-user-voice'></i> Customers</a></li>
        <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>"><i class='bx bx-package'></i> Products Master</a></li>
        <li><a href="invoices.php" class="<?= basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : '' ?>"><i class='bx bx-receipt'></i> Sales Invoices</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Purchase Module</li>
        <li><a href="suppliers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : '' ?>"><i class='bx bx-shopping-bag'></i> Suppliers</a></li>
        <li><a href="purchases.php" class="<?= basename($_SERVER['PHP_SELF']) == 'purchases.php' ? 'active' : '' ?>"><i class='bx bx-cart-download'></i> Purchase Bills</a></li>
        <li><a href="transporters.php" class="<?= basename($_SERVER['PHP_SELF']) == 'transporters.php' ? 'active' : '' ?>"><i class='bx bx-truck'></i> Transporters</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Inventory & Production</li>
        <li><a href="materials.php" class="<?= basename($_SERVER['PHP_SELF']) == 'materials.php' ? 'active' : '' ?>"><i class='bx bx-cube'></i> Materials & Stock</a></li>
        <li><a href="production.php" class="<?= basename($_SERVER['PHP_SELF']) == 'production.php' ? 'active' : '' ?>"><i class='bx bx-sitemap'></i> Production Logs</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Accounts & Finance</li>
        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Administration</li>
        <li><a href="export_import.php" class="<?= basename($_SERVER['PHP_SELF']) == 'export_import.php' ? 'active' : '' ?>"><i class='bx bx-data'></i> Data Export &amp; Import</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Account</li>
        <li><a href="logout.php" style="color:#f87171;" onclick="return confirm('Are you sure you want to log out?');"><i class='bx bx-log-out-circle'></i> Sign Out</a></li>
    </ul>
</aside>
