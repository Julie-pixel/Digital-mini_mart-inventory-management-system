<?php
$host = "host.docker.internal:3306";
$user = "root";
$password = "";
$database = "digial_mini_mart_inventory_db";
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>