<?php
include 'includes_files/connection.php';

$admin_email = 'johnroedlahaylahay2231@gmail.com';
$admin_password = password_hash('Roedadmin@320102', PASSWORD_DEFAULT);
$admin_phone = '09637330408';
$user_role = 'admin';
$status = 'active';
$email_confirmed = 1;
$phone_confirmed = 1;
$two_step_on = 0;
$failed_attempts = 0;
$last_failed_attempt = null;

$sql = "INSERT INTO user_login (
    email_address, password_secret, user_role, email_confirmed, phone_number, phone_confirmed, two_step_on, failed_attempts, last_failed_attempt, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param(
        'sssisisiss',
        $admin_email,
        $admin_password,
        $user_role,
        $email_confirmed,
        $admin_phone,
        $phone_confirmed,
        $two_step_on,
        $failed_attempts,
        $last_failed_attempt,
        $status
    );
    if ($stmt->execute()) {
        echo "Admin account inserted successfully.";
    } else {
        echo "Error inserting admin account: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close(); 