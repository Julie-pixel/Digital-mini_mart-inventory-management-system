<?php
session_start();
include("../includes/auth_check.php");
include("../config/db.php");

// Only Admin can delete
if ($_SESSION['role'] !== 'Admin') {
    header("Location: suppliers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: suppliers.php");
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid request.");
}

$id = intval($_POST['supplier_id']);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: suppliers.php");
exit;
?>