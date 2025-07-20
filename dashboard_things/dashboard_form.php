<?php 
require_once '../includes_files/auth_check.php';
requireLogin();
include 'sidebar_form.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard_style.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-gauge"></i> Dashboard</h1>
        </div>
        <!-- Dashboard main content goes here -->
    </div>
</body>
</html>
