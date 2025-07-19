<?php
include '../includes_files/connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing or invalid user_id']);
    exit;
}
$user_id = intval($_POST['user_id']);

// Optionally, prevent deleting the currently logged-in user
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    echo json_encode(['success' => false, 'error' => 'You cannot delete your own account.']);
    exit;
}
// Prevent deleting executives
$stmt = $conn->prepare('SELECT job_title FROM user_login WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($job_title);
if ($stmt->fetch() && strtolower($job_title) === 'executives') {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Cannot delete executives.']);
    exit;
}
$stmt->close();
// Delete user
$stmt = $conn->prepare('DELETE FROM user_login WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete user.']);
}
$stmt->close(); 