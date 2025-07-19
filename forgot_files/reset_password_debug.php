<?php
session_start();
include '../includes_files/connection.php';

echo "<h2>Password Reset Debug - Form Submission</h2>";

// Check what was submitted
echo "<h3>POST Data:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>GET Data:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

if (!empty($_POST)) {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    echo "<h3>Form Data:</h3>";
    echo "<p><strong>Token:</strong> " . htmlspecialchars($token) . "</p>";
    echo "<p><strong>New Password:</strong> " . htmlspecialchars($new_password) . "</p>";
    echo "<p><strong>Confirm Password:</strong> " . htmlspecialchars($confirm_password) . "</p>";
    
    $error = '';
    
    // Validation
    if ($token === '' || $new_password === '' || $confirm_password === '') {
        $error = 'All fields are required.';
        echo "<p style='color: red;'>❌ Error: " . $error . "</p>";
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
        echo "<p style='color: red;'>❌ Error: " . $error . "</p>";
    } elseif (strlen($new_password) < 12 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error = 'Password must be at least 12 characters, include upper and lowercase letters, and a number.';
        echo "<p style='color: red;'>❌ Error: " . $error . "</p>";
    }
    
    if ($error !== '') {
        echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "&error=" . urlencode($error) . "'>Go back to form with error</a></p>";
        exit();
    }
    
    // Check if token exists and is valid
    echo "<h3>Token Validation:</h3>";
    $find_user = $conn->prepare('SELECT user_id, reset_code_expiry, reset_code_used, reset_code FROM user_login WHERE reset_code = ? LIMIT 1');
    $find_user->bind_param('s', $token);
    $find_user->execute();
    $result = $find_user->get_result();
    
    echo "<p>Database query executed. Rows found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows === 0) {
        echo "<p style='color: red;'>❌ Token not found in database!</p>";
        echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "&error=" . urlencode('Invalid or expired reset link.') . "'>Go back to form with error</a></p>";
        exit();
    }
    
    $user = $result->fetch_assoc();
    echo "<p>✅ User found: ID " . $user['user_id'] . "</p>";
    echo "<p>✅ Reset code used: " . ($user['reset_code_used'] ? 'Yes' : 'No') . "</p>";
    echo "<p>✅ Reset code expiry: " . $user['reset_code_expiry'] . "</p>";
    
    // Check if token is already used
    if ($user['reset_code_used']) {
        echo "<p style='color: red;'>❌ Token has already been used!</p>";
        echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "&error=" . urlencode('This reset link has already been used. Please request a new password reset.') . "'>Go back to form with error</a></p>";
        exit();
    }
    
    // Check if token has expired
    if (strtotime($user['reset_code_expiry']) < time()) {
        echo "<p style='color: red;'>❌ Token has expired!</p>";
        echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "&error=" . urlencode('This reset link has expired. Please request a new password reset.') . "'>Go back to form with error</a></p>";
        exit();
    }
    
    echo "<p style='color: green;'>✅ Token is valid!</p>";
    
    // Update password
    echo "<h3>Password Update:</h3>";
    $user_id = $user['user_id'];
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    echo "<p>Attempting to update password...</p>";
    
    $update_stmt = $conn->prepare('UPDATE user_login SET password_secret = ?, reset_code = NULL, reset_code_expiry = NULL, reset_code_used = 1 WHERE user_id = ? AND reset_code = ? AND reset_code_used = 0');
    $update_stmt->bind_param('sis', $password_hash, $user_id, $token);
    
    if ($update_stmt->execute()) {
        echo "<p>✅ Update query executed successfully</p>";
        echo "<p>Affected rows: " . $update_stmt->affected_rows . "</p>";
        
        if ($update_stmt->affected_rows > 0) {
            echo "<p style='color: green;'>✅ Password updated successfully!</p>";
            echo "<p><a href='password_reset_success.php'>Go to Success Page</a></p>";
        } else {
            echo "<p style='color: red;'>❌ No rows were affected!</p>";
            echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "&error=" . urlencode('Failed to update password. The reset link may have been used already.') . "'>Go back to form with error</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Update query failed: " . $update_stmt->error . "</p>";
        echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "&error=" . urlencode('Failed to update password. Please try again.') . "'>Go back to form with error</a></p>";
    }
    
    $update_stmt->close();
    $find_user->close();
} else {
    echo "<p>No form submission detected.</p>";
    echo "<p><a href='forgot_password_form.php'>Go to Forgot Password</a></p>";
}
?> 