<?php
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['unconfirmed'])) {
    $show_unconfirmed_modal = true;
} else {
    $show_unconfirmed_modal = false;
}
if (isset($_GET['approval_token'])) {
    $approval_token = $_GET['approval_token'];
    $show_approval_modal = true;
} else {
    $approval_token = '';
    $show_approval_modal = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to Your Account</title>
    <link rel="stylesheet" href="login_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="login-page-center">
        <div class="system-name">ACCOUNTECH</div>
        <div class="login-container">
            <h2>WELCOME, WE'RE HAPPY TO WORK WITH YOU</h2>
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)) : ?>
                <div class="error-message" style="color: #388e3c; background: rgba(76, 175, 80, 0.13); border: 1px solid rgba(76, 175, 80, 0.3);">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <label for="user_login_id">Email or Phone Number</label>
                <div class="input-icon-group">
                    <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                    <input type="text" id="user_login_id" name="user_login_id" required>
                </div>

                <label for="password_secret">Password</label>
                <div class="input-icon-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="password_secret" name="password_secret" required>
                </div>

                <a href="../forgot_files/forgot_password_form.php" class="forgot-password-link">Forgot Password?</a>

                <button type="submit" name="login_button">Sign In</button>
            </form>
        </div>
    </div>
<?php if ($show_unconfirmed_modal): ?>
<div id="unconfirmed-modal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);display:flex;align-items:center;justify-content:center;z-index:9999;">
  <div style="background:#fff;padding:32px 28px;border-radius:12px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);max-width:350px;text-align:center;position:relative;">
    <div style="font-size:22px;font-weight:700;color:#1976d2;margin-bottom:18px;">Email Confirmation Required</div>
    <div style="font-size:16px;color:#333;margin-bottom:22px;">Please check your email and confirm your account before logging in.</div>
    <button onclick="document.getElementById('unconfirmed-modal').style.display='none'" style="padding:10px 24px;background:#1976d2;color:#fff;border:none;border-radius:6px;font-size:16px;font-weight:600;cursor:pointer;">Close</button>
  </div>
</div>
<script>document.body.style.overflow = 'hidden';document.getElementById('unconfirmed-modal').addEventListener('click',function(e){if(e.target===this){this.style.display='none';document.body.style.overflow='';}});</script>
<?php endif; ?>
<?php if ($show_approval_modal): ?>
<div id="approval-modal" class="approval-modal-bg">
  <div id="approval-modal-content" class="approval-modal-content">
    <div id="approval-message" class="approval-message">Login approval needed. We've sent an approval link to your email. Please check your inbox and click the link to approve this login. This helps keep your account secure.</div>
    <div id="approval-loading" class="approval-loading">
      <div class="spinner"></div>
    </div>
    <!-- Close button removed -->
  </div>
</div>
<script>
let polling = false;
function startApprovalPolling() {
  if (polling) return;
  polling = true;
  document.getElementById('approval-message').textContent = 'Login approval needed. We\'ve sent an approval link to your email. Please check your inbox and click the link to approve this login. This helps keep your account secure.';
  document.getElementById('approval-loading').style.display = 'flex';
  function poll() {
    fetch('check_approval.php?token=<?php echo htmlspecialchars($approval_token); ?>')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'approved') {
          window.location.href = 'complete_login.php?token=<?php echo htmlspecialchars($approval_token); ?>';
        } else if (data.status === 'expired') {
          document.getElementById('approval-message').textContent = 'Approval link expired. Please try logging in again.';
          document.getElementById('approval-loading').style.display = 'none';
        } else {
          setTimeout(poll, 2000);
        }
      })
      .catch(() => setTimeout(poll, 2000));
  }
  poll();
}
// Always start polling immediately if approval_token is present
startApprovalPolling();
window.addEventListener('focus', function() {
  if (polling) return;
  startApprovalPolling();
});
window.addEventListener('approval-link-clicked', function() {
  startApprovalPolling();
});
window.addEventListener('storage', function(e) {
  if (e.key === 'approval_token_clicked' && e.newValue === '<?php echo htmlspecialchars($approval_token); ?>') {
    startApprovalPolling();
  }
});
window.approvalLinkClicked = function() {
  localStorage.setItem('approval_token_clicked', '<?php echo htmlspecialchars($approval_token); ?>');
  startApprovalPolling();
};
// Prevent closing modal by clicking outside
const modalBg = document.getElementById('approval-modal');
modalBg.addEventListener('click', function(e) {
  if (e.target === modalBg) {
    // Do nothing
  }
});
</script>
<?php endif; ?>
</body>
</html> 