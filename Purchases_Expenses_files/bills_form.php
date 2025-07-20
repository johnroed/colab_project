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
    <title>Bills</title>
    <link rel="stylesheet" href="bills.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</h1>
        </div>
        <!-- Bills content goes here -->
    </div>
</body>
</html> 