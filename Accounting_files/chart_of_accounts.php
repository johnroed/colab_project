<?php
require_once '../includes_files/auth_check.php';
requireLogin();
include '../includes_files/connection.php';

// Handle Edit Account
if (
    isset($_POST['edit_account_id']) &&
    isset($_POST['edit_account_name']) &&
    isset($_POST['edit_account_type']) &&
    !empty(trim($_POST['edit_account_id'])) &&
    !empty(trim($_POST['edit_account_name'])) &&
    !empty(trim($_POST['edit_account_type']))
) {
    $id = intval($_POST['edit_account_id']);
    $account_name = trim($_POST['edit_account_name']);
    $account_type = trim($_POST['edit_account_type']);
    $account_code = isset($_POST['edit_account_code']) ? trim($_POST['edit_account_code']) : null;
    $description = isset($_POST['edit_description']) ? trim($_POST['edit_description']) : null;
    $stmt = $conn->prepare("UPDATE chart_of_accounts SET account_name=?, account_type=?, account_code=?, description=? WHERE id=?");
    $stmt->bind_param("ssssi", $account_name, $account_type, $account_code, $description, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: chart_of_accounts_form.php');
    exit;
}

// Handle Delete Account
if (isset($_POST['delete_account_id']) && !empty(trim($_POST['delete_account_id']))) {
    $id = intval($_POST['delete_account_id']);
    // Placeholder: Check if account is referenced elsewhere (e.g., in journal entries)
    $in_use = false;
    // Example: Uncomment and adjust if you have a journal_entries table
    // $check = $conn->prepare("SELECT COUNT(*) FROM journal_entries WHERE account_id = ?");
    // $check->bind_param("i", $id);
    // $check->execute();
    // $check->bind_result($count);
    // $check->fetch();
    // $check->close();
    // if ($count > 0) $in_use = true;
    if (!$in_use) {
        $stmt = $conn->prepare("DELETE FROM chart_of_accounts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    // Optionally, you can set a session message if deletion is blocked
    header('Location: chart_of_accounts_form.php');
    exit;
}

// Handle Add Account (existing logic)
if (
    isset($_POST['account_name']) &&
    isset($_POST['account_type']) &&
    !empty(trim($_POST['account_name'])) &&
    !empty(trim($_POST['account_type']))
) {
    $account_name = trim($_POST['account_name']);
    $account_type = trim($_POST['account_type']);
    $account_code = isset($_POST['account_code']) ? trim($_POST['account_code']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $stmt = $conn->prepare("INSERT INTO chart_of_accounts (account_name, account_type, account_code, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $account_name, $account_type, $account_code, $description);
    $stmt->execute();
    $stmt->close();
}
header('Location: chart_of_accounts_form.php');
exit; 