<?php include '../dashboard_things/sidebar_form.php'; ?>
<?php
include '../includes_files/connection.php';
// Fetch accounts for filter dropdown
$accounts_result = $conn->query("SELECT id, account_name, account_number FROM bank_accounts ORDER BY account_name");
$accounts = [];
while ($row = $accounts_result->fetch_assoc()) {
    $accounts[] = $row;
}
// Handle filters
$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all_time';
$specified_start = isset($_GET['specified_start']) ? $_GET['specified_start'] : '';
$specified_end = isset($_GET['specified_end']) ? $_GET['specified_end'] : '';
$where = [];
$params = [];
$types = '';
if ($account_id) {
    $where[] = 'bt.bank_account_id = ?';
    $params[] = $account_id;
    $types .= 'i';
}
if ($type) {
    $where[] = 'bt.transaction_type = ?';
    $params[] = $type;
    $types .= 's';
}
// Date filter logic
if ($date_filter !== 'all_time') {
    if ($date_filter === 'last_week') {
        $start = date('Y-m-d', strtotime('-1 week'));
        $end = date('Y-m-d');
    } elseif ($date_filter === 'last_month') {
        $start = date('Y-m-d', strtotime('-1 month'));
        $end = date('Y-m-d');
    } elseif ($date_filter === 'last_year') {
        $start = date('Y-m-d', strtotime('-1 year'));
        $end = date('Y-m-d');
    } elseif ($date_filter === 'specified' && $specified_start && $specified_end) {
        $start = $specified_start;
        $end = $specified_end;
    }
    if (isset($start) && isset($end)) {
        $where[] = 'DATE(bt.transaction_date) >= ?';
        $params[] = $start;
        $types .= 's';
        $where[] = 'DATE(bt.transaction_date) <= ?';
        $params[] = $end;
        $types .= 's';
    }
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT bt.*, ba.account_name, ba.account_number FROM bank_transactions bt JOIN bank_accounts ba ON bt.bank_account_id = ba.id $where_sql ORDER BY bt.transaction_date DESC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link rel="stylesheet" href="transaction.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-money-check-dollar"></i> Transactions</h1>
        </div>
        <form class="transactions-filter-bar" method="get" id="filterForm">
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label for="account_id" style="color:#1976d2; font-weight:500;">Account</label>
                <select name="account_id" id="account_id" style="padding:8px 12px; border-radius:6px; border:1px solid #b6c6e3; min-width:160px;">
                    <option value="">All Accounts</option>
                    <?php foreach (
                        $accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>" <?= ($account_id == $acc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($acc['account_name']) ?> (<?= htmlspecialchars($acc['account_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label for="type" style="color:#1976d2; font-weight:500;">Type</label>
                <select name="type" id="type" style="padding:8px 12px; border-radius:6px; border:1px solid #b6c6e3; min-width:140px;">
                    <option value="">All Types</option>
                    <option value="deposit" <?= ($type=='deposit')?'selected':'' ?>>Deposit</option>
                    <option value="withdrawal" <?= ($type=='withdrawal')?'selected':'' ?>>Withdrawal</option>
                    <option value="transfer_in" <?= ($type=='transfer_in')?'selected':'' ?>>Transfer In</option>
                    <option value="transfer_out" <?= ($type=='transfer_out')?'selected':'' ?>>Transfer Out</option>
                    <option value="fee" <?= ($type=='fee')?'selected':'' ?>>Fee</option>
                    <option value="interest" <?= ($type=='interest')?'selected':'' ?>>Interest</option>
                    <option value="adjustment" <?= ($type=='adjustment')?'selected':'' ?>>Adjustment</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: 4px; min-width:180px;">
                <label for="date_filter" style="color:#1976d2; font-weight:500;">Date Range</label>
                <select name="date_filter" id="date_filter" style="padding:8px 12px; border-radius:6px; border:1px solid #b6c6e3;">
                    <option value="all_time" <?= ($date_filter=='all_time')?'selected':'' ?>>All Time</option>
                    <option value="last_week" <?= ($date_filter=='last_week')?'selected':'' ?>>Last Week</option>
                    <option value="last_month" <?= ($date_filter=='last_month')?'selected':'' ?>>Last Month</option>
                    <option value="last_year" <?= ($date_filter=='last_year')?'selected':'' ?>>Last Year</option>
                    <option value="specified" <?= ($date_filter=='specified')?'selected':'' ?>>Specified Date</option>
                </select>
            </div>
            <div id="specifiedDates" style="display:<?= ($date_filter=='specified')?'flex':'none' ?>; flex-direction: row; gap: 12px; align-items: flex-end;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label for="specified_start" style="color:#1976d2; font-weight:500;">Starting Date</label>
                    <input type="date" name="specified_start" id="specified_start" value="<?= htmlspecialchars($specified_start) ?>" style="padding:8px 12px; border-radius:6px; border:1px solid #b6c6e3;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label for="specified_end" style="color:#1976d2; font-weight:500;">Up to What Date</label>
                    <input type="date" name="specified_end" id="specified_end" value="<?= htmlspecialchars($specified_end) ?>" style="padding:8px 12px; border-radius:6px; border:1px solid #b6c6e3;">
                </div>
            </div>
        </form>
        <script>
        // Auto-submit on any filter change
        const filterForm = document.getElementById('filterForm');
        ['account_id','type','date_filter'].forEach(id => {
            document.getElementById(id).addEventListener('change', function() {
                if (id === 'date_filter') {
                    const val = this.value;
                    const specified = document.getElementById('specifiedDates');
                    if (val === 'specified') {
                        specified.style.display = 'flex';
                    } else {
                        specified.style.display = 'none';
                        // Clear specified dates so they don't get submitted
                        document.getElementById('specified_start').value = '';
                        document.getElementById('specified_end').value = '';
                    }
                }
                filterForm.submit();
            });
        });
        ['specified_start','specified_end'].forEach(id => {
            document.getElementById(id).addEventListener('change', function() {
                filterForm.submit();
            });
        });
        </script>
        <div class="transactions-table-container">
            <h2 class="transactions-table-title">All Bank Transactions</h2>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Account Name</th>
                        <th>Account Number</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th>Category</th>
                        <th>Notes</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($row['transaction_date']))) ?></td>
                            <td><?= htmlspecialchars($row['account_name']) ?></td>
                            <td><?= htmlspecialchars($row['account_number']) ?></td>
                            <td>
                                <?php
                                $type = $row['transaction_type'];
                                $typeIcon = [
                                    'deposit' => '<i class="fa-solid fa-arrow-down"></i>',
                                    'withdrawal' => '<i class="fa-solid fa-arrow-up"></i>',
                                    'transfer_in' => '<i class="fa-solid fa-arrow-right-arrow-left"></i>',
                                    'transfer_out' => '<i class="fa-solid fa-arrow-right-arrow-left"></i>',
                                    'fee' => '<i class="fa-solid fa-receipt"></i>',
                                    'interest' => '<i class="fa-solid fa-percent"></i>',
                                    'adjustment' => '<i class="fa-solid fa-sliders"></i>',
                                ];
                                $typeLabel = [
                                    'deposit' => 'Deposit',
                                    'withdrawal' => 'Withdrawal',
                                    'transfer_in' => 'Transfer In',
                                    'transfer_out' => 'Transfer Out',
                                    'fee' => 'Fee',
                                    'interest' => 'Interest',
                                    'adjustment' => 'Adjustment',
                                ];
                                $badgeClass = 'type-badge ' . $type;
                                echo '<span class="' . $badgeClass . '">' . ($typeIcon[$type] ?? '') . ($typeLabel[$type] ?? ucfirst($type)) . '</span>';
                                ?>
                            </td>
                            <td style="text-align:right; color:<?= ($row['transaction_type']==='deposit'||$row['transaction_type']==='transfer_in') ? '#388e3c' : '#c62828' ?>; font-weight:600;">
                                <?= ($row['transaction_type']==='deposit'||$row['transaction_type']==='transfer_in') ? '+' : '-' ?>₱<?= number_format($row['amount'], 2) ?>
                            </td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['reference_number']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['notes']) ?></td>
                            <td style="text-align:right;">₱<?= number_format($row['balance_before'], 2) ?></td>
                            <td style="text-align:right;">₱<?= number_format($row['balance_after'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11">No transactions found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 