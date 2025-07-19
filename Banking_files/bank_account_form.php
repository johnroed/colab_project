<?php include '../dashboard_things/sidebar_form.php'; ?>
<?php
include '../includes_files/connection.php';

$accounts = [];
$sql = "SELECT * FROM bank_accounts ORDER BY account_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
}

$total_balance = 0;
foreach ($accounts as $account) {
    $total_balance += $account['current_balance'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Accounts</title>
    <link rel="stylesheet" href="bank_account.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-university"></i> Bank Accounts</h1>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="message success-message">
                <i class="fa-solid fa-check-circle"></i>
                <span><?= htmlspecialchars($_GET['success']) ?></span>
                <button class="message-close">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="message error-message">
                <i class="fa-solid fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
                <button class="message-close">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="bank-summary">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="summary-content">
                    <h3>Total Balance</h3>
                    <p class="summary-amount">₱<?= number_format($total_balance, 2) ?></p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="summary-content">
                    <h3>Active Accounts</h3>
                    <p class="summary-count"><?= count($accounts) ?></p>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="add-button" id="openAddAccountModal">
                <i class="fa-solid fa-plus"></i> Add Account
            </button>
            <button class="add-button" id="openTransactionModal" style="background: #4caf50;">
                <i class="fa-solid fa-exchange-alt"></i> New Transaction
            </button>
        </div>

        <!-- Add Account Modal -->
        <div id="addAccountModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add New Bank Account</h2>
                </div>
                <span class="close-modal" id="closeAddAccountModal">&times;</span>
                <form class="add-account-form" method="POST" action="bank_account.php">
                    <div class="form-row">
                        <label for="account_name">Account Name</label>
                        <input type="text" id="account_name" name="account_name" required>
                    </div>
                    <div class="form-row">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number">
                    </div>
                    <div class="form-row">
                        <label for="account_type">Account Type</label>
                        <select id="account_type" name="account_type" required>
                            <option value="">Select Type</option>
                            <option value="checking">Checking</option>
                            <option value="savings">Savings</option>
                            <option value="business">Business</option>
                            <option value="investment">Investment</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="initial_balance">Initial Balance</label>
                        <input type="number" id="initial_balance" name="initial_balance" step="0.01" min="0" required>
                    </div>
                    <div class="form-row">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Add Account</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transaction Modal -->
        <div id="transactionModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>New Transaction</h2>
                </div>
                <span class="close-modal" id="closeTransactionModal">&times;</span>
                <form class="transaction-form" method="POST" action="bank_transaction.php">
                    <div class="form-row">
                        <label for="bank_account_id">Account</label>
                        <select id="bank_account_id" name="bank_account_id" required>
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?> (₱<?= number_format($account['current_balance'], 2) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="transaction_type">Transaction Type</label>
                        <select id="transaction_type" name="transaction_type" required>
                            <option value="">Select Type</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                            <option value="transfer_out">Transfer Out</option>
                        </select>
                    </div>
                    <div class="form-row" id="transfer_to_row" style="display: none;">
                        <label for="transfer_to_account">Transfer To</label>
                        <select id="transfer_to_account" name="transfer_to_account">
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-row">
                        <label for="transaction_description">Description</label>
                        <input type="text" id="transaction_description" name="description" required>
                    </div>
                    <div class="form-row">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" placeholder="e.g., Sales, Expenses, Transfer">
                    </div>
                    <div class="form-row">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Process Transaction</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="accounts-container">
            <h2 class="accounts-title">Account Overview</h2>
            <div class="accounts-grid">
                <?php if (count($accounts) > 0): ?>
                    <?php foreach ($accounts as $account): ?>
                        <div class="account-card">
                            <div class="account-header">
                                <div class="account-icon">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div class="account-info">
                                    <h3><?= htmlspecialchars($account['account_name']) ?></h3>
                                    <p class="account-number"><?= htmlspecialchars($account['account_number']) ?></p>
                                    <span class="account-type-badge"><?= ucfirst($account['account_type']) ?></span>
                                </div>
                            </div>
                            <div class="account-balance">
                                <h4>Current Balance</h4>
                                <p class="balance-amount">₱<?= number_format($account['current_balance'], 2) ?></p>
                            </div>
                            <div class="account-actions">
                                <button class="account-btn view-transactions" data-account-id="<?= $account['id'] ?>" data-account-name="<?= htmlspecialchars($account['account_name']) ?>">
                                    <i class="fa-solid fa-list"></i> Transactions
                                </button>
                                <button class="account-btn edit-account" data-account-id="<?= $account['id'] ?>">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-accounts">
                        <i class="fa-solid fa-building-columns"></i>
                        <h3>No Bank Accounts</h3>
                        <p>Create your first bank account to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transactions History Modal -->
        <div id="transactionsModal" class="modal-overlay">
            <div class="modal-content transactions-modal">
                <div class="modal-header-row">
                    <h2 id="transactionsTitle">Transaction History</h2>
                </div>
                <span class="close-modal" id="closeTransactionsModal">&times;</span>
                <div id="transactionsList" class="transactions-list">
                    <!-- Transactions will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Bank Slip Modal -->
        <div id="bankSlipModal" class="modal-overlay">
            <div class="modal-content bank-slip-modal">
                <div class="modal-header-row">
                    <h2>Bank Slip</h2>
                    <button class="print-slip-btn" onclick="printBankSlip()">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>
                <span class="close-modal" id="closeBankSlipModal">&times;</span>
                <div id="bankSlipContent" class="bank-slip-content">
                    <!-- Bank slip content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <style>
    .message {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 500;
        position: relative;
    }
    .success-message {
        background: #e8f5e8;
        color: #2e7d32;
        border: 1px solid #4caf50;
    }
    .error-message {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #f44336;
    }
    .message i {
        font-size: 18px;
    }
    .message-close {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: inherit;
        opacity: 0.7;
    }
    .message-close:hover {
        opacity: 1;
    }
    .bank-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(25, 118, 210, 0.08);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .summary-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #1976d2, #1565c0);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }
    .summary-content h3 {
        margin: 0 0 8px 0;
        color: #666;
        font-size: 14px;
        font-weight: 500;
    }
    .summary-amount {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #1976d2;
    }
    .summary-count {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #4caf50;
    }
    .action-buttons {
        margin: 20px 0;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .add-button {
        display: inline-flex;
        align-items: center;
        padding: 12px 28px;
        background-color: #1976d2;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.10);
        transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
        gap: 10px;
    }
    .add-button:hover {
        background-color: #1565c0;
        box-shadow: 0 4px 16px rgba(25, 118, 210, 0.18);
        transform: translateY(-2px) scale(1.03);
    }
    .add-button i {
        margin-right: 10px;
        font-size: 20px;
    }
    .modal-overlay {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(25, 118, 210, 0.10);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(25, 118, 210, 0.18);
        padding: 36px 28px 24px 28px;
        min-width: 400px;
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
    }
    .transactions-modal {
        min-width: 600px;
        max-width: 800px;
    }
    .bank-slip-modal {
        min-width: 500px;
        max-width: 600px;
    }
    .modal-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f6ff;
    }
    .modal-header-row h2 {
        margin: 0;
        color: #1976d2;
        font-size: 1.5rem;
        font-weight: 600;
    }
    .print-slip-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #1976d2;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .print-slip-btn:hover {
        background: #1565c0;
    }
    .close-modal {
        color: #1976d2;
        position: absolute;
        top: 12px;
        right: 18px;
        font-size: 2.2rem;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
        outline: none;
        z-index: 10;
        transition: color 0.2s, transform 0.2s;
    }
    .close-modal:hover {
        color: #e53935;
        transform: scale(1.18);
    }
    .add-account-form, .transaction-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .form-row {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-row label {
        font-size: 1rem;
        font-weight: 500;
        color: #1976d2;
        margin-bottom: 2px;
    }
    .form-row input,
    .form-row select,
    .form-row textarea {
        padding: 10px 12px;
        border: 1px solid #b6c6e3;
        border-radius: 6px;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-row input:focus,
    .form-row select:focus,
    .form-row textarea:focus {
        border-color: #1976d2;
    }
    .form-row.form-actions {
        display: flex;
        justify-content: center;
        margin-top: 8px;
    }
    .form-row.form-actions .add-button {
        width: fit-content;
        min-width: unset;
        max-width: 100%;
        flex: 0 0 auto;
        align-self: center;
        padding-left: 32px;
        padding-right: 32px;
        margin: 0 auto;
    }
    .accounts-container {
        margin-top: 40px;
    }
    .accounts-title {
        margin: 0 0 24px 0;
        color: #1976d2;
        font-weight: 600;
        font-size: 1.35rem;
        letter-spacing: 0.5px;
    }
    .accounts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 24px;
    }
    .account-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(25, 118, 210, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .account-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(25, 118, 210, 0.15);
    }
    .account-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }
    .account-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #1976d2, #1565c0);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    .account-info h3 {
        margin: 0 0 4px 0;
        color: #333;
        font-size: 1.2rem;
        font-weight: 600;
    }
    .account-number {
        margin: 0 0 8px 0;
        color: #666;
        font-size: 0.9rem;
    }
    .account-type-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .account-balance {
        margin-bottom: 20px;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .account-balance h4 {
        margin: 0 0 8px 0;
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .balance-amount {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: #1976d2;
    }
    .account-actions {
        display: flex;
        gap: 8px;
    }
    .account-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 16px;
        font-size: 0.9rem;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .view-transactions {
        background: #1976d2;
        color: white;
    }
    .view-transactions:hover {
        background: #1565c0;
    }
    .edit-account {
        background: #f0f6ff;
        color: #1976d2;
        border: 1px solid #1976d2;
    }
    .edit-account:hover {
        background: #1976d2;
        color: white;
    }
    .no-accounts {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    .no-accounts i {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 16px;
    }
    .no-accounts h3 {
        margin: 0 0 8px 0;
        color: #333;
    }
    .no-accounts p {
        margin: 0;
        color: #666;
    }
    .transactions-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .transaction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border-bottom: 1px solid #e0e0e0;
        transition: background 0.2s;
    }
    .transaction-item:hover {
        background: #f5f5f5;
    }
    .transaction-item:last-child {
        border-bottom: none;
    }
    .transaction-info {
        flex: 1;
    }
    .transaction-type {
        font-weight: 600;
        margin-bottom: 4px;
    }
    .transaction-description {
        color: #666;
        font-size: 0.9rem;
    }
    .transaction-amount {
        font-weight: 700;
        font-size: 1.1rem;
    }
    .amount-positive {
        color: #4caf50;
    }
    .amount-negative {
        color: #f44336;
    }
    .bank-slip-content {
        background: #f8f9fa;
        border: 2px solid #1976d2;
        border-radius: 8px;
        padding: 24px;
        font-family: 'Courier New', monospace;
    }
    .slip-header {
        text-align: center;
        border-bottom: 2px solid #1976d2;
        padding-bottom: 16px;
        margin-bottom: 20px;
    }
    .slip-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1976d2;
        margin: 0 0 8px 0;
    }
    .slip-date {
        color: #666;
        font-size: 0.9rem;
    }
    .slip-details {
        margin-bottom: 20px;
    }
    .slip-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .slip-label {
        font-weight: bold;
        color: #333;
    }
    .slip-value {
        color: #666;
    }
    .slip-amount {
        font-size: 1.3rem;
        font-weight: bold;
        color: #1976d2;
    }
    .slip-footer {
        text-align: center;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #ddd;
        color: #666;
        font-size: 0.8rem;
    }
    @media (max-width: 768px) {
        .accounts-grid {
            grid-template-columns: 1fr;
        }
        .modal-content {
            min-width: 90vw;
            margin: 20px;
        }
        .transactions-modal {
            min-width: 90vw;
        }
        .bank-slip-modal {
            min-width: 90vw;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const openAddBtn = document.getElementById('openAddAccountModal');
        const closeAddBtn = document.getElementById('closeAddAccountModal');
        const addModal = document.getElementById('addAccountModal');
        
        const openTransactionBtn = document.getElementById('openTransactionModal');
        const closeTransactionBtn = document.getElementById('closeTransactionModal');
        const transactionModal = document.getElementById('transactionModal');
        
        const openTransactionsBtn = document.querySelectorAll('.view-transactions');
        const closeTransactionsBtn = document.getElementById('closeTransactionsModal');
        const transactionsModal = document.getElementById('transactionsModal');
        
        const closeBankSlipBtn = document.getElementById('closeBankSlipModal');
        const bankSlipModal = document.getElementById('bankSlipModal');
        
        // Message close functionality
        const messageCloseBtns = document.querySelectorAll('.message-close');
        messageCloseBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);
        
        // Add Account Modal
        if (openAddBtn && addModal) {
            openAddBtn.onclick = function() {
                addModal.style.display = 'flex';
            };
        }
        if (closeAddBtn && addModal) {
            closeAddBtn.onclick = function() {
                addModal.style.display = 'none';
            };
        }
        
        // Transaction Modal
        if (openTransactionBtn && transactionModal) {
            openTransactionBtn.onclick = function() {
                transactionModal.style.display = 'flex';
            };
        }
        if (closeTransactionBtn && transactionModal) {
            closeTransactionBtn.onclick = function() {
                transactionModal.style.display = 'none';
            };
        }
        
        // Transactions History Modal
        openTransactionsBtn.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const accountId = btn.getAttribute('data-account-id');
                const accountName = btn.getAttribute('data-account-name');
                document.getElementById('transactionsTitle').textContent = accountName + ' - Transaction History';
                loadTransactions(accountId);
                transactionsModal.style.display = 'flex';
            });
        });
        
        if (closeTransactionsBtn && transactionsModal) {
            closeTransactionsBtn.onclick = function() {
                transactionsModal.style.display = 'none';
            };
        }
        
        // Bank Slip Modal
        if (closeBankSlipBtn && bankSlipModal) {
            closeBankSlipBtn.onclick = function() {
                bankSlipModal.style.display = 'none';
            };
        }
        
        // Transfer functionality
        const transactionType = document.getElementById('transaction_type');
        const transferToRow = document.getElementById('transfer_to_row');
        
        if (transactionType) {
            transactionType.addEventListener('change', function() {
                if (this.value === 'transfer_out') {
                    transferToRow.style.display = 'block';
                    document.getElementById('transfer_to_account').required = true;
                } else {
                    transferToRow.style.display = 'none';
                    document.getElementById('transfer_to_account').required = false;
                }
            });
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }
        
        function loadTransactions(accountId) {
            document.getElementById('transactionsList').innerHTML = 
                '<p style="text-align: center; color: #666; padding: 40px;">Loading transactions...</p>';
            
            fetch(`bank_transaction.php?get_transactions=1&account_id=${accountId}`)
                .then(response => response.json())
                .then(data => {
                    displayTransactions(data);
                })
                .catch(error => {
                    document.getElementById('transactionsList').innerHTML = 
                        '<p style="text-align: center; color: #f44336; padding: 40px;">Error loading transactions</p>';
                });
        }
        
        function displayTransactions(transactions) {
            const container = document.getElementById('transactionsList');
            
            if (transactions.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666; padding: 40px;">No transactions found</p>';
                return;
            }
            
            let html = '';
            transactions.forEach(function(transaction) {
                const amount = parseFloat(transaction.amount);
                const isPositive = transaction.transaction_type === 'deposit' || transaction.transaction_type === 'transfer_in';
                const amountClass = isPositive ? 'amount-positive' : 'amount-negative';
                const amountSign = isPositive ? '+' : '-';
                
                html += `
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-type">${transaction.transaction_type.toUpperCase()}</div>
                            <div class="transaction-description">${transaction.description}</div>
                            <div style="font-size: 0.8rem; color: #999;">${transaction.reference_number} • ${new Date(transaction.transaction_date).toLocaleDateString()}</div>
                        </div>
                        <div class="transaction-actions">
                            <div class="transaction-amount ${amountClass}">
                                ${amountSign}₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}
                            </div>
                            <button class="slip-btn" onclick="generateBankSlip(${transaction.id})" title="Generate Bank Slip">
                                <i class="fa-solid fa-receipt"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
    });
    
    function printBankSlip() {
        const slipContent = document.getElementById('bankSlipContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Bank Slip</title>
                    <style>
                        body { font-family: 'Courier New', monospace; margin: 20px; }
                        .slip-header { text-align: center; border-bottom: 2px solid #1976d2; padding-bottom: 16px; margin-bottom: 20px; }
                        .slip-title { font-size: 1.5rem; font-weight: bold; color: #1976d2; margin: 0 0 8px 0; }
                        .slip-date { color: #666; font-size: 0.9rem; }
                        .slip-details { margin-bottom: 20px; }
                        .slip-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
                        .slip-label { font-weight: bold; color: #333; }
                        .slip-value { color: #666; }
                        .slip-amount { font-size: 1.3rem; font-weight: bold; color: #1976d2; }
                        .slip-footer { text-align: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid #ddd; color: #666; font-size: 0.8rem; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    ${slipContent}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
    </script>
</body>
</html> 