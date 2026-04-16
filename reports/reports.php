<?php
include("../includes/auth_check.php");
include("../config/db.php");

$reports = null;
// Capture dates and keep them for the form "value" attributes
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
    <title>Reports | Digital Mini-Mart</title>
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

        /* Modern Sidebar */
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
            margin-bottom: 10px;
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

        /* Filter Form */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: end;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e2e8f0;
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

        /* Print Button */
        .print-btn {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            transition: all 0.3s;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
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

        .type-in { 
            color: #16a34a; 
            font-weight: 600; 
        }
        .type-out { 
            color: #ef4444; 
            font-weight: 600; 
        }

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
            .filter-form { flex-direction: column; }
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
           <a href="../suppliers/suppliers.php" class="nav-link"><i class="fas fa-truck"></i><span>Suppliers</span></a>
            <a href="../products/products.php" class="nav-link"><i class="fas fa-boxes"></i><span>Products</span></a>
            <a href="../stock/stock_management.php" class="nav-link"><i class="fas fa-layer-group"></i><span>Stock Management</span></a>
             <?php if($_SESSION['role'] == "Admin"){ ?>
                  <a href="../reports/reports.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
            <?php } ?>
              <a href="../pos/pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Sales</span></a>
            
            <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
</aside>

<main class="main-content">
    <h1 class="page-title">Stock Transaction Reports</h1>
    <p class="subtitle">View and analyze inventory movement between selected dates</p>

    <div class="container">
        <!-- Filter Form -->
        <form method="POST" class="filter-form">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
            </div>
            <button type="submit" name="generate_report" class="generate-btn">
                <i class="fas fa-sync"></i> Generate Report
            </button>
        </form>

        <?php if ($reports && mysqli_num_rows($reports) > 0): ?>
            <a href="print_report.php?start=<?php echo urlencode($start_date); ?>&end=<?php echo urlencode($end_date); ?>" 
               target="_blank" class="print-btn">
                <i class="fas fa-file-pdf"></i> Download / Print Report
            </a>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Product Name</th>
                            <th>Transaction Type</th>
                            <th>Quantity</th>
                            <th>Handled By</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($reports)): 
                            $type_class = (stripos($row['transaction_type'], 'in') !== false || 
                                         stripos($row['transaction_type'], 'add') !== false || 
                                         stripos($row['transaction_type'], 'receive') !== false) 
                                        ? 'type-in' : 'type-out';
                        ?>
                        <tr>
                            <td><strong>#<?php echo $row['transaction_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><span class="<?php echo $type_class; ?>">
                                <?php echo strtoupper(htmlspecialchars($row['transaction_type'])); ?>
                            </span></td>
                            <td><strong><?php echo (int)$row['quantity']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['username'] ?? 'System'); ?></td>
                            <td><?php echo date("M d, Y | h:i A", strtotime($row['transaction_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif (isset($_POST['generate_report'])): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No transactions found for the selected date range.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>