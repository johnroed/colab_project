<?php
session_start();
include '../dashboard_things/sidebar_form.php';
include '../includes_files/connection.php';

// Fetch products for dropdown
$products = [];
$sql = 'SELECT id, name FROM inventory_items ORDER BY name ASC';
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch suppliers for dropdown
$suppliers = [];
$sql = 'SELECT id, name FROM suppliers WHERE status = "active" ORDER BY name ASC';
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Fetch customers for dropdown
$customers = [];
$sql = 'SELECT id, name FROM customers WHERE status = "active" ORDER BY name ASC';
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Handle form submission
$successMsg = '';
$errorMsg = '';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        if ($user_id <= 0) {
            $errorMsg = 'You must be logged in to record stock movements.';
        } else {
            $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
            $movement_type = isset($_POST['movement_type']) ? $_POST['movement_type'] : '';
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
            $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            
            // Enhanced fields
            $supplier_id = isset($_POST['supplier_id']) && !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
            $customer_id = isset($_POST['customer_id']) && !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
            $reference_number = isset($_POST['reference_number']) ? trim($_POST['reference_number']) : null;
            $cost_per_unit = isset($_POST['cost_per_unit']) && !empty($_POST['cost_per_unit']) ? floatval($_POST['cost_per_unit']) : null;
            $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : 'pending';
            $movement_category = isset($_POST['movement_category']) ? $_POST['movement_category'] : 'adjustment';
            $batch_number = isset($_POST['batch_number']) ? trim($_POST['batch_number']) : null;
            $expiry_date = isset($_POST['expiry_date']) && !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
            $quality_status = isset($_POST['quality_status']) ? $_POST['quality_status'] : 'good';
            
            // Enhanced validation
            $validationErrors = [];
            
            if ($item_id <= 0) {
                $validationErrors[] = 'Please select a valid product.';
            }
            
            if (!in_array($movement_type, ['IN','OUT'])) {
                $validationErrors[] = 'Please select a valid movement type.';
            }
            
            if ($quantity <= 0 || $quantity > 999999) {
                $validationErrors[] = 'Quantity must be between 1 and 999,999.';
            }
            
            if (empty($reason)) {
                $validationErrors[] = 'Reason is required.';
            } else {
                $reason = filter_var($reason, FILTER_SANITIZE_STRING);
                if (strlen($reason) > 100) {
                    $validationErrors[] = 'Reason must be 100 characters or less.';
                }
            }
            
            if (!empty($notes)) {
                $notes = filter_var($notes, FILTER_SANITIZE_STRING);
                if (strlen($notes) > 500) {
                    $validationErrors[] = 'Notes must be 500 characters or less.';
                }
            }
            
            // Validate supplier/customer based on movement type
            if ($movement_type === 'IN' && $supplier_id === null && $movement_category === 'purchase') {
                $validationErrors[] = 'Supplier is required for purchase stock in.';
            }
            
            if ($movement_type === 'OUT' && $customer_id === null && $movement_category === 'sale') {
                $validationErrors[] = 'Customer is required for sale stock out.';
            }
            
            // Validate cost per unit
            if ($cost_per_unit !== null && ($cost_per_unit < 0 || $cost_per_unit > 999999.99)) {
                $validationErrors[] = 'Cost per unit must be between 0 and 999,999.99.';
            }
            
            if (!empty($validationErrors)) {
                $errorMsg = implode(' ', $validationErrors);
            } else {
                // Calculate total cost
                $total_cost = ($cost_per_unit !== null && $quantity > 0) ? $cost_per_unit * $quantity : null;
                
                // Get current quantity
                $stmt = $conn->prepare('SELECT quantity FROM inventory_items WHERE id = ?');
                $stmt->bind_param('i', $item_id);
                $stmt->execute();
                $stmt->bind_result($currentQty);
                if ($stmt->fetch()) {
                    $stmt->close();
                    $newQty = $movement_type === 'IN' ? $currentQty + $quantity : $currentQty - $quantity;
                    if ($newQty < 0) {
                        $errorMsg = 'Stock out quantity exceeds current inventory.';
                    } else {
                        // Insert into stock_movements with enhanced fields
                        $stmt2 = $conn->prepare('INSERT INTO stock_movements (item_id, movement_type, quantity, reason, notes, user_id, supplier_id, customer_id, reference_number, cost_per_unit, total_cost, payment_status, movement_category, batch_number, expiry_date, quality_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $stmt2->bind_param('isissiissddsssss', $item_id, $movement_type, $quantity, $reason, $notes, $user_id, $supplier_id, $customer_id, $reference_number, $cost_per_unit, $total_cost, $payment_status, $movement_category, $batch_number, $expiry_date, $quality_status);
                        if ($stmt2->execute()) {
                            $stmt2->close();
                            // Update inventory_items
                            $stmt3 = $conn->prepare('UPDATE inventory_items SET quantity = ? WHERE id = ?');
                            $stmt3->bind_param('ii', $newQty, $item_id);
                            if ($stmt3->execute()) {
                                $successMsg = 'Stock movement recorded successfully.';
                            } else {
                                $errorMsg = 'Failed to update inventory quantity.';
                            }
                            $stmt3->close();
                        } else {
                            $errorMsg = 'Failed to record stock movement.';
                        }
                    }
                } else {
                    $errorMsg = 'Selected product not found.';
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In/Out</title>
    <link rel="stylesheet" href="stock_in_out.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-arrow-right-arrow-left"></i> Stock In/Out</h1>
        </div>
        
        <div class="action-buttons">
            <button class="add-button" id="openStockModal">
                <i class="fa-solid fa-plus-minus"></i>
                Stock In/Out
            </button>
        </div>

        <!-- Stock Movement History Table -->
        <div class="stock-table-container">
            <h2 class="stock-table-title">Recent Stock Movements</h2>
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Category</th>
                        <th>Reference</th>
                        <th>Cost</th>
                        <th>Payment</th>
                        <th>Reason</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $history_sql = "SELECT sm.*, ii.name AS product_name, ul.email_address AS user_email, 
                    s.name AS supplier_name, c.name AS customer_name 
                    FROM stock_movements sm
                    LEFT JOIN inventory_items ii ON sm.item_id = ii.id
                    LEFT JOIN user_login ul ON sm.user_id = ul.user_id
                    LEFT JOIN suppliers s ON sm.supplier_id = s.id
                    LEFT JOIN customers c ON sm.customer_id = c.id
                    ORDER BY sm.movement_date DESC, sm.id DESC LIMIT 20";
                $history_result = $conn->query($history_sql);
                if ($history_result && $history_result->num_rows > 0):
                    while ($row = $history_result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['movement_date']))) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td style="color:<?= $row['movement_type']==='IN'?'#388e3c':'#e53935' ?>; font-weight:600;"><?= htmlspecialchars($row['movement_type']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><span class="category-badge category-<?= $row['movement_category'] ?>"><?= ucfirst(htmlspecialchars($row['movement_category'])) ?></span></td>
                        <td><?= htmlspecialchars($row['reference_number'] ?? '-') ?></td>
                        <td><?= $row['total_cost'] ? '₱' . number_format($row['total_cost'], 2) : '-' ?></td>
                        <td><span class="payment-badge payment-<?= $row['payment_status'] ?>"><?= ucfirst(htmlspecialchars($row['payment_status'])) ?></span></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td><?= htmlspecialchars($row['user_email'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="10">No stock movements found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enhanced Stock In/Out Modal -->
    <div id="stockModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; padding:32px 28px 24px 28px; max-width:700px; width:95%; max-height:90vh; overflow-y:auto; box-shadow:0 8px 32px rgba(0,0,0,0.2); position:relative;">
            <!-- Modal Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h3 style="margin:0; font-size:1.5rem; color:#1976d2; font-weight:600;">Stock In/Out</h3>
                <button id="closeStockModal" style="background:none; border:none; color:#1976d2; font-size:1.8rem; cursor:pointer; padding:0; width:30px; height:30px; display:flex; align-items:center; justify-content:center; transition:color 0.2s;">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div>
                <?php if ($successMsg): ?>
                    <div style="color: #388e3c; margin-bottom: 18px; font-weight: 600; padding: 12px; background: #e8f5e8; border-radius: 6px; border-left: 4px solid #388e3c;"> <?= htmlspecialchars($successMsg) ?> </div>
                <?php elseif ($errorMsg): ?>
                    <div style="color: #e53935; margin-bottom: 18px; font-weight: 600; padding: 12px; background: #ffebee; border-radius: 6px; border-left: 4px solid #e53935;"> <?= htmlspecialchars($errorMsg) ?> </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="stockForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <!-- Left Column -->
                        <div>
                            <label for="item_id" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Product *</label>
                            <select id="item_id" name="item_id" required style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?= $prod['id'] ?>" <?= (isset($_POST['item_id']) && $_POST['item_id'] == $prod['id']) || (isset($_GET['item_id']) && $_GET['item_id'] == $prod['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prod['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <label for="movement_type" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Movement Type *</label>
                            <select id="movement_type" name="movement_type" required style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                <option value="">-- Select Type --</option>
                                <option value="IN" <?= (isset($_POST['movement_type']) && $_POST['movement_type'] == 'IN') ? 'selected' : '' ?>>Stock In</option>
                                <option value="OUT" <?= (isset($_POST['movement_type']) && $_POST['movement_type'] == 'OUT') ? 'selected' : '' ?>>Stock Out</option>
                            </select>
                            
                            <label for="movement_category" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Category *</label>
                            <select id="movement_category" name="movement_category" required style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                <option value="adjustment" <?= (isset($_POST['movement_category']) && $_POST['movement_category'] == 'adjustment') ? 'selected' : '' ?>>Adjustment</option>
                                <option value="purchase" <?= (isset($_POST['movement_category']) && $_POST['movement_category'] == 'purchase') ? 'selected' : '' ?>>Purchase</option>
                                <option value="sale" <?= (isset($_POST['movement_category']) && $_POST['movement_category'] == 'sale') ? 'selected' : '' ?>>Sale</option>
                                <option value="return" <?= (isset($_POST['movement_category']) && $_POST['movement_category'] == 'return') ? 'selected' : '' ?>>Return</option>
                                <option value="loss" <?= (isset($_POST['movement_category']) && $_POST['movement_category'] == 'loss') ? 'selected' : '' ?>>Loss</option>
                                <option value="transfer" <?= (isset($_POST['movement_category']) && $_POST['movement_category'] == 'transfer') ? 'selected' : '' ?>>Transfer</option>
                            </select>
                            
                            <label for="quantity" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Quantity *</label>
                            <input type="number" id="quantity" name="quantity" min="1" required value="<?= isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '' ?>" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                            
                            <label for="cost_per_unit" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Cost per Unit (₱)</label>
                            <input type="number" id="cost_per_unit" name="cost_per_unit" min="0" step="0.01" value="<?= isset($_POST['cost_per_unit']) ? htmlspecialchars($_POST['cost_per_unit']) : '' ?>" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                            
                            <label for="payment_status" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Payment Status</label>
                            <select id="payment_status" name="payment_status" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                <option value="pending" <?= (isset($_POST['payment_status']) && $_POST['payment_status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= (isset($_POST['payment_status']) && $_POST['payment_status'] == 'paid') ? 'selected' : '' ?>>Paid</option>
                                <option value="partial" <?= (isset($_POST['payment_status']) && $_POST['payment_status'] == 'partial') ? 'selected' : '' ?>>Partial</option>
                            </select>
                        </div>
                        
                        <!-- Right Column -->
                        <div>
                            <div id="supplierSection" style="display:none;">
                                <label for="supplier_id" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Supplier</label>
                                <select id="supplier_id" name="supplier_id" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $supp): ?>
                                        <option value="<?= $supp['id'] ?>" <?= (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($supp['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="customerSection" style="display:none;">
                                <label for="customer_id" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Customer</label>
                                <select id="customer_id" name="customer_id" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                    <option value="">-- Select Customer --</option>
                                    <?php foreach ($customers as $cust): ?>
                                        <option value="<?= $cust['id'] ?>" <?= (isset($_POST['customer_id']) && $_POST['customer_id'] == $cust['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cust['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <label for="reference_number" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Reference Number</label>
                            <input type="text" id="reference_number" name="reference_number" maxlength="50" value="<?= isset($_POST['reference_number']) ? htmlspecialchars($_POST['reference_number']) : '' ?>" placeholder="PO#, Invoice#, etc." style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                            
                            <label for="batch_number" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Batch/Lot Number</label>
                            <input type="text" id="batch_number" name="batch_number" maxlength="50" value="<?= isset($_POST['batch_number']) ? htmlspecialchars($_POST['batch_number']) : '' ?>" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                            
                            <label for="expiry_date" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date" value="<?= isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '' ?>" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                            
                            <label for="quality_status" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Quality Status</label>
                            <select id="quality_status" name="quality_status" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                                <option value="good" <?= (isset($_POST['quality_status']) && $_POST['quality_status'] == 'good') ? 'selected' : '' ?>>Good</option>
                                <option value="damaged" <?= (isset($_POST['quality_status']) && $_POST['quality_status'] == 'damaged') ? 'selected' : '' ?>>Damaged</option>
                                <option value="expired" <?= (isset($_POST['quality_status']) && $_POST['quality_status'] == 'expired') ? 'selected' : '' ?>>Expired</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Full Width Fields -->
                    <label for="reason" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Reason *</label>
                    <input type="text" id="reason" name="reason" required maxlength="100" value="<?= isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?>" style="width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc; border-radius:6px; font-size:1rem; outline:none; transition:border-color 0.2s;">
                    
                    <label for="notes" style="font-weight:600; display:block; margin-bottom:6px; color:#1976d2;">Notes (optional)</label>
                    <textarea id="notes" name="notes" maxlength="500" style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ccc; border-radius:6px; font-size:1rem; resize:vertical; min-height:80px; outline:none; transition:border-color 0.2s;"><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                    
                    <div style="display:flex; justify-content:flex-end; gap:12px;">
                        <button type="button" id="submitBtn" style="background:#1976d2; color:#fff; border:none; border-radius:6px; padding:10px 20px; font-size:1rem; font-weight:600; cursor:pointer; transition:background 0.2s;">Submit</button>
                        <button type="button" id="cancelBtn" style="background:#eee; color:#222; border:none; border-radius:6px; padding:10px 20px; font-size:1rem; font-weight:600; cursor:pointer; transition:background 0.2s;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; padding:24px; max-width:500px; width:90%; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
            <h3 style="margin:0 0 16px 0; color:#1976d2; font-size:1.2rem;">Confirm Stock Movement</h3>
            <div id="confirmDetails" style="margin-bottom:20px; line-height:1.5;">
                <!-- Details will be populated by JavaScript -->
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" id="confirmYes" style="background:#1976d2; color:#fff; border:none; border-radius:5px; padding:8px 16px; font-size:0.95rem; font-weight:600; cursor:pointer;">Confirm</button>
                <button type="button" id="confirmNo" style="background:#eee; color:#222; border:none; border-radius:5px; padding:8px 16px; font-size:0.95rem; font-weight:600; cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stockModal = document.getElementById('stockModal');
            const openStockModal = document.getElementById('openStockModal');
            const closeStockModal = document.getElementById('closeStockModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const form = document.getElementById('stockForm');
            const submitBtn = document.getElementById('submitBtn');
            const confirmModal = document.getElementById('confirmModal');
            const confirmDetails = document.getElementById('confirmDetails');
            const confirmYes = document.getElementById('confirmYes');
            const confirmNo = document.getElementById('confirmNo');
            
            // Dynamic form fields
            const movementCategory = document.getElementById('movement_category');
            const supplierSection = document.getElementById('supplierSection');
            const customerSection = document.getElementById('customerSection');
            const supplierSelect = document.getElementById('supplier_id');
            const customerSelect = document.getElementById('customer_id');
            
            // Show/hide supplier/customer sections based on category
            function updateDynamicFields() {
                const category = movementCategory.value;
                
                // Hide both sections first
                supplierSection.style.display = 'none';
                customerSection.style.display = 'none';
                
                // Show relevant section based on category
                if (category === 'purchase') {
                    supplierSection.style.display = 'block';
                    supplierSelect.required = true;
                    customerSelect.required = false;
                } else if (category === 'sale') {
                    customerSection.style.display = 'block';
                    customerSelect.required = true;
                    supplierSelect.required = false;
                } else {
                    supplierSelect.required = false;
                    customerSelect.required = false;
                }
            }
            
            movementCategory.addEventListener('change', updateDynamicFields);
            
            // Open stock modal
            openStockModal.addEventListener('click', function() {
                stockModal.style.display = 'flex';
                updateDynamicFields();
            });
            
            // Close stock modal
            function closeModal() {
                stockModal.style.display = 'none';
                form.reset();
                supplierSection.style.display = 'none';
                customerSection.style.display = 'none';
            }
            
            closeStockModal.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            
            // Close modal when clicking outside
            stockModal.addEventListener('click', function(e) {
                if (e.target === stockModal) {
                    closeModal();
                }
            });
            
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Validate form
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                // Get form data
                const formData = new FormData(form);
                const productSelect = document.getElementById('item_id');
                const productName = productSelect.options[productSelect.selectedIndex].text;
                const movementType = formData.get('movement_type');
                const quantity = formData.get('quantity');
                const category = formData.get('movement_category');
                const reason = formData.get('reason');
                const notes = formData.get('notes');
                const costPerUnit = formData.get('cost_per_unit');
                const paymentStatus = formData.get('payment_status');
                const referenceNumber = formData.get('reference_number');
                const batchNumber = formData.get('batch_number');
                const expiryDate = formData.get('expiry_date');
                const qualityStatus = formData.get('quality_status');
                
                // Calculate total cost
                const totalCost = costPerUnit && quantity ? (parseFloat(costPerUnit) * parseInt(quantity)).toFixed(2) : null;
                
                // Show confirmation modal
                let confirmHtml = `
                    <strong>Product:</strong> ${productName}<br>
                    <strong>Type:</strong> ${movementType}<br>
                    <strong>Quantity:</strong> ${quantity}<br>
                    <strong>Category:</strong> ${category.charAt(0).toUpperCase() + category.slice(1)}<br>
                    <strong>Reason:</strong> ${reason}<br>
                `;
                
                if (costPerUnit) {
                    confirmHtml += `<strong>Cost per Unit:</strong> ₱${parseFloat(costPerUnit).toLocaleString('en-US', {minimumFractionDigits: 2})}<br>`;
                }
                
                if (totalCost) {
                    confirmHtml += `<strong>Total Cost:</strong> ₱${parseFloat(totalCost).toLocaleString('en-US', {minimumFractionDigits: 2})}<br>`;
                }
                
                if (referenceNumber) {
                    confirmHtml += `<strong>Reference:</strong> ${referenceNumber}<br>`;
                }
                
                if (batchNumber) {
                    confirmHtml += `<strong>Batch Number:</strong> ${batchNumber}<br>`;
                }
                
                if (expiryDate) {
                    confirmHtml += `<strong>Expiry Date:</strong> ${expiryDate}<br>`;
                }
                
                if (notes) {
                    confirmHtml += `<strong>Notes:</strong> ${notes}<br>`;
                }
                
                confirmHtml += `<br><span style="color:#e53935; font-weight:600;">Are you sure you want to proceed with this stock movement?</span>`;
                
                confirmDetails.innerHTML = confirmHtml;
                confirmModal.style.display = 'flex';
            });
            
            confirmYes.addEventListener('click', function() {
                // Add confirmation field and submit
                const confirmInput = document.createElement('input');
                confirmInput.type = 'hidden';
                confirmInput.name = 'confirm';
                confirmInput.value = 'yes';
                form.appendChild(confirmInput);
                form.submit();
            });
            
            confirmNo.addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });
            
            // Close confirmation modal when clicking outside
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) {
                    confirmModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 