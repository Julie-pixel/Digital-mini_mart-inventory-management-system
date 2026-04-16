<?php
include("../config/db.php");
header('Content-Type: application/json');

$sql = "SELECT 
            product_id, 
            product_name, 
            selling_price, 
            quantity 
        FROM products 
        WHERE quantity > 0 
        ORDER BY product_name ASC";

$result = mysqli_query($conn, $sql);

$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = [
        'product_id'    => (int)$row['product_id'],
        'product_name'  => htmlspecialchars($row['product_name']),
        'selling_price' => (float)$row['selling_price'],
        'quantity'      => (int)$row['quantity']
    ];
}
if (empty($products)) {
    echo json_encode(['debug' => 'No products found or query failed']);
} else {
    echo json_encode($products);
}

if (mysqli_error($conn)) {
    echo json_encode(['error' => mysqli_error($conn)]);
}

?>