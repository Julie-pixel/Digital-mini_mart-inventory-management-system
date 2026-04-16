<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../includes/log_activity.php");

$message = "";

if (isset($_POST['process_stock'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $type = $_POST['transaction_type'];
    $user_id = $_SESSION['user_id'];

    if ($product_id <= 0 || $quantity <= 0) {
        $message = "All fields are required!";
    } else {
        $product_query = mysqli_query($conn, "SELECT * FROM products WHERE product_id='$product_id'");
        if (mysqli_num_rows($product_query) == 0) {
            $message = "Product not found!";
        } else {
            $product = mysqli_fetch_assoc($product_query);
            $current_quantity = $product['quantity'];

            if ($type == "stock-in") {
                $new_quantity = $current_quantity + $quantity;
                $update = mysqli_query($conn, "UPDATE products SET quantity='$new_quantity' WHERE product_id='$product_id'");
            } elseif ($type == "stock-out") {
                if ($quantity > $current_quantity) {
                    $message = "Not enough stock available!";
                } else {
                    $new_quantity = $current_quantity - $quantity;
                    $update = mysqli_query($conn, "UPDATE products SET quantity='$new_quantity' WHERE product_id='$product_id'");
                }
            }

            if (empty($message)) {
                mysqli_query($conn, "INSERT INTO stock_transactions (product_id, transaction_type, quantity, user_id)
                VALUES('$product_id', '$type', '$quantity', '$user_id')");
                
                logActivity($conn, $user_id, "Stock " . $type, $product_id);
                $message = "Stock updated successfully!";
            }
        }
    }
}

$products = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management | Digital Mini-Mart</title>
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

        /* Modern Sidebar - Consistent with all pages */
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
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-card {
            background: white;
            padding: 45px 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
            font-size: 14.5px;
        }

        select, input[type="number"] {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            background: white;
            transition: all 0.3s ease;
        }

        select:focus, input[type="number"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        /* Messages */
        .message {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
            font-size: 14.5px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        @media (max-width: 992px) {
            .sidebar { width: 240px; }
            .main-content { margin-left: 240px; }
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar-header h2, .sidebar-header p, .nav-link span { display: none; }
            .main-content { margin-left: 80px; padding: 25px; }
            .form-card { padding: 35px 25px; }
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
              <a href="../pos/pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span> Sales</span></a>
            
            <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
</aside>

<main class="main-content">
    <div class="form-card">
        <h1 class="page-title">Stock Management</h1>
        <p class="subtitle">Add or remove stock from inventory</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Select Product</label>
                <select name="product_id" required>
                    <option value="">-- Choose Product --</option>
                    <?php while ($p = mysqli_fetch_assoc($products)): ?>
                        <option value="<?php echo $p['product_id']; ?>">
                            <?php echo htmlspecialchars($p['product_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Transaction Type</label>
                <select name="transaction_type" required>
                    <option value="stock-in">Stock In (Add Stock)</option>
                    <option value="stock-out">Stock Out (Remove Stock)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" placeholder="Enter quantity" required>
            </div>

            <button type="submit" name="process_stock">
                <i class="fas fa-check-circle"></i> Process Stock Update
            </button>
        </form>
    </div>
</main>

</body>
</html>