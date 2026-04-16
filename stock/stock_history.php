<?php
include("../includes/auth_check.php");
include("../config/db.php");

$reports = null;
$start_date = $_POST['start_date'] ?? "";
$end_date = $_POST['end_date'] ?? "";

if (isset($_POST['generate_report'])) {
    $s_date = mysqli_real_escape_string($conn, $start_date);
    $e_date = mysqli_real_escape_string($conn, $end_date);

    $query = "SELECT st.*, p.product_name, u.username 
              FROM stock_transactions st
              LEFT JOIN products p ON st.product_id = p.product_id 
              LEFT JOIN users u ON st.user_id = u.user_id 
              WHERE DATE(st.transaction_date) BETWEEN '$s_date' AND '$e_date'
              ORDER BY st.transaction_date DESC";

    $reports = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock History | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --accent: #f97316;
            --dark: #0f172a;
            --light: #f8fafc;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            color: #1e2937;
            min-height: 100vh;
            display: flex;
        }

        /* Modern Sidebar - Consistent Style */
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

        .sidebar-nav { flex: 1; padding: 20px 0; }

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

        .nav-link i { font-size: 20px; width: 24px; }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            overflow-y: auto;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 30px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        /* Filter Section */
        .filter-section {
            background: #f8fafc;
            padding: 25px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            margin-bottom: 35px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: end;
        }

        .form-group {
            flex: 1;
            min-width: 220px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
            font-size: 14.5px;
        }

        input[type="date"] {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            background: white;
        }

        input[type="date"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .generate-btn {
            padding: 14px 32px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        /* Table */
        .table-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
        }

        .bg-in {
            background: #dcfce7;
            color: #16a34a;
        }

        .bg-out {
            background: #fee2e2;
            color: #ef4444;
        }

        .qty-change {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qty-plus { color: #16a34a; }
        .qty-minus { color: #ef4444; }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.6;
        }

        @media (max-width: 992px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; }
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar-header h2, .sidebar-header p, .nav-link span { display: none; }
            .main-content { margin-left: 80px; padding: 25px; }
            .filter-form { flex-direction: column; align-items: stretch; }
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
        <a href="../products/products.php" class="nav-link"><i class="fas fa-boxes"></i><span>Products</span></a>
        <a href="../stock/stock_history.php" class="nav-link active"><i class="fas fa-exchange-alt"></i><span>Stock History</span></a>
        <a href="../reports/reports.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
        <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
</aside>

<main class="main-content">
    <h1 class="page-title">Stock Transaction History</h1>
    <p class="subtitle">View all stock movements and inventory changes</p>

    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="POST" class="filter-form">
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
                </div>
                <button type="submit" name="generate_report" class="generate-btn">
                    <i class="fas fa-filter"></i> Filter Records
                </button>
            </form>
        </div>

        <?php if ($reports && mysqli_num_rows($reports) > 0): ?>
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th width="90">Ref #</th>
                            <th>Product Name</th>
                            <th>Action Type</th>
                            <th>Quantity Change</th>
                            <th>Handled By</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($reports)): 
                            $type = strtolower($row['transaction_type'] ?? '');
                            $isIn = in_array($type, ['in', 'addition', 'received', 'restock', 'stock-in']);
                            $badgeClass = $isIn ? 'bg-in' : 'bg-out';
                            $qtySign = $isIn ? '+' : '-';
                            $qtyClass = $isIn ? 'qty-plus' : 'qty-minus';
                        ?>
                        <tr>
                            <td><strong>#<?php echo $row['transaction_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['product_name'] ?? 'Deleted Product'); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo strtoupper(htmlspecialchars($row['transaction_type'])); ?>
                                </span>
                            </td>
                            <td class="qty-change <?php echo $qtyClass; ?>">
                                <i class="fas fa-arrow-<?php echo $isIn ? 'up' : 'down'; ?>"></i>
                                <?php echo $qtySign . (int)$row['quantity']; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['username'] ?? 'System'); ?></td>
                            <td style="color: #64748b;">
                                <?php echo date("d M Y", strtotime($row['transaction_date'])); ?> 
                                <small>at <?php echo date("h:i A", strtotime($row['transaction_date'])); ?></small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif (isset($_POST['generate_report'])): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No stock movements found for the selected date range.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>