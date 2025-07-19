<?php
header('Content-Type: application/json');
include '../includes_files/connection.php';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$status = 'invalid';
if ($token !== '') {
    $find = $conn->prepare('SELECT status, expires_at FROM login_approvals WHERE token = ? LIMIT 1');
    $find->bind_param('s', $token);
    $find->execute();
    $result = $find->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['status'] === 'approved') {
            $status = 'approved';
        } elseif ($row['status'] === 'pending' && strtotime($row['expires_at']) > time()) {
            $status = 'pending';
        } elseif ($row['status'] === 'pending' && strtotime($row['expires_at']) <= time()) {
            $status = 'expired';
        } else {
            $status = 'expired';
        }
    }
    $find->close();
}
echo json_encode(['status' => $status]); 