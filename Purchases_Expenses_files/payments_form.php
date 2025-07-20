<?php 
require_once '../includes_files/auth_check.php';
requireLogin();
include '../dashboard_things/sidebar_form.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="payments.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-money-check-dollar"></i> Payments</h1>
        </div>
        <!-- Payments content goes here -->
    </div>
</body>
</html> 