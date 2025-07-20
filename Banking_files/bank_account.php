<?php
require_once '../includes_files/auth_check.php';
requireLogin();
include '../includes_files/connection.php';

// Handle Add Account
if (isset($_POST['account_name']) && isset($_POST['account_type']) && isset($_POST['initial_balance'])) {
    $account_name = trim($_POST['account_name']);
    $account_type = trim($_POST['account_type']);
    $initial_balance = floatval($_POST['initial_balance']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // Generate a unique random account number
    function generateUniqueAccountNumber($conn) {
        do {
            $account_number = 'ACCT-' . mt_rand(10000000, 99999999);
            $check_stmt = $conn->prepare("SELECT id FROM bank_accounts WHERE account_number = ?");
            $check_stmt->bind_param("s", $account_number);
            $check_stmt->execute();
            $check_stmt->store_result();
            $exists = $check_stmt->num_rows > 0;
            $check_stmt->close();
        } while ($exists);
        return $account_number;
    }
    $account_number = generateUniqueAccountNumber($conn);

    // Insert new account
    $stmt = $conn->prepare("INSERT INTO bank_accounts (account_name, account_number, account_type, initial_balance, current_balance, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdds", $account_name, $account_number, $account_type, $initial_balance, $initial_balance, $description);

    if ($stmt->execute()) {
        $account_id = $stmt->insert_id;
        $stmt->close();

        // Create initial deposit transaction
        if ($initial_balance > 0) {
            $ref_number = 'INIT-' . str_pad($account_id, 3, '0', STR_PAD_LEFT);
            $transaction_stmt = $conn->prepare("INSERT INTO bank_transactions (bank_account_id, transaction_type, amount, balance_before, balance_after, description, reference_number, category) VALUES (?, 'deposit', ?, 0, ?, ?, ?, 'Initial Setup')");
            $transaction_stmt->bind_param("iddss", $account_id, $initial_balance, $initial_balance, "Initial deposit for $account_name", $ref_number);
            $transaction_stmt->execute();
            $transaction_stmt->close();
        }

        header('Location: bank_account_form.php?success=Account created successfully');
    } else {
        header('Location: bank_account_form.php?error=Failed to create account');
    }
    exit;
}

// Handle Edit Account
if (isset($_POST['edit_account_id']) && isset($_POST['edit_account_name'])) {
    $account_id = intval($_POST['edit_account_id']);
    $account_name = trim($_POST['edit_account_name']);
    $account_type = trim($_POST['edit_account_type']);
    $description = isset($_POST['edit_description']) ? trim($_POST['edit_description']) : null;
    
    $stmt = $conn->prepare("UPDATE bank_accounts SET account_name=?, account_type=?, description=? WHERE id=?");
    $stmt->bind_param("sssi", $account_name, $account_type, $description, $account_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: bank_account_form.php?success=Account updated successfully');
    exit;
}

// Handle Delete Account
if (isset($_POST['delete_account_id'])) {
    $account_id = intval($_POST['delete_account_id']);
    
    // Check if account has transactions
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM bank_transactions WHERE bank_account_id = ?");
    $check_stmt->bind_param("i", $account_id);
    $check_stmt->execute();
    $check_stmt->bind_result($transaction_count);
    $check_stmt->fetch();
    $check_stmt->close();
    
    if ($transaction_count > 0) {
        header('Location: bank_account_form.php?error=Cannot delete account with existing transactions');
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM bank_accounts WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: bank_account_form.php?success=Account deleted successfully');
    exit;
}

// Fallback redirect
header('Location: bank_account_form.php');
exit;
?> 