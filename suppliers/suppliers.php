<?php
include("../includes/auth_check.php");
include("../config/db.php");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$stmt = $conn->prepare("SELECT * FROM suppliers ORDER BY supplier_id DESC");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers | Digital Mini-Mart</title>
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

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .logo i { font-size: 32px; color: #60a5fa; }
        .logo h2 { font-size: 22px; font-weight: 700; color: #f8fafc; letter-spacing: -0.5px; }
        .logo p { font-size: 13px; color: #94a3b8; margin-top: 4px; }

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

        .header-flex {
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

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }

        .table-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
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

        .supplier-id {
            color: #64748b;
            font-family: monospace;
            font-weight: 500;
        }

        .email-link {
            color: #2563eb;
            text-decoration: none;
        }
        .email-link:hover { text-decoration: underline; }

        .products-count {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }

        .action-btn {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .edit-btn {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #10b981;
        }
        .edit-btn:hover {
            background: #10b981;
            color: white;
        }

        .delete-btn {
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #f43f5e;
            margin-left: 8px;
        }
        .delete-btn:hover {
            background: #f43f5e;
            color: white;
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
            <a href="../suppliers/suppliers.php" class="nav-link active"><i class="fas fa-truck"></i><span>Suppliers</span></a>
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
    <div class="header-flex">
        <h1 class="page-title">Supplier Management</h1>
        <?php if (in_array($_SESSION['role'], ['Admin', 'Manager'])): ?>
            <a href="add_supplier.php" class="add-btn">
                <i class="fas fa-plus"></i> Add New Supplier
            </a>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Contact Number</th>
                    <th>Email Address</th>
                    <th>Products Supplied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $prod_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE supplier_id = ?");
                        $prod_stmt->bind_param("i", $row['supplier_id']);
                        $prod_stmt->execute();
                        $prod_result = $prod_stmt->get_result();
                        $prod_count = $prod_result->fetch_assoc()['total'];
                        $prod_stmt->close();
                    ?>
                    <tr>
                        <td><span class="supplier-id">#<?= $row['supplier_id'] ?></span></td>
                        <td><strong><?= htmlspecialchars($row['supplier_name']) ?></strong></td>
                        <td><?= htmlspecialchars($row['contact_number'] ?? 'N/A') ?></td>
                        <td>
                            <?php if (!empty($row['email'])): ?>
                                <a class="email-link" href="mailto:<?= htmlspecialchars($row['email']) ?>">
                                    <?= htmlspecialchars($row['email']) ?>
                                </a>
                            <?php else: ?>
                                <span class="no-email">Not provided</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="products-count">
                                <?= $prod_count ?> product<?= $prod_count != 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td>
                            <?php if (in_array($_SESSION['role'], ['Admin', 'Manager'])): ?>
                                <a class="action-btn edit-btn" href="update_supplier.php?id=<?= $row['supplier_id'] ?>">
                                    <i class=""></i> Edit
                                </a>
                            <?php endif; ?>

                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                <form method="POST" action="delete_supplier.php" style="display:inline;" 
                                      onsubmit="return confirm('Delete supplier <?= htmlspecialchars(addslashes($row['supplier_name'])) ?>? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="supplier_id" value="<?= $row['supplier_id'] ?>">
                                    <button type="submit" class="action-btn delete-btn">
                                        <i class=""></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-truck"></i>
                            <p>No suppliers have been added yet.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>