<?php
include("../includes/auth_check.php");
include("../config/db.php");

if ($_SESSION['role'] !== 'Admin') {
    header("Location: products.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];
$check = mysqli_query($conn, "SELECT COUNT(*) as count FROM sales_items WHERE product_id = $product_id");
$row = mysqli_fetch_assoc($check);

if ($row['count'] > 0) {
    $_SESSION['error'] = "Cannot delete this product because it has been sold before. You can only deactivate it or reduce stock.";
    header("Location: products.php");
    exit();
}

// If no sales, safe to delete
if (mysqli_query($conn, "DELETE FROM products WHERE product_id = $product_id")) {
    $_SESSION['success'] = "Product deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting product.";
}

header("Location: products.php");
exit();
?>

