<?php
require_once '../includes_files/auth_check.php';
requireManagementAccess();
include '../includes_files/connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['email'], $_POST['password'], $_POST['job_title'], $_POST['status'])
    ) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $job_title = $_POST['job_title'];
        $status = $_POST['status'];
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
        if (!$email || !$password || !$job_title || !$status) {
            echo '<script>alert("Missing required fields.");window.history.back();</script>';
            exit;
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("SELECT user_id FROM user_login WHERE email_address = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo '<script>alert("Email already exists.");window.history.back();</script>';
            exit;
        }
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO user_login (email_address, password_secret, job_title, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $email, $password_hash, $job_title, $status);
        if (!$stmt->execute()) {
            echo '<script>alert("Failed to create user account.");window.history.back();</script>';
            exit;
        }
        $stmt->close();
        echo '<script>alert("User account created successfully.");window.location.href="user_roles_form.php";</script>';
        exit;
    } else {
        echo '<script>alert("Missing required fields.");window.history.back();</script>';
        exit;
    }
} else {
    header('Location: user_roles_form.php');
    exit;
} 