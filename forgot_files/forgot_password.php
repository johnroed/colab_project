<?php
session_start();
include '../includes_files/connection.php';
require_once '../PHPMailer-master/src/PHPMailer.php';
require_once '../PHPMailer-master/src/SMTP.php';
require_once '../PHPMailer-master/src/Exception.php';

if (isset($_POST['user_login_id'])) {
    $login_input = trim($_POST['user_login_id']);
    $success_message = 'If an account exists for that email or phone, you will receive an email with reset instructions.';
    
    if ($login_input !== '') {
        $find_user = $conn->prepare('SELECT user_id, email_address, reset_code_expiry FROM user_login WHERE email_address = ? OR phone_number = ? LIMIT 1');
        $find_user->bind_param('ss', $login_input, $login_input);
        $find_user->execute();
        $result = $find_user->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];
            $email = $user['email_address'];
            $last_expiry = $user['reset_code_expiry'];
            $now = time();
            $can_send = true;
            if ($last_expiry) {
                $last_request_time = strtotime($last_expiry) - 300; // 5 min expiry, so last request was expiry-5min
                if ($now - $last_request_time < 300) { // 5 min cooldown
                    $can_send = false;
                }
            }
            if ($can_send) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', $now + 300);
                $update_stmt = $conn->prepare('UPDATE user_login SET reset_code = ?, reset_code_expiry = ?, reset_code_used = 0 WHERE user_id = ?');
                $update_stmt->bind_param('ssi', $token, $expiry, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
                $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset_password_form.php?token=" . $token;
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'johnroedlahaylahay2231@gmail.com';
                $mail->Password = 'teyl woxe qyfh kxgd';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('Admin_Support@gmail.com', 'ACCOUNTECH');
                $mail->addAddress($email);
                $mail->Subject = 'Password Reset Request';
                $mail->isHTML(true);
                $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            font-family: "Poppins", Arial, sans-serif; 
            background: #f5f5f5; 
            line-height: 1.6; 
        }
        .email-container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: #ffffff; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
        }
        .email-header { 
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); 
            padding: 32px 24px; 
            text-align: center; 
            color: #ffffff; 
        }
        .brand-name { 
            font-size: 28px; 
            font-weight: 800; 
            margin: 0 0 8px 0; 
            letter-spacing: 2px; 
            text-shadow: 0 2px 8px rgba(0,0,0,0.2); 
        }
        .brand-tagline { 
            font-size: 14px; 
            opacity: 0.9; 
            margin: 0; 
            font-weight: 400; 
        }
        .email-content { 
            padding: 40px 32px; 
            background: #ffffff; 
        }
        .greeting { 
            font-size: 20px; 
            font-weight: 600; 
            color: #1976d2; 
            margin: 0 0 16px 0; 
        }
        .message { 
            font-size: 16px; 
            color: #333333; 
            margin: 0 0 24px 0; 
            line-height: 1.6; 
        }
        .security-notice { 
            background: #e3f2fd; 
            border-left: 4px solid #1976d2; 
            padding: 16px; 
            margin: 24px 0; 
            border-radius: 0 6px 6px 0; 
        }
        .security-title { 
            font-size: 14px; 
            font-weight: 600; 
            color: #1976d2; 
            margin: 0 0 8px 0; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .security-text { 
            font-size: 14px; 
            color: #555555; 
            margin: 0; 
        }
        .button-container { 
            text-align: center; 
            margin: 32px 0; 
        }
        .reset-button { 
            display: inline-block; 
            padding: 16px 32px; 
            background: linear-gradient(90deg, #1976d2 0%, #6dd5fa 100%); 
            color: #ffffff; 
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.25); 
            transition: all 0.3s ease; 
            border: none; 
            cursor: pointer; 
        }
        .reset-button:hover { 
            background: linear-gradient(90deg, #1565c0 0%, #2193b0 100%); 
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.35); 
            transform: translateY(-2px); 
        }
        .email-footer { 
            background: #f8f9fa; 
            padding: 24px 32px; 
            text-align: center; 
            border-top: 1px solid #e9ecef; 
        }
        .footer-text { 
            font-size: 12px; 
            color: #6c757d; 
            margin: 0 0 8px 0; 
        }
        .footer-link { 
            color: #1976d2; 
            text-decoration: none; 
        }
        .footer-link:hover { 
            text-decoration: underline; 
        }
        .expiry-notice { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            border-radius: 6px; 
            padding: 12px; 
            margin: 16px 0; 
            text-align: center; 
        }
        .expiry-text { 
            font-size: 13px; 
            color: #856404; 
            margin: 0; 
        }
        .warning-box { 
            background: #fff5f5; 
            border: 1px solid #fed7d7; 
            border-radius: 6px; 
            padding: 16px; 
            margin: 24px 0; 
        }
        .warning-title { 
            font-size: 14px; 
            font-weight: 600; 
            color: #c53030; 
            margin: 0 0 8px 0; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        .warning-text { 
            font-size: 14px; 
            color: #742a2a; 
            margin: 0; 
        }
        @media only screen and (max-width: 600px) {
            .email-container { 
                margin: 0; 
                border-radius: 0; 
            }
            .email-header { 
                padding: 24px 16px; 
            }
            .brand-name { 
                font-size: 24px; 
            }
            .email-content { 
                padding: 24px 16px; 
            }
            .reset-button { 
                padding: 14px 24px; 
                font-size: 15px; 
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="brand-name">ACCOUNTECH</div>
            <div class="brand-tagline">Professional Accounting Business System</div>
        </div>
        
        <div class="email-content">
            <div class="greeting">🔑 Password Reset Request</div>
            
            <div class="message">
                We received a request to reset the password for your ACCOUNTECH account. If this was you, please click the button below to create a new password.
            </div>
            
            <div class="security-notice">
                <div class="security-title">🔒 Security Notice</div>
                <div class="security-text">
                    This reset link is valid for 5 minutes only. For your security, please ensure you\'re on a trusted device and network before proceeding.
                </div>
            </div>
            
            <div class="expiry-notice">
                <div class="expiry-text">⏰ This link expires in 5 minutes for your security</div>
            </div>
            
            <div class="button-container">
                <a href="' . $reset_link . '" class="reset-button">
                    🔄 Reset Password
                </a>
            </div>
            
            <div class="warning-box">
                <div class="warning-title">
                    ⚠️ Important Security Reminder
                </div>
                <div class="warning-text">
                    If you did not request this password reset, please ignore this email. Your current password will remain unchanged. For additional security, consider enabling two-factor authentication on your account.
                </div>
            </div>
            
            <div class="message">
                If the button above doesn\'t work, you can copy and paste this link into your browser:<br>
                <a href="' . $reset_link . '" style="color: #1976d2; word-break: break-all;">' . $reset_link . '</a>
            </div>
        </div>
        
        <div class="email-footer">
            <div class="footer-text">
                This is an automated security message from ACCOUNTECH.<br>
                If you have any questions, please contact our support team.
            </div>
            <div class="footer-text" style="margin-top: 12px;">
                © 2024 ACCOUNTECH. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>';
                try {
                    $mail->send();
                } catch (Exception $e) {
                }
            }
        }
        $find_user->close();
    }
    header('Location: forgot_password_form.php?success=' . urlencode($success_message));
    exit();
} else {
    header('Location: forgot_password_form.php');
    exit();
} 