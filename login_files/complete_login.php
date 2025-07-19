<?php
session_start();
include '../includes_files/connection.php';
$token = isset($_GET['token']) ? $_GET['token'] : '';
if ($token === '') {
    header('Location: login_form.php?error=' . urlencode('Invalid login approval.'));
    exit();
}
$find = $conn->prepare('SELECT approval_id, user_id, status, expires_at FROM login_approvals WHERE token = ? LIMIT 1');
$find->bind_param('s', $token);
$find->execute();
$result = $find->get_result();
if ($result->num_rows === 0) {
    header('Location: login_form.php?error=' . urlencode('Invalid login approval.'));
    exit();
}
$row = $result->fetch_assoc();
if ($row['status'] !== 'approved' || strtotime($row['expires_at']) < time()) {
    header('Location: login_form.php?error=' . urlencode('Login approval expired or not approved.'));
    exit();
}
$user_id = $row['user_id'];
$user = $conn->prepare('SELECT job_title FROM user_login WHERE user_id = ? LIMIT 1');
$user->bind_param('i', $user_id);
$user->execute();
$user_result = $user->get_result();
if ($user_result->num_rows === 0) {
    header('Location: login_form.php?error=' . urlencode('User not found.'));
    exit();
}
$user_row = $user_result->fetch_assoc();
$_SESSION['user_id'] = $user_id;
$_SESSION['job_title'] = $user_row['job_title'];
$delete = $conn->prepare('DELETE FROM login_approvals WHERE approval_id = ?');
$delete->bind_param('i', $row['approval_id']);
$delete->execute();
$delete->close();
header('Location: ../dashboard_things/dashboard_form.php');
exit(); 