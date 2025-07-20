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
    <title>Sales & Expense Reports</title>
    <link rel="stylesheet" href="sales_expense_reports.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-file-chart-pie"></i> Sales & Expense Reports</h1>
        </div>
        <!-- Sales & Expense Reports content goes here -->
    </div>
</body>
</html> 