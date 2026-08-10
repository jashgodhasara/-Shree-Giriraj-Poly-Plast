<aside class="sidebar">
    <div class="logo">
        Giriraj<span>ERP Solutions</span>
    </div>
    <ul class="nav-links">
        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase;">Core & Overview</li>
        <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
        <li><a href="billing.php" class="<?= basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : '' ?>"><i class='bx bx-store-alt'></i> POS</a></li>
        <li><a href="#"><i class='bx bx-bar-chart-alt-2'></i> Reports</a></li>
        
        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Sales Module</li>
        <li><a href="#"><i class='bx bx-file'></i> Sales Order</a></li>
        <li><a href="#"><i class='bx bx-package'></i> Delivery Notes</a></li>
        <li><a href="invoices.php" class="<?= basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : '' ?>"><i class='bx bx-receipt'></i> Sales</a></li>
        <li><a href="#"><i class='bx bx-minus-circle'></i> Credit Note</a></li>
        <li><a href="#"><i class='bx bx-undo'></i> Rejection In</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Purchase Module</li>
        <li><a href="#"><i class='bx bx-cart'></i> Purchase Order</a></li>
        <li><a href="#"><i class='bx bx-box'></i> Receipt Note</a></li>
        <li><a href="#"><i class='bx bx-shopping-bag'></i> Purchase</a></li>
        <li><a href="#"><i class='bx bx-plus-circle'></i> Debit Note</a></li>
        <li><a href="#"><i class='bx bx-redo'></i> Rejection Out</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Inventory & Production</li>
        <li><a href="materials.php" class="<?= basename($_SERVER['PHP_SELF']) == 'materials.php' ? 'active' : '' ?>"><i class='bx bx-cube'></i> Inventory</a></li>
        <li><a href="#"><i class='bx bx-down-arrow-circle'></i> Material In</a></li>
        <li><a href="#"><i class='bx bx-up-arrow-circle'></i> Material Out</a></li>
        <li><a href="#"><i class='bx bx-book'></i> Stock Journal</a></li>
        <li><a href="production.php" class="<?= basename($_SERVER['PHP_SELF']) == 'production.php' ? 'active' : '' ?>"><i class='bx bx-sitemap'></i> BOM</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Job Work</li>
        <li><a href="#"><i class='bx bx-log-in'></i> Job Work In</a></li>
        <li><a href="#"><i class='bx bx-log-out'></i> Job Work Out</a></li>

        <li style="color:var(--text-muted); font-size:0.75rem; font-weight:800; padding:10px 20px; text-transform:uppercase; margin-top:10px;">Accounts & Finance</li>
        <li><a href="ledger.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ledger.php' ? 'active' : '' ?>"><i class='bx bx-money'></i> Payment</a></li>
        <li><a href="ledger.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ledger.php' ? 'active' : '' ?>"><i class='bx bx-detail'></i> Receipt</a></li>
    </ul>
</aside>

