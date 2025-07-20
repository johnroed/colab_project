<?php 
require_once '../includes_files/auth_check.php';
requireManagementAccess();
include '../dashboard_things/sidebar_form.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attachments</title>
    <link rel="stylesheet" href="attachments.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-paperclip"></i> Attachments</h1>
        </div>
        <!-- Attachments content goes here -->
    </div>
</body>
</html> 