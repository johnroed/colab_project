<?php include '../dashboard_things/sidebar_form.php'; ?>
<?php
include '../includes_files/connection.php';
$accounts = [];
$sql = "SELECT * FROM chart_of_accounts ORDER BY account_type, account_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart of Accounts</title>
    <link rel="stylesheet" href="chart_of_accounts.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-diagram-project"></i> Chart of Accounts</h1>
        </div>
        <div class="action-buttons">
            <button class="add-button" id="openAddAccountModal">
                <i class="fa-solid fa-plus"></i> Add Account
            </button>
        </div>
        <div id="addAccountModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add New Account</h2>
                </div>
                <span class="close-modal" id="closeAddAccountModal">&times;</span>
                <form class="add-account-form" method="POST" action="chart_of_accounts.php">
                    <div class="form-row">
                        <label for="account_name">Account Name</label>
                        <input type="text" id="account_name" name="account_name" required>
                    </div>
                    <div class="form-row">
                        <label for="account_type">Account Type</label>
                        <select id="account_type" name="account_type" required>
                            <option value="">Select Type</option>
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="account_code">Account Code/Number</label>
                        <input type="text" id="account_code" name="account_code">
                    </div>
                    <div class="form-row">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description">
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Add Account</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Edit Account Modal -->
        <div id="editAccountModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Edit Account</h2>
                </div>
                <span class="close-modal" id="closeEditAccountModal">&times;</span>
                <form class="edit-account-form" method="POST" action="chart_of_accounts.php">
                    <input type="hidden" id="edit_account_id" name="edit_account_id">
                    <div class="form-row">
                        <label for="edit_account_name">Account Name</label>
                        <input type="text" id="edit_account_name" name="edit_account_name" required>
                    </div>
                    <div class="form-row">
                        <label for="edit_account_type">Account Type</label>
                        <select id="edit_account_type" name="edit_account_type" required>
                            <option value="">Select Type</option>
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="edit_account_code">Account Code/Number</label>
                        <input type="text" id="edit_account_code" name="edit_account_code">
                    </div>
                    <div class="form-row">
                        <label for="edit_description">Description</label>
                        <input type="text" id="edit_description" name="edit_description">
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Delete Account Modal -->
        <div id="deleteAccountModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Delete Account</h2>
                </div>
                <span class="close-modal" id="closeDeleteAccountModal">&times;</span>
                <form class="delete-account-form" method="POST" action="chart_of_accounts.php">
                    <input type="hidden" id="delete_account_id" name="delete_account_id">
                    <div class="form-row">
                        <p>Are you sure you want to delete the account <span id="delete_account_name" style="font-weight:bold;"></span>?</p>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button" style="background:#d32f2f;">Delete</button>
                        <button type="button" class="add-button" id="cancelDeleteAccount" style="background:#bdbdbd;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="accounts-table-container">
            <h2 class="accounts-table-title">Accounts List</h2>
            <table class="accounts-table">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Code/Number</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($accounts) > 0): ?>
                    <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['account_name']) ?></td>
                            <td><?= htmlspecialchars($acc['account_type']) ?></td>
                            <td><?= htmlspecialchars($acc['account_code']) ?></td>
                            <td><?= htmlspecialchars($acc['description']) ?></td>
                            <td>
                                <button class="edit-account-btn" data-id="<?= $acc['id'] ?>" data-name="<?= htmlspecialchars($acc['account_name']) ?>" data-type="<?= htmlspecialchars($acc['account_type']) ?>" data-code="<?= htmlspecialchars($acc['account_code']) ?>" data-description="<?= htmlspecialchars($acc['description']) ?>"><i class="fa-solid fa-pen"></i> Edit</button>
                                <button class="delete-account-btn" data-id="<?= $acc['id'] ?>" data-name="<?= htmlspecialchars($acc['account_name']) ?>"><i class="fa-solid fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No accounts found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <style>
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
            min-width: 340px;
            max-width: 400px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .close-modal {
            position: absolute;
            top: 0px;
            right: 18px;
            font-size: 1.6rem;
            color: #1976d2;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
            z-index: 10;
        }
        .close-modal:hover {
            color: #1565c0;
        }
        .add-account-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .form-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .form-row label {
            font-size: 1rem;
            font-weight: 500;
            color: #1976d2;
            margin-bottom: 2px;
        }
        .form-row input,
        .form-row select {
            padding: 8px 12px;
            border: 1px solid #b6c6e3;
            border-radius: 6px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-row input:focus,
        .form-row select:focus {
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
        .accounts-table-container {
            margin-top: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.08), 0 1.5px 4px rgba(0,0,0,0.04);
            padding: 24px 18px 18px 18px;
        }
        .accounts-table-title {
            margin: 0 0 18px 0;
            color: #1976d2;
            font-weight: 600;
            font-size: 1.35rem;
            letter-spacing: 0.5px;
        }
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            table-layout: fixed;
        }
        .accounts-table th,
        .accounts-table td {
            vertical-align: middle;
            padding: 12px 10px;
            line-height: 1.4;
        }
        .accounts-table th {
            background: #1976d2;
            color: #fff;
            font-weight: 600;
            border: none;
            text-align: left;
        }
        .accounts-table td {
            border-bottom: 1px solid #e3e8ee;
            font-size: 1.04rem;
            background: #fff;
            text-align: left;
        }
        .accounts-table tr:last-child td {
            border-bottom: none;
        }
        .accounts-table tr {
            transition: background 0.18s;
        }
        .accounts-table tbody tr:hover {
            background: #f0f6ff;
        }
        .accounts-table td:last-child, .accounts-table th:last-child {
            text-align: center;
        }
        .edit-account-btn, .delete-account-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 4px;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            box-shadow: 0 1.5px 4px rgba(25,118,210,0.08);
        }
        .edit-account-btn {
            background: #1976d2;
            color: #fff;
        }
        .edit-account-btn:hover {
            background: #1565c0;
            color: #fff;
        }
        .delete-account-btn {
            background: #fff0f0;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }
        .delete-account-btn:hover {
            background: #d32f2f;
            color: #fff;
        }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openBtn = document.getElementById('openAddAccountModal');
            const closeBtn = document.getElementById('closeAddAccountModal');
            const modal = document.getElementById('addAccountModal');
            if (openBtn && modal) {
                openBtn.onclick = function() {
                    modal.style.display = 'flex';
                };
            }
            if (closeBtn && modal) {
                closeBtn.onclick = function() {
                    modal.style.display = 'none';
                };
            }
            const editBtns = document.querySelectorAll('.edit-account-btn');
            const deleteBtns = document.querySelectorAll('.delete-account-btn');
            const editModal = document.getElementById('editAccountModal');
            const closeEditBtn = document.getElementById('closeEditAccountModal');
            const editForm = document.querySelector('.edit-account-form');
            const deleteModal = document.getElementById('deleteAccountModal');
            const closeDeleteBtn = document.getElementById('closeDeleteAccountModal');
            const deleteForm = document.querySelector('.delete-account-form');
            const cancelDeleteBtn = document.getElementById('cancelDeleteAccount');

            // Edit button logic
            editBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_account_id').value = btn.getAttribute('data-id');
                    document.getElementById('edit_account_name').value = btn.getAttribute('data-name');
                    document.getElementById('edit_account_type').value = btn.getAttribute('data-type');
                    document.getElementById('edit_account_code').value = btn.getAttribute('data-code');
                    document.getElementById('edit_description').value = btn.getAttribute('data-description');
                    editModal.style.display = 'flex';
                });
            });
            if (closeEditBtn && editModal) {
                closeEditBtn.onclick = function() {
                    editModal.style.display = 'none';
                };
            }
            // Delete button logic
            deleteBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('delete_account_id').value = btn.getAttribute('data-id');
                    document.getElementById('delete_account_name').textContent = btn.getAttribute('data-name');
                    deleteModal.style.display = 'flex';
                });
            });
            if (closeDeleteBtn && deleteModal) {
                closeDeleteBtn.onclick = function() {
                    deleteModal.style.display = 'none';
                };
            }
            if (cancelDeleteBtn && deleteModal) {
                cancelDeleteBtn.onclick = function() {
                    deleteModal.style.display = 'none';
                };
            }
        });
        </script>
</body>
</html> 