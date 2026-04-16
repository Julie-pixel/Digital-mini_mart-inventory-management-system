<?php
include("../config/db.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$cart = $data['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$total = 0;
$conn->begin_transaction();

try {
    $total = array_sum(array_map(fn($item) => $item['selling_price'] * $item['quantity'], $cart));
    
    $stmt = $conn->prepare("INSERT INTO sales (total_amount, cashier_id) VALUES (?, ?)");
    $stmt->bind_param("di", $total, $_SESSION['user_id']);
    $stmt->execute();
    $sale_id = $conn->insert_id;

    foreach ($cart as $item) {
        $subtotal = $item['selling_price'] * $item['quantity'];
        
        $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity_sold, price_per_unit, subtotal) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $sale_id, $item['product_id'], $item['quantity'], $item['selling_price'], $subtotal);
        $stmt->execute();

        // Deduct stock
        $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'total' => number_format($total, 2)]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>