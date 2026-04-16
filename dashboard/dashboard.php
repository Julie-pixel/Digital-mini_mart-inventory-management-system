<?php
include("../includes/auth_check.php");
include("../config/db.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $total_products = 0;
    $total_query = $conn->query("SELECT COUNT(*) AS total FROM products");
    if ($total_query && $row = $total_query->fetch_assoc()) {
        $total_products = (int)$row['total'];
    }

    $low_threshold = 5;
    $low_stock_items = [];
    $low_stock_stmt = $conn->prepare(
        "SELECT product_name, quantity FROM products 
         WHERE quantity <= ? ORDER BY quantity ASC LIMIT 8"
    );
    $low_stock_stmt->bind_param("i", $low_threshold);
    $low_stock_stmt->execute();
    $low_stock_result = $low_stock_stmt->get_result();
    while ($row = $low_stock_result->fetch_assoc()) {
        $low_stock_items[] = $row;
    }
    $low_stock_count = count($low_stock_items);

    $product_names = $stock_in_data = $stock_out_data = [];
    $stock_sql = "
        SELECT p.product_name,
               COALESCE(SUM(CASE WHEN st.transaction_type = 'stock-in' THEN st.quantity ELSE 0 END), 0) AS stock_in,
               COALESCE(SUM(CASE WHEN st.transaction_type = 'stock-out' THEN st.quantity ELSE 0 END), 0) AS stock_out
        FROM products p
        LEFT JOIN stock_transactions st ON p.product_id = st.product_id
        GROUP BY p.product_id, p.product_name
        ORDER BY p.product_name ASC LIMIT 8";
    
    $stock_movement_stmt = $conn->prepare($stock_sql);
    $stock_movement_stmt->execute();
    $stock_result = $stock_movement_stmt->get_result();
    while ($row = $stock_result->fetch_assoc()) {
        $product_names[] = $row['product_name'];
        $stock_in_data[] = (int)$row['stock_in'];
        $stock_out_data[] = (int)$row['stock_out'];
    }

    // Top Products
    $top_names = $top_qty = [];
    $top_products_stmt = $conn->prepare(
        "SELECT product_name, quantity FROM products 
         ORDER BY quantity DESC LIMIT 10"
    );
    $top_products_stmt->execute();
    $top_result = $top_products_stmt->get_result();
    while ($row = $top_result->fetch_assoc()) {
        $top_names[] = $row['product_name'];
        $top_qty[] = (int)$row['quantity'];
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$hour = date("H");
$greeting = ($hour < 12) ? "Good Morning" : (($hour < 17) ? "Good Afternoon" : "Good Evening");

$safe_username = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8');
$safe_role = htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES, 'UTF-8');
$safe_greeting = htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
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
        }

        .dashboard-wrapper { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e2937 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
        }

        .sidebar-header {
            padding: 35px 25px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .logo i { font-size: 32px; color: #60a5fa; }
        .logo h2 { font-size: 22px; font-weight: 700; color: #f8fafc; letter-spacing: -0.5px; }

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

        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .greeting {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }

        .role-badge {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            padding: 8px 20px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 600;
            margin-top: 8px;
            display: inline-block;
        }

        /* Cards */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card i {
            font-size: 42px;
            margin-bottom: 16px;
        }

        .card h3 {
            font-size: 36px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .card p {
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
        }

        .low-stock-list {
            margin-top: 16px;
            max-height: 160px;
            overflow-y: auto;
            font-size: 14px;
            line-height: 1.7;
        }

        .low-stock-list div {
            padding: 6px 0;
            border-bottom: 1px solid #fee2e2;
        }

        .low-stock-list div:last-child {
            border-bottom: none;
        }
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .chart-container h3 {
            margin-bottom: 20px;
            font-size: 17px;
            color: #334155;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        canvas {
            width: 100% !important;
            height: 280px !important;
        }

        @media (max-width: 992px) {
            .charts-row, .cards-grid { grid-template-columns: 1fr; }
            .main-content { padding: 25px; }
            .sidebar { width: 260px; }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-store"></i>
                <h2>MINI-MART</h2>
            </div>
            <p>Inventory Management System</p>
        </div>

        <nav class="sidebar-nav">
            <a href="../dashboard/dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
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
            
            
            <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </nav>
    </aside>
    <main class="main-content">
        <div class="main-header">
            <h1 class="greeting"><?php echo $safe_greeting; ?>, <?php echo $safe_username; ?>!</h1>
            <span class="role-badge"><?php echo strtoupper($safe_role); ?> ACCOUNT</span>
        </div>

        <!-- Stats Cards -->
        <div class="cards-grid">
            <!-- Total Products -->
            <div class="card">
                <i class="fas fa-boxes" style="color: #2563eb;"></i>
                <div>
                    <h3><?php echo $total_products; ?></h3>
                    <p>Total Products</p>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="card">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                <div>
                    <h3><?php echo $low_stock_count; ?></h3>
                    <p>Low Stock Items</p>
                    <div class="low-stock-list">
                        <?php if (!empty($low_stock_items)): ?>
                            <?php foreach ($low_stock_items as $item): ?>
                                <div>
                                    <?php echo htmlspecialchars($item['product_name']); ?> — 
                                    <strong><?php echo (int)$item['quantity']; ?> left</strong>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="color: #10b981;">All items are well stocked ✓</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-container">
                <h3><i class="fas fa-chart-bar"></i> Stock Movement by Product (Top 8)</h3>
                <canvas id="stockMovementChart"></canvas>
            </div>

            <div class="chart-container">
                <h3><i class="fas fa-chart-pie"></i> Top 10 Products by Current Stock</h3>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
new Chart(document.getElementById('stockMovementChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($product_names); ?>,
        datasets: [
            {
                label: 'Stock In',
                data: <?php echo json_encode($stock_in_data); ?>,
                backgroundColor: '#2563eb',
                borderRadius: 6,
                barThickness: 26
            },
            {
                label: 'Stock Out',
                data: <?php echo json_encode($stock_out_data); ?>,
                backgroundColor: '#ef4444',
                borderRadius: 6,
                barThickness: 26
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        scales: {
            y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
            x: { grid: { color: '#e2e8f0' }, ticks: { maxRotation: 45, minRotation: 45 } }
        },
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 12, padding: 15 } }
        }
    }
});

// Top Products Chart
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($top_names); ?>,
        datasets: [{
            label: 'Current Stock',
            data: <?php echo json_encode($top_qty); ?>,
            backgroundColor: '#14b8a6',
            borderRadius: 8,
            barThickness: 28
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        scales: {
            x: { beginAtZero: true, grid: { color: '#e2e8f0' } },
            y: { grid: { display: false } }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { label: (context) => context.raw + ' units' }
            }
        }
    }
});
</script>

</body>
</html>