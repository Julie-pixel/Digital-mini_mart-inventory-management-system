<?php
include("../includes/auth_check.php");
include("../config/db.php");

$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT p.*, s.supplier_name 
              FROM products p 
              LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
              WHERE (p.product_name LIKE '%$search%' OR p.category LIKE '%$search%')
                AND p.is_active = 1
              ORDER BY p.product_id DESC";
} else {
    $query = "SELECT p.*, s.supplier_name 
              FROM products p 
              LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
              WHERE p.is_active = 1
              ORDER BY p.product_id DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #ef4444;
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
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(0,0,0,0.12);
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
        }

        .sidebar-header i { font-size: 32px; color: #60a5fa; }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; color: #f8fafc; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 28px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.15);
            color: white;
            border-left: 4px solid #60a5fa;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            overflow-y: auto;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title { 
            font-size: 28px; 
            font-weight: 700; 
            color: #0f172a; 
        }

        .search-box {
            position: relative; 
            width: 320px;
        }

        .search-box input {
            width: 100%;
            padding: 14px 20px 14px 50px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .search-box i {
            position: absolute; 
            left: 18px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #64748b;
        }

        .add-btn {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            color: white;
            padding: 12px 26px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .table-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            padding: 18px 24px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 13.5px;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        tr:hover { 
            background-color: #f8fafc; 
        }

        .product-name { font-weight: 600; }
        .price { font-weight: 700; color: #16a34a; }

        .stock-badge {
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 600;
        }
        .stock-low { background: #fee2e2; color: #ef4444; }
        .stock-good { background: #dcfce7; color: #16a34a; }

        /* Expiry Status Badges */
        .expiry-badge {
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }

        .expired { background: #fee2e2; color: #ef4444; }
        .expiring-soon { background: #fef3c7; color: #d97706; }
        .good { background: #ecfdf5; color: #10b981; }
        .no-expiry { background: #f1f5f9; color: #64748b; }

        /* New Action Buttons - Matching Screenshot Style */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .edit-btn {
            background-color: #f0fdf4;
            color: #16a34a;
            border-color: #86efac;
        }

        .edit-btn:hover {
            background-color: #16a34a;
            color: white;
            transform: translateY(-1px);
        }

        .delete-btn {
            background-color: #fef2f2;
            color: #ef4444;
            border-color: #fda4af;
        }

        .delete-btn:hover {
            background-color: #ef4444;
            color: white;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 80px 40px;
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
        <a href="../products/products.php" class="nav-link active"><i class="fas fa-boxes"></i><span>Products</span></a>
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
    <div class="header-section">
        <h1 class="page-title">Product Inventory</h1>
        
        <div style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
            <form method="GET" class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            </form>

            <?php if (in_array($_SESSION['role'], ['Admin', 'Manager'])): ?>
                <a href="add_product.php" class="add-btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                    <th>Stock</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $stock = (int)$row['quantity'];
                        $stockClass = ($stock <= 5) ? 'stock-low' : 'stock-good';

                        // Expiry Status Logic
                        $expiry_display = '—';
                        $status_html = '<span class="expiry-badge no-expiry">No Expiry</span>';

                        if (!empty($row['expiry_date'])) {
                            $expiry_date = $row['expiry_date'];
                            $expiry_display = date('d M Y', strtotime($expiry_date));
                            
                            $days_left = (strtotime($expiry_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                            $days_left = round($days_left);

                            if ($days_left < 0) {
                                $status_html = '<span class="expiry-badge expired">Expired (' . abs($days_left) . ' days ago)</span>';
                            } elseif ($days_left == 0) {
                                $status_html = '<span class="expiry-badge expired">Expires Today</span>';
                            } elseif ($days_left <= 7) {
                                $status_html = '<span class="expiry-badge expiring-soon">Expires in ' . $days_left . ' day' . ($days_left > 1 ? 's' : '') . '</span>';
                            } elseif ($days_left <= 30) {
                                $status_html = '<span class="expiry-badge expiring-soon">Expires in ' . $days_left . ' days</span>';
                            } else {
                                $status_html = '<span class="expiry-badge good">' . $days_left . ' days left</span>';
                            }
                        }
                    ?>
                    <tr>
                        <td><strong>#<?= $row['product_id'] ?></strong></td>
                        <td class="product-name"><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['category'] ?? 'N/A') ?></td>
                        <td class="price">KSh <?= number_format($row['buying_price'] ?? 0, 2) ?></td>
                        <td class="price">KSh <?= number_format($row['selling_price'] ?? 0, 2) ?></td>
                        <td>
                            <span class="stock-badge <?= $stockClass ?>">
                                <?= $stock ?> units
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['supplier_name'] ?? 'No Supplier') ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if (in_array($_SESSION['role'], ['Admin', 'Manager'])): ?>
                                    <a href="update_product.php?id=<?= $row['product_id'] ?>" class="action-btn edit-btn">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                <?php endif; ?>

                                <?php if ($_SESSION['role'] === 'Admin'): ?>
                                    <a href="delete_product.php?id=<?= $row['product_id'] ?>" 
                                       class="action-btn delete-btn"
                                       onclick="return confirm('Are you sure you want to delete this product?\n\nThis action cannot be undone.');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= $expiry_display ?></td>
                        <td><?= $status_html ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>No products found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>