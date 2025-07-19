<?php
session_start();
include '../includes_files/connection.php';

if (!empty($_POST)) {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $error = '';
    
    if ($token === '' || $new_password === '' || $confirm_password === '') {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 12 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error = 'Password must be at least 12 characters, include upper and lowercase letters, and a number.';
    }
    
    if ($error !== '') {
        header('Location: reset_password_form.php?token=' . urlencode($token) . '&error=' . urlencode($error));
        exit();
    }
    
    // Check if token exists and is valid
    $find_user = $conn->prepare('SELECT user_id, reset_code_expiry, reset_code_used, reset_code FROM user_login WHERE reset_code = ? LIMIT 1');
    $find_user->bind_param('s', $token);
    $find_user->execute();
    $result = $find_user->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: reset_password_form.php?token=' . urlencode($token) . '&error=' . urlencode('Invalid or expired reset link.'));
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Check if token is already used
    if ($user['reset_code_used']) {
        header('Location: reset_password_form.php?token=' . urlencode($token) . '&error=' . urlencode('This reset link has already been used. Please request a new password reset.'));
        exit();
    }
    
    // Check if token has expired
    if (strtotime($user['reset_code_expiry']) < time()) {
        header('Location: reset_password_form.php?token=' . urlencode($token) . '&error=' . urlencode('This reset link has expired. Please request a new password reset.'));
        exit();
    }
    
    $user_id = $user['user_id'];
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password and invalidate reset code - ONLY on successful password reset
    $update_stmt = $conn->prepare('UPDATE user_login SET password_secret = ?, reset_code = NULL, reset_code_expiry = NULL, reset_code_used = 1 WHERE user_id = ? AND reset_code = ? AND reset_code_used = 0');
    $update_stmt->bind_param('sis', $password_hash, $user_id, $token);
    
    if ($update_stmt->execute() && $update_stmt->affected_rows > 0) {
        $update_stmt->close();
        $find_user->close();
        header('Location: password_reset_success.php');
        exit();
    } else {
        $update_stmt->close();
        $find_user->close();
        header('Location: reset_password_form.php?token=' . urlencode($token) . '&error=' . urlencode('Failed to update password. The reset link may have been used already. Please request a new password reset.'));
        exit();
    }
} else {
    header('Location: reset_password_form.php');
    exit();
} 