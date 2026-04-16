<?php
$host = "db";
$user = "root";
$password = "";
$database = "digital_mini_mart_inventory_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>