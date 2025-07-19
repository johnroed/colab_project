<?php
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ACCOUNTECH</title>
    <link rel="stylesheet" href="forgot.css">
    <link rel="stylesheet" href="../login_files/login_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="login-page-center">
        <div class="system-name">
            <div>ACCOUNTECH</div>
            <div style="font-size: 18px; margin-top: 8px;">Don't worry, it happens to the best of us.</div>
            <div style="font-size: 16px; margin-top: 4px;">Let's get you back in!</div>
        </div>
        <div class="login-container">
            <h2>Forgot Your Password?</h2>
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)) : ?>
                <div class="error-message" style="color: #388e3c; background: rgba(76, 175, 80, 0.13); border: 1px solid rgba(76, 175, 80, 0.3);">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="forgot_password.php">
                <label for="user_login_id">Email or Phone Number</label>
                <div class="input-icon-group">
                    <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                    <input type="text" id="user_login_id" name="user_login_id" required>
                </div>
                <button type="submit" name="forgot_password_button">Send Reset Instructions</button>
            </form>
            <div style="margin-top: 18px; text-align: center;">
                <a href="../login_files/login_form.php" style="color: #1976d2; text-decoration: none; font-weight: 500;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html> 