<?php
// Digital Mini Mart - Entry Point
// Redirect to the authentication page if not logged in, or to dashboard if logged in

session_start();

if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to dashboard
    header('Location: dashboard/dashboard.php');
} else {
    // User is not logged in, redirect to login page
    header('Location: auth/login.php');
}
exit;
?>
