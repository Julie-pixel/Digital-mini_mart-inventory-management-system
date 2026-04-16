<?php
include("../includes/auth_check.php");
include("../config/db.php");

$message = "";
$success = false;
$supplier = null;

// Get supplier ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: suppliers.php");
    exit();
}

$supplier_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

if (!$supplier) {
    header("Location: suppliers.php");
    exit();
}

if (isset($_POST['update_supplier'])) {
    $name    = mysqli_real_escape_string($conn, trim($_POST['supplier_name']));
    $contact = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $email   = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));

    if (empty($name) || empty($contact)) {
        $message = "Supplier Name and Contact Number are required!";
    } else {
        $query = "UPDATE suppliers SET 
                  supplier_name = '$name', 
                  contact_number = '$contact', 
                  email = '$email' 
                  WHERE supplier_id = $supplier_id";

        if (mysqli_query($conn, $query)) {
            $message = "Supplier updated successfully!";
            $success = true;
            
            // Refresh supplier data
            $result = $conn->query("SELECT * FROM suppliers WHERE supplier_id = $supplier_id");
            $supplier = $result->fetch_assoc();
        } else {
            $message = "Error updating supplier: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier | Digital Mini-Mart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #ef4444;
            --dark: #0f172a;
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

        /* Sidebar - Same as other pages */
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
        .sidebar-header h2 { font-size: 22px; font-weight: 700; color: #f8fafc; }
        .sidebar-header p { font-size: 13px; color: #94a3b8; margin-top: 4px; }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 28px;
            color: #cbd5e1;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.15);
            color: #ffffff;
            border-left: 4px solid #60a5fa;
        }

        .nav-link i { font-size: 20px; width: 24px; }
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 520px;
            border: 1px solid #e2e8f0;
        }

        .page-title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 35px;
            font-size: 15px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #f87171;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .required::after {
            content: " *";
            color: #ef4444;
        }

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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 25px;
        }

        .back-link:hover {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-header h2, .sidebar-header p, .nav-link span { display: none; }
            .main-content { margin-left: 70px; padding: 20px; }
            .form-card { padding: 30px 25px; }
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
              <a href="pos.php" class="nav-link"><i class="fas fa-cash-register"></i><span>Sales</span></a>
            <a href="../logs/activity_logs.php" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Activity Logs</span></a>
            <a href="../auth/logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
</aside>

<div class="main-content">
    <div class="form-card">
        <a href="suppliers.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Suppliers
        </a>

        <h2 class="page-title">Edit Supplier</h2>
        <p class="subtitle">Update supplier information</p>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
                <i class="fas <?= $success ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="required">Supplier Name</label>
                <input type="text" name="supplier_name" placeholder="Supplier Name" required
                       value="<?= htmlspecialchars($supplier['supplier_name']) ?>">
            </div>

            <div class="form-group">
                <label class="required">Contact Number</label>
                <input type="text" name="contact_number" placeholder="Contact Number" required
                       value="<?= htmlspecialchars($supplier['contact_number'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email Address (Optional)</label>
                <input type="email" name="email" placeholder="supplier@example.com"
                       value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
            </div>

            <button type="submit" name="update_supplier" class="btn">
                <i class="fas fa-save"></i> Update Supplier
            </button>
        </form>
    </div>
</div>

</body>
</html>