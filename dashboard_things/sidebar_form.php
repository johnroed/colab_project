<?php
require_once '../includes_files/auth_check.php';
requireLogin();
?>
<link rel="stylesheet" href="/colab_project/dashboard_things/sidebar_style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<div class="sidebar">
    <a class="sidebar-title" href="/colab_project/dashboard_things/dashboard_form.php">Dashboard</a>
    <div class="sidebar-menu">
        <button class="sidebar-category"><i class="fa-solid fa-building-columns"></i> Banking <span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Banking_files/bank_account_form.php"><i class="fa-solid fa-university"></i> Bank Accounts</a>
            <a class="sidebar-link" href="/colab_project/Banking_files/Transaction_form.php"><i class="fa-solid fa-money-check-dollar"></i> Transactions</a>
            <a class="sidebar-link" href="/colab_project/Banking_files/Reconciliation_form.php"><i class="fa-solid fa-scale-balanced"></i> Reconciliation</a>
        </div>
        <button class="sidebar-category"><i class="fa-solid fa-chart-line"></i> Sales <span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Sales_files/all_sales_form.php"><i class="fa-solid fa-list"></i> All Sales</a>
            <a class="sidebar-link" href="/colab_project/Sales_files/invoices_form.php"><i class="fa-solid fa-file-invoice-dollar"></i> Invoices</a>
            <a class="sidebar-link" href="/colab_project/Sales_files/estimates_form.php"><i class="fa-solid fa-file-signature"></i> Estimates</a>
            <a class="sidebar-link" href="/colab_project/Sales_files/sales_orders_form.php"><i class="fa-solid fa-file-contract"></i> Sales Orders</a>
            <a class="sidebar-link" href="/colab_project/Sales_files/customers_form.php"><i class="fa-solid fa-user"></i> Customers</a>
            <a class="sidebar-link" href="/colab_project/Sales_files/products_services_form.php"><i class="fa-solid fa-box"></i> Products/Services</a>
        </div>
        <button class="sidebar-category"><i class="fa-solid fa-credit-card"></i> Purchases & Expenses <span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Purchases_Expenses_files/expenses_form.php"><i class="fa-solid fa-money-bill-wave"></i> Expenses</a>
            <a class="sidebar-link" href="/colab_project/Purchases_Expenses_files/vendors_form.php"><i class="fa-solid fa-truck"></i> Vendors</a>
            <a class="sidebar-link" href="/colab_project/Purchases_Expenses_files/bills_form.php"><i class="fa-solid fa-file-invoice"></i> Bills</a>
            <a class="sidebar-link" href="/colab_project/Purchases_Expenses_files/payments_form.php"><i class="fa-solid fa-money-check"></i> Payments</a>
        </div>
        <button class="sidebar-category"><i class="fa-solid fa-book"></i> Accounting <span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Accounting_files/chart_of_accounts_form.php"><i class="fa-solid fa-diagram-project"></i> Chart of Accounts</a>
            <a class="sidebar-link" href="/colab_project/Accounting_files/journal_entries_form.php"><i class="fa-solid fa-pen-nib"></i> Journal Entries</a>
        </div>
        <button class="sidebar-category"><i class="fa-solid fa-file-alt"></i> Reports <span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Reports_files/profit_loss_form.php"><i class="fa-solid fa-chart-pie"></i> Profit & Loss</a>
            <a class="sidebar-link" href="/colab_project/Reports_files/balance_sheet_form.php"><i class="fa-solid fa-balance-scale"></i> Balance Sheet</a>
            <a class="sidebar-link" href="/colab_project/Reports_files/cash_flow_form.php"><i class="fa-solid fa-water"></i> Cash Flow</a>
            <a class="sidebar-link" href="/colab_project/Reports_files/ar_ap_aging_form.php"><i class="fa-solid fa-hourglass-half"></i> AR/AP Aging</a>
            <a class="sidebar-link" href="/colab_project/Reports_files/sales_expense_reports_form.php"><i class="fa-solid fa-chart-bar"></i> Sales/Expense Reports</a>
        </div>
        <button class="sidebar-category"><i class="fa-solid fa-boxes-stacked"></i> Inventory<span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Inventory_files/inventory_list_form.php"><i class="fa-solid fa-warehouse"></i> Inventory List</a>
            <a class="sidebar-link" href="/colab_project/Inventory_files/stock_in_out_form.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Stock In/Out</a>
        </div>
        <button class="sidebar-category"><i class="fa-solid fa-users"></i> Payroll<span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
        <div class="sidebar-dropdown">
            <a class="sidebar-link" href="/colab_project/Payroll_files/employees_form.php"><i class="fa-solid fa-user-tie"></i> Employees</a>
            <a class="sidebar-link" href="/colab_project/Payroll_files/pay_slips_form.php"><i class="fa-solid fa-file-invoice"></i> Pay Slips</a>
        </div>
        <?php if (isset($_SESSION['job_title']) && strtolower($_SESSION['job_title']) === 'workers'): ?>
            <button class="sidebar-category sidebar-disabled" disabled style="cursor:not-allowed; position:relative;">
                <i class="fa-solid fa-cogs"></i> Management & Settings
                <span class="sidebar-chevron"><i class="fa-solid fa-lock"></i></span>
                <span class="sidebar-tooltip">Your account is not authorized to access this part</span>
            </button>
        <?php else: ?>
            <button class="sidebar-category"><i class="fa-solid fa-cogs"></i> Management & Settings <span class="sidebar-chevron"><i class="fa-solid fa-chevron-left"></i></span></button>
            <div class="sidebar-dropdown">
                <a class="sidebar-link" href="/colab_project/dashboard_things/user_roles_form.php"><i class="fa-solid fa-users-cog"></i> Users & Roles</a>
                <a class="sidebar-link" href="/colab_project/Management_Settings_files/company_settings_form.php"><i class="fa-solid fa-building"></i> Company Settings</a>
                <a class="sidebar-link" href="/colab_project/Management_Settings_files/tax_management_form.php"><i class="fa-solid fa-percent"></i> Tax Management</a>
                <a class="sidebar-link" href="/colab_project/Management_Settings_files/import_export_form.php"><i class="fa-solid fa-file-import"></i> Import/Export</a>
                <a class="sidebar-link" href="/colab_project/Management_Settings_files/audit_log_form.php"><i class="fa-solid fa-clipboard-list"></i> Audit Log</a>
                <a class="sidebar-link" href="/colab_project/Management_Settings_files/attachments_form.php"><i class="fa-solid fa-paperclip"></i> Attachments</a>
                <a class="sidebar-link" href="/colab_project/Management_Settings_files/notifications_form.php"><i class="fa-solid fa-bell"></i> Notifications</a>
            </div>
        <?php endif; ?>
    </div>
    <a href="/colab_project/logout.php" class="sidebar-logout-btn">Logout</a>
</div>
<style>
.sidebar-disabled {
    background: rgba(255,255,255,0.04) !important;
    color: #bdbdbd !important;
    pointer-events: none;
    opacity: 0.7;
}
.sidebar-disabled .fa-lock {
    color: #bdbdbd;
}
.sidebar-disabled .sidebar-tooltip {
    display: none;
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background: #222;
    color: #fff;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    white-space: nowrap;
    z-index: 9999;
    margin-left: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.sidebar-disabled:hover .sidebar-tooltip {
    display: block;
}
</style>
<script>
    // Sidebar dropdown logic
    const categories = document.querySelectorAll('.sidebar-category');
    categories.forEach((cat, idx) => {
        cat.addEventListener('click', function() {
            const dropdown = cat.nextElementSibling;
            if (!dropdown) return;
            dropdown.classList.toggle('show');
            cat.classList.toggle('active');
            // Chevron toggle
            const chevron = cat.querySelector('.sidebar-chevron i');
            if (dropdown.classList.contains('show')) {
                chevron.classList.remove('fa-chevron-left');
                chevron.classList.add('fa-chevron-down');
            } else {
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-left');
            }
            // Optionally close others
            categories.forEach((otherCat, otherIdx) => {
                if (otherIdx !== idx) {
                    otherCat.classList.remove('active');
                    const otherDropdown = otherCat.nextElementSibling;
                    if (otherDropdown) otherDropdown.classList.remove('show');
                    // Reset chevron
                    const otherChevron = otherCat.querySelector('.sidebar-chevron i');
                    if (otherChevron) {
                        otherChevron.classList.remove('fa-chevron-down');
                        otherChevron.classList.add('fa-chevron-left');
                    }
                }
            });
        });
    });
</script> 