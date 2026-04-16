<?php
include("../includes/auth_check.php");
include("../config/db.php");

$message = "";
$success = false;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Fetch current product data
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Handle Update
if (isset($_POST['update_product'])) {
    $name          = mysqli_real_escape_string($conn, trim($_POST['product_name']));
    $category      = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
    $selling_price = (float)$_POST['selling_price'];
    $buying_price  = (float)$_POST['buying_price'];
    $quantity      = (int)$_POST['quantity'];
    $supplier_id   = (int)$_POST['supplier_id'];
    $expiry_date   = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;

    if (empty($name) || $selling_price <= 0) {
        $message = "Product name and selling price are required!";
    } else {
        // Update query with expiry_date
        if ($expiry_date !== NULL) {
            $query = "UPDATE products SET 
                      product_name = '$name',
                      category = '$category',
                      selling_price = $selling_price,
                      buying_price = $buying_price,
                      quantity = $quantity,
                      supplier_id = $supplier_id,
                      expiry_date = '$expiry_date'
                      WHERE product_id = $product_id";
        } else {
            $query = "UPDATE products SET 
                      product_name = '$name',
                      category = '$category',
                      selling_price = $selling_price,
                      buying_price = $buying_price,
                      quantity = $quantity,
                      supplier_id = $supplier_id,
                      expiry_date = NULL
                      WHERE product_id = $product_id";
        }

        if (mysqli_query($conn, $query)) {
            $message = "Product updated successfully!";
            $success = true;
            
            // Refresh product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "Error updating product: " . mysqli_error($conn);
        }
    }
}

// Fetch suppliers for dropdown
$suppliers = mysqli_query($conn, "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #ef4444;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f1f5f9;
            display: flex;
            min-height: 100vh;
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
        }
        .sidebar-header .logo {
            display: flex; align-items: center; justify-content: center; gap: 12px;
        }
        .sidebar-header i { font-size: 32px; color: #60a5fa; }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; color: #f8fafc; }

        .nav-link {
            display: flex; align-items: center; gap: 14px; padding: 16px 28px;
            color: #cbd5e1; text-decoration: none; transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(59,130,246,0.15); color: white; border-left: 4px solid #60a5fa;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-card {
            background: white;
            padding: 40px 45px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            max-width: 560px;
            width: 100%;
        }
        .page-title { text-align: center; font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 30px; }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #10b981; }
        .alert-error { background: #fef2f2; color: #ef4444; border: 1px solid #f87171; }

        .form-group { margin-bottom: 22px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        input, select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 16px;
        }
        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
            outline: none;
        }
        .required::after { content: " *"; color: #ef4444; }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.3); }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            margin-bottom: 25px;
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
    <nav>
        <a href="../dashboard/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <?php if(in_array($_SESSION['role'], ['Admin','Manager'])): ?>
            <a href="../suppliers/suppliers.php" class="nav-link"><i class="fas fa-truck"></i><span>Suppliers</span></a>
        <?php endif; ?>
        <a href="../products/products.php" class="nav-link"><i class="fas fa-boxes"></i><span>Products</span></a>
        <?php if(in_array($_SESSION['role'], ['Admin','Manager'])): ?>
            <a href="../stock/stock_management.php" class="nav-link"><i class="fas fa-layer-group"></i><span>Stock Management</span></a>
        <?php endif; ?>
        <?php if($_SESSION['role'] == "Admin"){ ?>
            <a href="../reports/reports.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Reports</span></a>
        <?php } ?>
        <a href="../pos/pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Sales</span></a>
        <?php if($_SESSION['role'] === 'Admin'): ?>
            <a href="../logs/activity_logs.php" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Activity Logs</span></a>
        <?php endif; ?>
        <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </nav>
</aside>

<div class="main-content">
    <div class="form-card">
        <a href="products.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>

        <h2 class="page-title">Edit Product</h2>
        <p class="subtitle">Update product information</p>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
                <i class="fas <?= $success ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="required">Product Name</label>
                <input type="text" name="product_name" required 
                       value="<?= htmlspecialchars($product['product_name']) ?>">
            </div>

            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" 
                       value="<?= htmlspecialchars($product['category'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="required">Selling Price (KSh)</label>
                <input type="number" name="selling_price" step="0.01" min="0" required
                       value="<?= $product['selling_price'] ?>">
            </div>

            <div class="form-group">
                <label>Buying Price (KSh)</label>
                <input type="number" name="buying_price" step="0.01" min="0"
                       value="<?= $product['buying_price'] ?>">
            </div>

            <div class="form-group">
                <label class="required">Stock Quantity</label>
                <input type="number" name="quantity" min="0" required
                       value="<?= $product['quantity'] ?>">
            </div>

            <!-- Expiry Date Field - Same style as add_product.php -->
            <div class="form-group">
                <label for="expiry_date">Expiry Date </label>
                <input type="date" name="expiry_date" id="expiry_date" 
                       value="<?= !empty($product['expiry_date']) ? $product['expiry_date'] : '' ?>">
                            </div>

            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id">
                    <option value="0">No Supplier</option>
                    <?php 
                    mysqli_data_seek($suppliers, 0);
                    while($sup = mysqli_fetch_assoc($suppliers)): 
                    ?>
                        <option value="<?= $sup['supplier_id'] ?>" 
                            <?= $sup['supplier_id'] == $product['supplier_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sup['supplier_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="update_product" class="btn">
                <i class="fas fa-save"></i> Update Product
            </button>
        </form>
    </div>
</div>

</body>
</html>