<?php
// File: products/mark_expired.php   (or cron/mark_expired.php)

include("../config/db.php");   // Make sure this path is correct from your current file location

// Enable better error reporting for mysqli (highly recommended)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$today = date('Y-m-d');
$affected = 0;

try {
    // Updated query - safer and clearer
    $sql = "
        UPDATE products 
        SET is_active = 0 
        WHERE is_active = 1 
          AND expiry_date IS NOT NULL 
          AND expiry_date < ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $today);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Optional: Log the action
    if ($affected > 0) {
        $log_stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, description, created_at) 
            VALUES (0, 'SYSTEM_EXPIRE', ?, NOW())
        ");
        $description = "Auto-marked $affected product(s) as expired on $today";
        $log_stmt->bind_param("s", $description);
        $log_stmt->execute();
        $log_stmt->close();
    }

    echo "[" . date('Y-m-d H:i:s') . "] Success: Marked $affected product(s) as expired.<br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    error_log("Expiry script error: " . $e->getMessage());
}
?>