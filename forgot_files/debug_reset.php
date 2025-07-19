<?php
session_start();
include '../includes_files/connection.php';

echo "<h2>Password Reset Debug</h2>";

// Check if we have a token
$token = isset($_GET['token']) ? $_GET['token'] : '';
echo "<p><strong>Token:</strong> " . htmlspecialchars($token) . "</p>";

if (!empty($token)) {
    // Check if token exists in database
    $find_user = $conn->prepare('SELECT user_id, reset_code_expiry, reset_code_used, reset_code FROM user_login WHERE reset_code = ? LIMIT 1');
    $find_user->bind_param('s', $token);
    $find_user->execute();
    $result = $find_user->get_result();
    
    echo "<p><strong>Database Query Results:</strong></p>";
    echo "<p>Number of rows found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<p><strong>User ID:</strong> " . $user['user_id'] . "</p>";
        echo "<p><strong>Reset Code:</strong> " . htmlspecialchars($user['reset_code']) . "</p>";
        echo "<p><strong>Reset Code Expiry:</strong> " . $user['reset_code_expiry'] . "</p>";
        echo "<p><strong>Reset Code Used:</strong> " . ($user['reset_code_used'] ? 'Yes' : 'No') . "</p>";
        
        $now = time();
        $expiry_time = strtotime($user['reset_code_expiry']);
        echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s', $now) . "</p>";
        echo "<p><strong>Expiry Time:</strong> " . date('Y-m-d H:i:s', $expiry_time) . "</p>";
        echo "<p><strong>Time Difference:</strong> " . ($expiry_time - $now) . " seconds</p>";
        echo "<p><strong>Is Expired:</strong> " . ($expiry_time < $now ? 'Yes' : 'No') . "</p>";
        
        if (!$user['reset_code_used'] && $expiry_time > $now) {
            echo "<p style='color: green;'><strong>✅ Token is valid!</strong></p>";
            echo "<p><a href='reset_password_form.php?token=" . urlencode($token) . "'>Go to Reset Form</a></p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Token is invalid!</strong></p>";
            if ($user['reset_code_used']) {
                echo "<p>Reason: Token has already been used</p>";
            }
            if ($expiry_time < $now) {
                echo "<p>Reason: Token has expired</p>";
            }
        }
    } else {
        echo "<p style='color: red;'><strong>❌ Token not found in database!</strong></p>";
    }
    
    $find_user->close();
} else {
    echo "<p style='color: red;'><strong>❌ No token provided!</strong></p>";
}

echo "<hr>";
echo "<p><a href='forgot_password_form.php'>Back to Forgot Password</a></p>";
?> 