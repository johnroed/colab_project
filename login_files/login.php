<?php
session_start();
include '../includes_files/connection.php';
require_once '../PHPMailer-master/src/PHPMailer.php';
require_once '../PHPMailer-master/src/SMTP.php';
require_once '../PHPMailer-master/src/Exception.php';
if (isset($_POST['user_login_id']) && isset($_POST['password_secret'])) {
    $login_input = trim($_POST['user_login_id']);
    $user_password = $_POST['password_secret'];
    if ($login_input !== '') {
        $find_user = $conn->prepare('SELECT user_id, email_address, phone_number, password_secret, job_title, status, failed_attempts, last_failed_attempt FROM user_login WHERE email_address = ? OR phone_number = ? LIMIT 1');
        $find_user->bind_param('ss', $login_input, $login_input);
        $find_user->execute();
        $result = $find_user->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['status'] !== 'active') {
                if (isset($user['failed_attempts']) && $user['failed_attempts'] == 0) {
                    $error_message = 'Your account has been deactivated due to multiple failed login attempts. Please contact the Admin to reactivate your account.';
                } else {
                    $error_message = 'Your account has been deactivated. Contact the Admin to activate your account.';
                }
                header('Location: login_form.php?error=' . urlencode($error_message));
                exit();
            } elseif (password_verify($user_password, $user['password_secret'])) {
                $user_id = $user['user_id'];
                $email = $user['email_address'];
                $token = bin2hex(random_bytes(32));
                $now = time();
                $expires_at = date('Y-m-d H:i:s', $now + 180); // 3 minutes
                $insert_approval = $conn->prepare('INSERT INTO login_approvals (user_id, token, status, created_at, expires_at) VALUES (?, ?, "pending", NOW(), ?)');
                $insert_approval->bind_param('iss', $user_id, $token, $expires_at);
                $insert_approval->execute();
                $insert_approval->close();
                $approval_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/approve_login.php?token=" . $token;
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
                $mail->Subject = 'Approve Login Attempt';
                $mail->isHTML(true);
                $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Approval Request</title>
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
        .approve-button { 
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
        .approve-button:hover { 
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
            .approve-button { 
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
            <div class="greeting">🔐 Login Approval Required</div>
            
            <div class="message">
                We detected a login attempt to your ACCOUNTECH account. To ensure the security of your account, we require your approval before proceeding.
            </div>
            
            <div class="security-notice">
                <div class="security-title">🔒 Security Notice</div>
                <div class="security-text">
                    This approval link is valid for 3 minutes only. If you did not attempt to log in, please ignore this email and consider changing your password.
                </div>
            </div>
            
            <div class="expiry-notice">
                <div class="expiry-text">⏰ This link expires in 3 minutes for your security</div>
            </div>
            
            <div class="button-container">
                <a href="' . $approval_link . '" class="approve-button">
                    ✅ Approve Login
                </a>
            </div>
            
            <div class="message">
                If the button above doesn\'t work, you can copy and paste this link into your browser:<br>
                <a href="' . $approval_link . '" style="color: #1976d2; word-break: break-all;">' . $approval_link . '</a>
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
                header('Location: login_form.php?approval_token=' . urlencode($token));
                exit();
            } else {
                $failed_attempts = isset($user['failed_attempts']) ? (int)$user['failed_attempts'] : 0;
                $last_failed_attempt = isset($user['last_failed_attempt']) ? $user['last_failed_attempt'] : null;
                $now = date('Y-m-d H:i:s');
                if ($failed_attempts > 0 && $last_failed_attempt) {
                    $last = strtotime($last_failed_attempt);
                    $diff = time() - $last;
                    if ($diff >= 3600) {
                        $failed_attempts = 0;
                    }
                }
                $failed_attempts++;
                if ($failed_attempts >= 3) {
                    $deactivate_stmt = $conn->prepare('UPDATE user_login SET status = "inactive", failed_attempts = 0, last_failed_attempt = NULL WHERE user_id = ?');
                    $deactivate_stmt->bind_param('i', $user['user_id']);
                    $deactivate_stmt->execute();
                    $deactivate_stmt->close();
                    $error_message = 'Your account has been deactivated due to multiple failed login attempts. Please contact the Admin to reactivate your account.';
                } else {
                    $update_stmt = $conn->prepare('UPDATE user_login SET failed_attempts = ?, last_failed_attempt = ? WHERE user_id = ?');
                    $update_stmt->bind_param('isi', $failed_attempts, $now, $user['user_id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    $error_message = 'Invalid email/phone or password.';
                }
                header('Location: login_form.php?error=' . urlencode($error_message));
                exit();
            }
        } else {
            $error_message = 'Invalid email/phone or password.';
            header('Location: login_form.php?error=' . urlencode($error_message));
            exit();
        }
        $find_user->close();
    } else {
        $error_message = 'Invalid email/phone or password.';
        header('Location: login_form.php?error=' . urlencode($error_message));
        exit();
    }
} else {
    header('Location: login_form.php');
    exit();
}
?>