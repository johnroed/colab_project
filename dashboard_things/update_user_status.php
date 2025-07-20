<?php
require_once '../includes_files/auth_check.php';
requireManagementAccess();
include '../includes_files/connection.php';
header('Content-Type: application/json');
if (!isset($_POST['user_id'], $_POST['status'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}
$user_id = intval($_POST['user_id']);
$status = $_POST['status'] === 'active' ? 'active' : 'inactive';
$stmt = $conn->prepare('UPDATE user_login SET status = ? WHERE user_id = ?');
$stmt->bind_param('si', $status, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$conn->close(); 