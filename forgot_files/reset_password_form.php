<?php
session_start();
include '../includes_files/connection.php';

if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
// Check if we have a token from GET or POST
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token) && isset($_POST['token'])) {
    $token = $_POST['token'];
}

// Only validate token if there's no error message and no token at all
if (empty($token) && empty($error_message)) {
    header('Location: forgot_password_form.php?error=' . urlencode('Invalid reset link. Please request a new password reset.'));
    exit();
}

// If we have a token, validate it
if (!empty($token)) {
    $find_user = $conn->prepare('SELECT user_id, reset_code_expiry, reset_code_used FROM user_login WHERE reset_code = ? LIMIT 1');
    $find_user->bind_param('s', $token);
    $find_user->execute();
    $result = $find_user->get_result();

    if ($result->num_rows === 0) {
        header('Location: forgot_password_form.php?error=' . urlencode('Invalid or expired reset link. Please request a new password reset.'));
        exit();
    }

    $user = $result->fetch_assoc();
    if ($user['reset_code_used'] || strtotime($user['reset_code_expiry']) < time()) {
        header('Location: forgot_password_form.php?error=' . urlencode('This reset link has already been used or has expired. Please request a new password reset.'));
        exit();
    }

    $find_user->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ACCOUNTECH</title>
    <link rel="stylesheet" href="forgot.css">
    <link rel="stylesheet" href="../login_files/login_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
            alert('This page is no longer accessible. Please request a new password reset if needed.');
        };
    </script>
</head>
<body>
    <div class="login-page-center">
        <div class="system-name">
            <div>ACCOUNTECH</div>
            <div style="font-size: 18px; margin-top: 8px;">Set a new password for your account</div>
            <div style="font-size: 16px; margin-top: 4px;">We're here to help you get back in!</div>
        </div>
        <div class="login-container">
            <h2>Reset Your Password</h2>
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)) : ?>
                <div class="error-message" style="color: #388e3c; background: rgba(76, 175, 80, 0.13); border: 1px solid rgba(76, 175, 80, 0.3);">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="reset_password.php" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <label for="new_password">New Password</label>
                <div class="input-icon-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="new_password" name="new_password" required minlength="12" 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{12,}" 
                           title="Must contain at least one number, one uppercase and lowercase letter, and at least 12 characters">
                </div>
                <div style="font-size: 12px; color: #666; margin: -15px 0 15px 0;">
                    Password must be at least 12 characters with uppercase, lowercase, and numbers
                </div>
                <label for="confirm_password">Confirm New Password</label>
                <div class="input-icon-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="12">
                </div>
                <div id="password-match" style="font-size: 12px; margin: -15px 0 15px 0; display: none;"></div>
                <button type="submit" name="reset_password_button" id="submitBtn">Reset Password</button>
            </form>
            <div style="margin-top: 18px; text-align: center;">
                <a href="../login_files/login_form.php" style="color: #1976d2; text-decoration: none; font-weight: 500;">Back to Login</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('password-match');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('resetForm');
            
            function checkPasswordMatch() {
                if (confirmPassword.value === '') {
                    passwordMatch.style.display = 'none';
                    return;
                }
                
                if (newPassword.value === confirmPassword.value) {
                    passwordMatch.style.display = 'block';
                    passwordMatch.style.color = '#388e3c';
                    passwordMatch.textContent = '✓ Passwords match';
                    submitBtn.disabled = false;
                } else {
                    passwordMatch.style.display = 'block';
                    passwordMatch.style.color = '#c62828';
                    passwordMatch.textContent = '✗ Passwords do not match';
                    submitBtn.disabled = true;
                }
            }
            
            newPassword.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
            
            form.addEventListener('submit', function(e) {
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match. Please try again.');
                    return false;
                }
                
                if (newPassword.value.length < 12) {
                    e.preventDefault();
                    alert('Password must be at least 12 characters long.');
                    return false;
                }
                
                if (!/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/.test(newPassword.value)) {
                    e.preventDefault();
                    alert('Password must contain at least one uppercase letter, one lowercase letter, and one number.');
                    return false;
                }
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Resetting Password...';
            });
        });
    </script>
</body>
</html> 