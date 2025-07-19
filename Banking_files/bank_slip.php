<?php
include '../includes_files/connection.php';

if (isset($_GET['transaction_id'])) {
    $transaction_id = intval($_GET['transaction_id']);
    
    // Get transaction details
    $sql = "SELECT bt.*, ba.account_name, ba.account_number 
            FROM bank_transactions bt 
            JOIN bank_accounts ba ON bt.bank_account_id = ba.id 
            WHERE bt.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();
    
    if ($transaction) {
        $amount = floatval($transaction['amount']);
        $isPositive = $transaction['transaction_type'] === 'deposit' || $transaction['transaction_type'] === 'transfer_in';
        $amountSign = $isPositive ? '+' : '-';
        
        $slip_html = '
        <div class="slip-header">
            <div class="slip-title">BANK TRANSACTION SLIP</div>
            <div class="slip-date">' . date('F j, Y g:i A', strtotime($transaction['transaction_date'])) . '</div>
        </div>
        
        <div class="slip-details">
            <div class="slip-row">
                <span class="slip-label">Reference Number:</span>
                <span class="slip-value">' . htmlspecialchars($transaction['reference_number']) . '</span>
            </div>
            <div class="slip-row">
                <span class="slip-label">Account Name:</span>
                <span class="slip-value">' . htmlspecialchars($transaction['account_name']) . '</span>
            </div>
            <div class="slip-row">
                <span class="slip-label">Account Number:</span>
                <span class="slip-value">' . htmlspecialchars($transaction['account_number']) . '</span>
            </div>
            <div class="slip-row">
                <span class="slip-label">Transaction Type:</span>
                <span class="slip-value">' . strtoupper($transaction['transaction_type']) . '</span>
            </div>
            <div class="slip-row">
                <span class="slip-label">Description:</span>
                <span class="slip-value">' . htmlspecialchars($transaction['description']) . '</span>
            </div>';
        
        if ($transaction['category']) {
            $slip_html .= '
            <div class="slip-row">
                <span class="slip-label">Category:</span>
                <span class="slip-value">' . htmlspecialchars($transaction['category']) . '</span>
            </div>';
        }
        
        $slip_html .= '
            <div class="slip-row">
                <span class="slip-label">Amount:</span>
                <span class="slip-amount">' . $amountSign . '₱' . number_format($amount, 2) . '</span>
            </div>
            <div class="slip-row">
                <span class="slip-label">Balance Before:</span>
                <span class="slip-value">₱' . number_format($transaction['balance_before'], 2) . '</span>
            </div>
            <div class="slip-row">
                <span class="slip-label">Balance After:</span>
                <span class="slip-value">₱' . number_format($transaction['balance_after'], 2) . '</span>
            </div>';
        
        if ($transaction['notes']) {
            $slip_html .= '
            <div class="slip-row">
                <span class="slip-label">Notes:</span>
                <span class="slip-value">' . htmlspecialchars($transaction['notes']) . '</span>
            </div>';
        }
        
        $slip_html .= '
        </div>
        
        <div class="slip-footer">
            <p>This is a computer-generated receipt.</p>
            <p>Thank you for your transaction!</p>
        </div>';
        
        // Return the slip HTML
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'html' => $slip_html]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Transaction not found']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Transaction ID required']);
}
?> 