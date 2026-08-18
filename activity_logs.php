<?php
require_once 'config/db.php';
require_once 'config/auth.php';

requireAuth();
$currentUser = getCurrentUser();

// Filters
$userFilter   = $_GET['user_id']     ?? '';
$actionFilter = $_GET['action_type'] ?? '';
$moduleFilter = $_GET['module']      ?? '';
$searchFilter = $_GET['search']      ?? '';
$dateFrom     = $_GET['date_from']   ?? '';
$dateTo       = $_GET['date_to']     ?? '';
$filterMonth  = $_GET['month']       ?? '';
$filterYear   = $_GET['year']        ?? '';

// Build Query
$where = ["1=1"];
$params = [];

if (!empty($userFilter)) {
    $where[] = "a.user_id = ?";
    $params[] = $userFilter;
}

if (!empty($actionFilter)) {
    $where[] = "a.action_type = ?";
    $params[] = $actionFilter;
}

if (!empty($moduleFilter)) {
    $where[] = "a.module = ?";
    $params[] = $moduleFilter;
}

if (!empty($searchFilter)) {
    $where[] = "(a.details LIKE ? OR a.username LIKE ? OR a.full_name LIKE ?)";
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
}

if (!empty($dateFrom)) {
    $where[] = "DATE(a.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "DATE(a.created_at) <= ?";
    $params[] = $dateTo;
}

if (!empty($filterMonth)) {
    $where[] = "MONTH(a.created_at) = ?";
    $params[] = (int)$filterMonth;
}

if (!empty($filterYear)) {
    $where[] = "YEAR(a.created_at) = ?";
    $params[] = (int)$filterYear;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT a.*, u.username as u_username, u.full_name as u_fullname
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE $whereSql
    ORDER BY a.id DESC
    LIMIT 300
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Year list for dropdown
$yearRows = $pdo->query("SELECT DISTINCT YEAR(created_at) as yr FROM activity_logs ORDER BY yr DESC")->fetchAll(PDO::FETCH_COLUMN);

$hasDateFilter = !empty($dateFrom) || !empty($dateTo) || !empty($filterMonth) || !empty($filterYear);

// Fetch unique users for filter dropdown
$usersList = $pdo->query("SELECT id, username, full_name FROM users ORDER BY full_name ASC")->fetchAll();

// Fetch unique modules for filter dropdown
$modulesList = $pdo->query("SELECT DISTINCT module FROM activity_logs ORDER BY module ASC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit & Activity Logs - Shree Giriraj Poly Plast</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .filter-group input, .filter-group select {
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.9rem;
            outline: none;
        }

        .filter-group input:focus, .filter-group select:focus {
            border-color: #4f46e5;
        }

        .action-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-login { background: #e0e7ff; color: #3730a3; }
        .badge-logout { background: #f1f5f9; color: #475569; }
        .badge-create { background: #dcfce7; color: #166534; }
        .badge-update { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #991b1b; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .user-details-text {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .user-ip {
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="header">
            <div>
                <h1>Audit & Activity Logs</h1>
                <div style="color:var(--text-muted); font-size:0.9rem;">Track who created entries, modified records, and logged in across computers</div>
            </div>
            <div>
                <a href="activity_logs.php" class="btn" style="background:#f1f5f9; color:#334155;">
                    <i class='bx bx-refresh'></i> Refresh Logs
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-card">
            <form action="activity_logs.php" method="GET">
                <!-- Row 1: Existing filters -->
                <div class="filter-grid" style="margin-bottom:14px;">
                    <div class="filter-group">
                        <label>User</label>
                        <select name="user_id">
                            <option value="">All Users</option>
                            <?php foreach ($usersList as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= $userFilter == $u['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['full_name'] ?? $u['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Action Type</label>
                        <select name="action_type">
                            <option value="">All Actions</option>
                            <option value="LOGIN"  <?= $actionFilter === 'LOGIN'  ? 'selected' : '' ?>>LOGIN</option>
                            <option value="LOGOUT" <?= $actionFilter === 'LOGOUT' ? 'selected' : '' ?>>LOGOUT</option>
                            <option value="CREATE" <?= $actionFilter === 'CREATE' ? 'selected' : '' ?>>CREATE</option>
                            <option value="UPDATE" <?= $actionFilter === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                            <option value="DELETE" <?= $actionFilter === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Module</label>
                        <select name="module">
                            <option value="">All Modules</option>
                            <?php foreach ($modulesList as $mod): ?>
                                <option value="<?= htmlspecialchars($mod) ?>" <?= $moduleFilter === $mod ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mod) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Search Keyword</label>
                        <input type="text" name="search" placeholder="Search details..." value="<?= htmlspecialchars($searchFilter) ?>">
                    </div>
                </div>

                <!-- Row 2: Date Filters -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#94a3b8; letter-spacing:0.5px; margin-bottom:10px; display:flex; align-items:center; gap:5px;">
                        <i class='bx bx-calendar-alt'></i> Date Filter
                    </div>
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>From Date</label>
                            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="filter-group">
                            <label>To Date</label>
                            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="filter-group">
                            <label>Month</label>
                            <select name="month">
                                <option value="">All Months</option>
                                <?php
                                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                foreach ($months as $i => $m):
                                    $val = $i + 1;
                                ?>
                                    <option value="<?= $val ?>" <?= $filterMonth == $val ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Year</label>
                            <select name="year">
                                <option value="">All Years</option>
                                <?php foreach ($yearRows as $yr): ?>
                                    <option value="<?= $yr ?>" <?= $filterYear == $yr ? 'selected' : '' ?>><?= $yr ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($yearRows)): ?>
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?= $y ?>" <?= $filterYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-group" style="display:flex; gap:8px; align-items:flex-end;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">
                                <i class='bx bx-filter-alt'></i> Filter
                            </button>
                            <?php if(!empty($userFilter) || !empty($actionFilter) || !empty($moduleFilter) || !empty($searchFilter) || $hasDateFilter): ?>
                                <a href="activity_logs.php" class="btn" style="background:#e2e8f0; color:#334155;">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Activity Logs Table -->
        <div class="glass-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Activity Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $act = strtoupper($log['action_type']);
                                $badgeClass = 'badge-login';
                                if ($act === 'LOGOUT') $badgeClass = 'badge-logout';
                                if ($act === 'CREATE') $badgeClass = 'badge-create';
                                if ($act === 'UPDATE') $badgeClass = 'badge-update';
                                if ($act === 'DELETE') $badgeClass = 'badge-delete';

                                $displayName = !empty($log['full_name']) ? $log['full_name'] : (!empty($log['username']) ? $log['username'] : 'System');
                                $initial = strtoupper(substr($displayName, 0, 1));
                            ?>
                            <tr>
                                <td style="white-space:nowrap; font-size:0.85rem; color:#475569;">
                                    <i class='bx bx-time-five' style="color:#94a3b8"></i> 
                                    <?= date('d M Y, h:i:s A', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar"><?= $initial ?></div>
                                        <div class="user-details-text">
                                            <span class="user-name"><?= htmlspecialchars($displayName) ?></span>
                                            <?php if(!empty($log['username']) && $log['username'] !== $displayName): ?>
                                                <small style="color:#94a3b8">@<?= htmlspecialchars($log['username']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="action-badge <?= $badgeClass ?>"><?= htmlspecialchars($act) ?></span>
                                </td>
                                <td>
                                    <strong style="color:#334155; font-size:0.85rem;"><?= htmlspecialchars($log['module']) ?></strong>
                                </td>
                                <td style="font-size:0.9rem; color:#1e293b;">
                                    <?= htmlspecialchars($log['details']) ?>
                                </td>
                                <td style="font-family:monospace; font-size:0.8rem; color:#64748b;">
                                    <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 30px; color: var(--text-muted);">
                                    <i class='bx bx-info-circle' style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                    No activity logs found matching the filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
