<?php 
require_once '../includes_files/auth_check.php';
requireLogin();
include '../dashboard_things/sidebar_form.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Slips</title>
    <link rel="stylesheet" href="pay_slips.css">
    <link rel="stylesheet" href="../dashboard_things/users_roles_style.css">
    <script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-file-invoice-dollar"></i> Pay Slips</h1>
        </div>
        <div class="action-buttons">
            <button class="add-button" id="openAddPaySlipModal">
                <i class="fa-solid fa-plus"></i> Add Pay Slip
            </button>
        </div>
        <!-- Add Pay Slip Modal -->
        <div id="addPaySlipModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add New Pay Slip</h2>
                </div>
                <span class="close-modal" id="closeAddPaySlipModal">&times;</span>
                <form class="add-pay-slip-form" method="POST" action="#">
                    <div class="form-row">
                        <label for="employee">Employee</label>
                        <select id="employee" name="employee" required>
                            <option value="">Select Employee</option>
                            <!-- Populate with PHP in future -->
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="period_start">Period Start</label>
                        <input type="date" id="period_start" name="period_start" required>
                    </div>
                    <div class="form-row">
                        <label for="period_end">Period End</label>
                        <input type="date" id="period_end" name="period_end" required>
                    </div>
                    <div class="form-row">
                        <label for="gross_pay">Gross Pay</label>
                        <input type="number" id="gross_pay" name="gross_pay" step="0.01" min="0" required>
                    </div>
                    <div class="form-row">
                        <label for="deductions">Deductions</label>
                        <input type="number" id="deductions" name="deductions" step="0.01" min="0" required>
                    </div>
                    <div class="form-row">
                        <label for="net_pay">Net Pay</label>
                        <input type="number" id="net_pay" name="net_pay" step="0.01" min="0" required>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Add Pay Slip</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Pay Slips Table -->
        <div class="employee-table-container">
            <h2 class="employee-table-title">Pay Slips List</h2>
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="7">No pay slips found.</td></tr>
                </tbody>
            </table>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal open/close logic
            const openBtn = document.getElementById('openAddPaySlipModal');
            const closeBtn = document.getElementById('closeAddPaySlipModal');
            const modal = document.getElementById('addPaySlipModal');
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
            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        });
        </script>
    </div>
</body>
</html> 