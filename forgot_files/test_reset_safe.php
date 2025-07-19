<?php
session_start();
include '../includes_files/connection.php';

echo "<h2>Password Reset Test (Safe Mode)</h2>";

// Simulate form submission
$token = 'f5ddb2215acb098ec9a9db147024aacecb2049633933ac6045a535b1d67e8727';
$new_password = 'TestPassword123';
$confirm_password = 'TestPassword123';

echo "<p><strong>Test Token:</strong> " . htmlspecialchars($token) . "</p>";
echo "<p><strong>Test Password:</strong> " . htmlspecialchars($new_password) . "</p>";
echo "<p><strong>Test Confirm Password:</strong> " . htmlspecialchars($confirm_password) . "</p>";

// Step 1: Check if token exists and is valid
echo "<h3>Step 1: Token Validation</h3>";
$find_user = $conn->prepare('SELECT user_id, reset_code_expiry, reset_code_used, reset_code FROM user_login WHERE reset_code = ? LIMIT 1');
$find_user->bind_param('s', $token);
$find_user->execute();
$result = $find_user->get_result();

echo "<p>Database query executed. Rows found: " . $result->num_rows . "</p>";

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p>✅ User found: ID " . $user['user_id'] . "</p>";
    echo "<p>✅ Reset code matches: " . ($user['reset_code'] === $token ? 'Yes' : 'No') . "</p>";
    echo "<p>✅ Reset code used: " . ($user['reset_code_used'] ? 'Yes' : 'No') . "</p>";
    echo "<p>✅ Reset code expiry: " . $user['reset_code_expiry'] . "</p>";
    
    $now = time();
    $expiry_time = strtotime($user['reset_code_expiry']);
    echo "<p>✅ Is expired: " . ($expiry_time < $now ? 'Yes' : 'No') . "</p>";
    
    if (!$user['reset_code_used'] && $expiry_time > $now) {
        echo "<p style='color: green;'>✅ Token is valid!</p>";
        
        // Step 2: Test password update (SAFE MODE - NO ACTUAL UPDATE)
        echo "<h3>Step 2: Password Update Test (Safe Mode)</h3>";
        $user_id = $user['user_id'];
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        echo "<p>Simulating password update (no actual changes will be made)...</p>";
        
        // Just test the query without executing it
        $update_stmt = $conn->prepare('UPDATE user_login SET password_secret = ?, reset_code = NULL, reset_code_expiry = NULL, reset_code_used = 1 WHERE user_id = ? AND reset_code = ? AND reset_code_used = 0');
        $update_stmt->bind_param('sis', $password_hash, $user_id, $token);
        
        echo "<p>✅ Query prepared successfully</p>";
        echo "<p>✅ Parameters bound successfully</p>";
        echo "<p style='color: orange;'>⚠️ Query NOT executed (safe mode)</p>";
        
        $update_stmt->close();
    } else {
        echo "<p style='color: red;'>❌ Token is invalid!</p>";
        if ($user['reset_code_used']) {
            echo "<p>Reason: Token has already been used</p>";
        }
        if ($expiry_time < $now) {
            echo "<p>Reason: Token has expired</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Token not found in database!</p>";
}

$find_user->close();

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<p>1. If your token is now invalid, you'll need to request a new password reset</p>";
echo "<p>2. Go to the forgot password form and request a new reset link</p>";
echo "<p>3. Use the new token to test the reset process</p>";

echo "<hr>";
echo "<p><a href='forgot_password_form.php'>Request New Password Reset</a></p>";
echo "<p><a href='debug_reset.php?token=" . urlencode($token) . "'>Check Current Token Status</a></p>";
?> 