<?php
include '../includes_files/connection.php';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$status = '';
if ($token !== '') {
    $find = $conn->prepare('SELECT approval_id, status, expires_at FROM login_approvals WHERE token = ? LIMIT 1');
    $find->bind_param('s', $token);
    $find->execute();
    $result = $find->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['status'] === 'pending' && strtotime($row['expires_at']) > time()) {
            $update = $conn->prepare('UPDATE login_approvals SET status = "approved" WHERE approval_id = ?');
            $update->bind_param('i', $row['approval_id']);
            $update->execute();
            $update->close();
            $status = 'approved';
        } elseif ($row['status'] === 'approved') {
            $status = 'already_approved';
        } else {
            $status = 'expired';
        }
    } else {
        $status = 'invalid';
    }
    $find->close();
} else {
    $status = 'invalid';
}
if ($status === 'approved' || $status === 'already_approved') {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Approval</title>
    <script>
    if (window.opener) {
        window.opener.localStorage.setItem('approval_token_clicked', '<?php echo htmlspecialchars($token); ?>');
        window.close();
    } else if (window.parent && window.parent !== window) {
        window.parent.localStorage.setItem('approval_token_clicked', '<?php echo htmlspecialchars($token); ?>');
        window.close();
    } else {
        localStorage.setItem('approval_token_clicked', '<?php echo htmlspecialchars($token); ?>');
        window.close();
    }
    </script>
    <style>body { background: #f7f7f7; }</style>
</head>
<body></body>
</html>
<?php } else { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Approval</title>
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f7f7f7; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .approval-box { background: #fff; padding: 36px 32px; border-radius: 12px; box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18); text-align: center; max-width: 350px; }
        .approval-title { font-size: 22px; font-weight: 700; color: #1976d2; margin-bottom: 18px; }
        .approval-message { font-size: 16px; color: #333; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="approval-box">
        <div class="approval-title">Login Approval</div>
        <?php if ($status === 'expired'): ?>
            <div class="approval-message">This approval link has expired. Please try logging in again.</div>
        <?php else: ?>
            <div class="approval-message">Invalid approval link.</div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php } 