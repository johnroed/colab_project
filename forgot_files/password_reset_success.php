<?php
session_start();

// Prevent direct access to this page without completing password reset
if (!isset($_SERVER['HTTP_REFERER']) || 
    (strpos($_SERVER['HTTP_REFERER'], 'reset_password.php') === false && 
     strpos($_SERVER['HTTP_REFERER'], 'reset_password_form.php') === false)) {
    header('Location: forgot_password_form.php?error=' . urlencode('Invalid access. Please request a password reset first.'));
    exit();
}

// Set a session flag to prevent back button access
$_SESSION['password_reset_completed'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Success - ACCOUNTECH</title>
    <link rel="stylesheet" href="forgot.css">
    <link rel="stylesheet" href="../login_files/login_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .success-container {
            max-width: 450px;
            margin: 0 auto 60px auto;
            padding: 40px 30px;
            background: rgba(255, 255, 255, 0.35);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255,255,255,0.25);
            text-align: center;
            z-index: 1;
        }
        .success-icon {
            font-size: 64px;
            color: #388e3c;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        .success-title {
            font-size: 24px;
            font-weight: 700;
            color: #388e3c;
            margin-bottom: 16px;
        }
        .success-message {
            font-size: 16px;
            color: #333;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .redirect-info {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
        }
        .countdown {
            font-size: 18px;
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 8px;
        }
        .redirect-text {
            font-size: 14px;
            color: #666;
        }
        .login-button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(90deg, #1976d2 0%, #6dd5fa 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            margin: 16px 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.25);
        }
        .login-button:hover {
            background: linear-gradient(90deg, #1565c0 0%, #2193b0 100%);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.35);
            transform: translateY(-2px);
        }
        .security-notice {
            background: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 16px;
            margin: 24px 0;
            border-radius: 0 6px 6px 0;
            text-align: left;
        }
        .security-title {
            font-size: 14px;
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .security-text {
            font-size: 14px;
            color: #555;
            margin: 0;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin: 16px 0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #388e3c 0%, #4caf50 100%);
            border-radius: 2px;
            transition: width 1s linear;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-page-center">
        <div class="system-name">
            <div>ACCOUNTECH</div>
            <div style="font-size: 18px; margin-top: 8px;">Password Reset Complete</div>
        </div>
        
        <div class="success-container">
            <div class="success-icon">✅</div>
            <div class="success-title">Password Successfully Changed!</div>
            <div class="success-message">
                Your ACCOUNTECH account password has been successfully updated. You can now log in with your new password.
            </div>
            
            <div class="security-notice">
                <div class="security-title">🔒 Security Reminder</div>
                <div class="security-text">
                    • Keep your new password secure and don't share it with anyone<br>
                    • Consider enabling two-factor authentication for extra security<br>
                    • Log out from all devices if you suspect unauthorized access
                </div>
            </div>
            
            <div class="redirect-info">
                <div class="countdown" id="countdown">Redirecting in 5 seconds...</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar"></div>
                </div>
                <div class="redirect-text">You will be automatically redirected to the login page</div>
            </div>
            
            <div>
                <a href="../login_files/login_form.php" class="login-button">
                    <i class="fa-solid fa-sign-in-alt"></i> Go to Login Now
                </a>
                <a href="../forgot_files/forgot_password_form.php" class="login-button" style="background: #6c757d;">
                    <i class="fa-solid fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');
        
        function updateCountdown() {
            countdownElement.textContent = `Redirecting in ${countdown} seconds...`;
            const progress = ((5 - countdown) / 5) * 100;
            progressBar.style.width = progress + '%';
            
            if (countdown <= 0) {
                window.location.href = '../login_files/login_form.php?success=' + encodeURIComponent('Your password has been reset successfully. You can now log in with your new password.');
                return;
            }
            
            countdown--;
            setTimeout(updateCountdown, 1000);
        }
        
        updateCountdown();
        
        // Prevent back button access
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
            alert('This page is no longer accessible. Please use the login page.');
        };
    </script>
</body>
</html> 