<?php
include("../includes/auth_check.php");

// Strict Admin Only
if ($_SESSION['role'] != "Admin") {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

include("../config/db.php");

// Fetch logs with better details
$query = "SELECT al.*, u.username 
          FROM activity_logs al 
          LEFT JOIN users u ON al.user_id = u.user_id 
          ORDER BY al.timestamp DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --accent: #f97316;
            --dark: #0f172a;
            --light: #f8fafc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            color: #1e2937;
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e2937 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.12);
        }

        .sidebar-header {
            padding: 35px 25px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .sidebar-header i { font-size: 32px; color: #60a5fa; }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; color: #f8fafc; letter-spacing: -0.5px; }
        .sidebar-header p { font-size: 13px; color: #94a3b8; margin-top: 4px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 28px;
            color: #cbd5e1;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.15);
            color: #ffffff;
            border-left: 4px solid #60a5fa;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            overflow-y: auto;
        }

        .main-header {
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }

        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .table-container {
            max-height: 680px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #0f172a;
        }

        th {
            padding: 18px 20px;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 14.5px;
        }

        .log-id { font-family: monospace; color: #64748b; }
        .username { font-weight: 600; color: #1e2937; }

        .action-tag {
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .action-login     { background: #dbeafe; color: #1e40af; }
        .action-create    { background: #d1fae5; color: #065f46; }
        .action-update    { background: #dbeafe; color: #1e40af; }
        .action-delete    { background: #fee2e2; color: #991b1b; }
        .action-stock-in  { background: #d1fae5; color: #065f46; }
        .action-stock-out { background: #fee2e2; color: #991b1b; }
        .action-sale      { background: #fef3c7; color: #92400e; }

        .timestamp {
            color: #64748b;
            font-size: 13.5px;
            white-space: nowrap;
        }

        .details {
            max-width: 450px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-store"></i>
            <h2>MINI-MART</h2>
        </div>
        <p>Inventory Management System</p>
    </div>

    <nav class="sidebar-nav">
        <a href="../dashboard/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <?php if(in_array($_SESSION['role'], ['Admin','Manager'])): ?>
            <a href="../suppliers/suppliers.php" class="nav-link"><i class="fas fa-truck"></i><span>Suppliers</span></a>
        <?php endif; ?>
        <a href="../products/products.php" class="nav-link"><i class="fas fa-boxes"></i><span>Products</span></a>
        <?php if(in_array($_SESSION['role'], ['Admin','Manager'])): ?>
            <a href="../stock/stock_management.php" class="nav-link"><i class="fas fa-layer-group"></i><span>Stock Management</span></a>
        <?php endif; ?>
        <?php if($_SESSION['role'] == "Admin"): ?>
            <a href="../reports/reports.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
        <?php endif; ?>
        <a href="../pos/pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Sales</span></a>
        <?php if($_SESSION['role'] === 'Admin'): ?>
            <a href="../logs/activity_logs.php" class="nav-link active"><i class="fas fa-clipboard-list"></i><span>Activity Logs</span></a>
        <?php endif; ?>
        <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </nav>
</aside>

<main class="main-content">
    <div class="main-header">
        <h1 class="page-title">Activity Logs</h1>
        <span class="role-badge">ADMIN • FULL SYSTEM AUDIT TRAIL</span>
    </div>

    <div class="table-card">
        <h3><i class="fas fa-history"></i> All User Activities & System Events</h3>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80">Log ID</th>
                        <th width="160">User</th>
                        <th width="140">Action</th>
                        <th>Details</th>
                        <th width="180">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $action = strtoupper($row['action_type'] ?? 'UNKNOWN');
                            $class = 'action-update';

                            if (stripos($action, 'LOGIN') !== false) $class = 'action-login';
                            elseif (stripos($action, 'LOGOUT') !== false) $class = 'action-login';
                            elseif (stripos($action, 'CREATE') !== false || stripos($action, 'ADD') !== false) $class = 'action-create';
                            elseif (stripos($action, 'UPDATE') !== false || stripos($action, 'EDIT') !== false) $class = 'action-update';
                            elseif (stripos($action, 'DELETE') !== false) $class = 'action-delete';
                            elseif (stripos($action, 'STOCK IN') !== false) $class = 'action-stock-in';
                            elseif (stripos($action, 'STOCK OUT') !== false) $class = 'action-stock-out';
                            elseif (stripos($action, 'SALE') !== false) $class = 'action-sale';
                        ?>
                            <tr>
                                <td><span class="log-id">#<?= $row['log_id'] ?></span></td>
                                <td><span class="username"><?= htmlspecialchars($row['username'] ?? 'System / Deleted User') ?></span></td>
                                <td><span class="action-tag <?= $class ?>"><?= htmlspecialchars($action) ?></span></td>
                                <td class="details"><?= htmlspecialchars($row['description'] ?? $row['affected_record'] ?? 'No details available') ?></td>
                                <td><span class="timestamp">
                                    <?= date("d M Y | h:i A", strtotime($row['timestamp'])) ?>
                                </span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>No activity logs recorded yet.</p>
                                </div>
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