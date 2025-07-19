<?php
include '../includes_files/connection.php';

// Handle New Transaction
if (isset($_POST['bank_account_id']) && isset($_POST['transaction_type']) && isset($_POST['amount']) && isset($_POST['description'])) {
    $bank_account_id = intval($_POST['bank_account_id']);
    $transaction_type = trim($_POST['transaction_type']);
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $category = isset($_POST['category']) ? trim($_POST['category']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
    $transfer_to_account = isset($_POST['transfer_to_account']) ? intval($_POST['transfer_to_account']) : null;
    
    // Validate amount
    if ($amount <= 0) {
        header('Location: bank_account_form.php?error=Amount must be greater than zero');
        exit;
    }
    
    // Get current account balance
    $balance_stmt = $conn->prepare("SELECT current_balance, account_name FROM bank_accounts WHERE id = ?");
    $balance_stmt->bind_param("i", $bank_account_id);
    $balance_stmt->execute();
    $balance_stmt->bind_result($current_balance, $account_name);
    $balance_stmt->fetch();
    $balance_stmt->close();
    
    if (!$current_balance) {
        header('Location: bank_account_form.php?error=Account not found');
        exit;
    }
    
    // Calculate new balance
    $balance_before = $current_balance;
    $balance_after = $current_balance;
    
    switch ($transaction_type) {
        case 'deposit':
            $balance_after = $current_balance + $amount;
            break;
        case 'withdrawal':
            if ($amount > $current_balance) {
                header('Location: bank_account_form.php?error=Insufficient funds');
                exit;
            }
            $balance_after = $current_balance - $amount;
            break;
        case 'transfer_out':
            if ($amount > $current_balance) {
                header('Location: bank_account_form.php?error=Insufficient funds for transfer');
                exit;
            }
            if (!$transfer_to_account || $transfer_to_account == $bank_account_id) {
                header('Location: bank_account_form.php?error=Invalid transfer account');
                exit;
            }
            $balance_after = $current_balance - $amount;
            break;
        default:
            header('Location: bank_account_form.php?error=Invalid transaction type');
            exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate reference number
        $ref_number = 'TXN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Insert transaction record
        $transaction_stmt = $conn->prepare("INSERT INTO bank_transactions (bank_account_id, transaction_type, amount, balance_before, balance_after, description, reference_number, category, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $transaction_stmt->bind_param("isddssss", $bank_account_id, $transaction_type, $amount, $balance_before, $balance_after, $description, $ref_number, $category, $notes);
        $transaction_stmt->execute();
        $transaction_id = $conn->insert_id;
        $transaction_stmt->close();
        
        // Update account balance
        $update_stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = ? WHERE id = ?");
        $update_stmt->bind_param("di", $balance_after, $bank_account_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Handle transfer to account
        if ($transaction_type === 'transfer_out' && $transfer_to_account) {
            // Get transfer to account balance
            $to_balance_stmt = $conn->prepare("SELECT current_balance, account_name FROM bank_accounts WHERE id = ?");
            $to_balance_stmt->bind_param("i", $transfer_to_account);
            $to_balance_stmt->execute();
            $to_balance_stmt->bind_result($to_current_balance, $to_account_name);
            $to_balance_stmt->fetch();
            $to_balance_stmt->close();
            
            if ($to_current_balance !== null) {
                $to_balance_after = $to_current_balance + $amount;
                
                // Insert transfer in transaction
                $transfer_in_stmt = $conn->prepare("INSERT INTO bank_transactions (bank_account_id, transaction_type, amount, balance_before, balance_after, description, reference_number, category, notes, related_transaction_id) VALUES (?, 'transfer_in', ?, ?, ?, ?, ?, ?, ?, ?)");
                $transfer_description = "Transfer from $account_name";
                $transfer_ref = 'TXN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $transfer_in_stmt->bind_param("isddssssi", $transfer_to_account, $amount, $to_current_balance, $to_balance_after, $transfer_description, $transfer_ref, $category, $notes, $transaction_id);
                $transfer_in_stmt->execute();
                $transfer_in_stmt->close();
                
                // Update transfer to account balance
                $update_to_stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = ? WHERE id = ?");
                $update_to_stmt->bind_param("di", $to_balance_after, $transfer_to_account);
                $update_to_stmt->execute();
                $update_to_stmt->close();
            }
        }
        
        $conn->commit();
        
        // Redirect with success message and transaction details
        $success_message = "Transaction processed successfully. Reference: $ref_number";
        header("Location: bank_account_form.php?success=" . urlencode($success_message));
        
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: bank_account_form.php?error=Transaction failed: ' . $e->getMessage());
    }
    
    exit;
}

// Handle Get Transactions (for AJAX)
if (isset($_GET['get_transactions']) && isset($_GET['account_id'])) {
    $account_id = intval($_GET['account_id']);
    
    $transactions = [];
    $sql = "SELECT bt.*, ba.account_name 
            FROM bank_transactions bt 
            JOIN bank_accounts ba ON bt.bank_account_id = ba.id 
            WHERE bt.bank_account_id = ? 
            ORDER BY bt.transaction_date DESC 
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($transactions);
    exit;
}

// Fallback redirect
header('Location: bank_account_form.php');
exit;
?> 