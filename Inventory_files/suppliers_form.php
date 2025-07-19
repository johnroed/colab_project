<?php
session_start();
include '../dashboard_things/sidebar_form.php';
include '../includes_files/connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add' && isset($_POST['name']) && !empty(trim($_POST['name']))) {
            $name = trim($_POST['name']);
            $contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : null;
            $email = isset($_POST['email']) ? trim($_POST['email']) : null;
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
            $address = isset($_POST['address']) ? trim($_POST['address']) : null;
            $tax_id = isset($_POST['tax_id']) ? trim($_POST['tax_id']) : null;
            $payment_terms = isset($_POST['payment_terms']) ? trim($_POST['payment_terms']) : null;
            
            $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address, tax_id, payment_terms) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $contact_person, $email, $phone, $address, $tax_id, $payment_terms);
            $stmt->execute();
            $stmt->close();
            
            header('Location: suppliers_form.php?success=1');
            exit;
        }
        
        if ($action === 'edit' && isset($_POST['id']) && isset($_POST['name']) && !empty(trim($_POST['name']))) {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : null;
            $email = isset($_POST['email']) ? trim($_POST['email']) : null;
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
            $address = isset($_POST['address']) ? trim($_POST['address']) : null;
            $tax_id = isset($_POST['tax_id']) ? trim($_POST['tax_id']) : null;
            $payment_terms = isset($_POST['payment_terms']) ? trim($_POST['payment_terms']) : null;
            
            $stmt = $conn->prepare("UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, address=?, tax_id=?, payment_terms=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $contact_person, $email, $phone, $address, $tax_id, $payment_terms, $id);
            $stmt->execute();
            $stmt->close();
            
            header('Location: suppliers_form.php?success=2');
            exit;
        }
        
        if ($action === 'delete' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            header('Location: suppliers_form.php?success=3');
            exit;
        }
    }
}

// Fetch suppliers
$suppliers = [];
$sql = "SELECT * FROM suppliers ORDER BY name ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers Management</title>
    <link rel="stylesheet" href="suppliers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-truck"></i> Suppliers Management</h1>
        </div>
        
        <div class="action-buttons">
            <button class="add-button" id="openAddSupplierModal">
                <i class="fa-solid fa-plus"></i> Add Supplier
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php 
                switch($_GET['success']) {
                    case 1: echo "Supplier added successfully!"; break;
                    case 2: echo "Supplier updated successfully!"; break;
                    case 3: echo "Supplier deleted successfully!"; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Suppliers Table -->
        <div class="suppliers-table-container">
            <h2 class="suppliers-table-title">Suppliers List</h2>
            <table class="suppliers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Payment Terms</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($suppliers) > 0): ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['name']) ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($supplier['email'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($supplier['phone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($supplier['payment_terms'] ?? '-') ?></td>
                            <td><span class="status-badge status-<?= $supplier['status'] ?>"><?= ucfirst($supplier['status']) ?></span></td>
                            <td>
                                <button class="edit-supplier-btn" data-id="<?= $supplier['id'] ?>" data-name="<?= htmlspecialchars($supplier['name']) ?>" data-contact="<?= htmlspecialchars($supplier['contact_person'] ?? '') ?>" data-email="<?= htmlspecialchars($supplier['email'] ?? '') ?>" data-phone="<?= htmlspecialchars($supplier['phone'] ?? '') ?>" data-address="<?= htmlspecialchars($supplier['address'] ?? '') ?>" data-tax="<?= htmlspecialchars($supplier['tax_id'] ?? '') ?>" data-terms="<?= htmlspecialchars($supplier['payment_terms'] ?? '') ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                                <button class="delete-supplier-btn" data-id="<?= $supplier['id'] ?>" data-name="<?= htmlspecialchars($supplier['name']) ?>">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No suppliers found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header-row">
                <h2>Add New Supplier</h2>
            </div>
            <span class="close-modal" id="closeAddSupplierModal">&times;</span>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <label for="name">Supplier Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-row">
                    <label for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person">
                </div>
                <div class="form-row">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-row">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone">
                </div>
                <div class="form-row">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"></textarea>
                </div>
                <div class="form-row">
                    <label for="tax_id">Tax ID</label>
                    <input type="text" id="tax_id" name="tax_id">
                </div>
                <div class="form-row">
                    <label for="payment_terms">Payment Terms</label>
                    <input type="text" id="payment_terms" name="payment_terms" placeholder="e.g., Net 30">
                </div>
                <div class="form-row form-actions">
                    <button type="submit" class="add-button">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="editSupplierModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header-row">
                <h2>Edit Supplier</h2>
            </div>
            <span class="close-modal" id="closeEditSupplierModal">&times;</span>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-row">
                    <label for="edit_name">Supplier Name *</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-row">
                    <label for="edit_contact_person">Contact Person</label>
                    <input type="text" id="edit_contact_person" name="contact_person">
                </div>
                <div class="form-row">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email">
                </div>
                <div class="form-row">
                    <label for="edit_phone">Phone</label>
                    <input type="text" id="edit_phone" name="phone">
                </div>
                <div class="form-row">
                    <label for="edit_address">Address</label>
                    <textarea id="edit_address" name="address"></textarea>
                </div>
                <div class="form-row">
                    <label for="edit_tax_id">Tax ID</label>
                    <input type="text" id="edit_tax_id" name="tax_id">
                </div>
                <div class="form-row">
                    <label for="edit_payment_terms">Payment Terms</label>
                    <input type="text" id="edit_payment_terms" name="payment_terms">
                </div>
                <div class="form-row form-actions">
                    <button type="submit" class="add-button">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Supplier Modal -->
    <div id="deleteSupplierModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header-row">
                <h2>Delete Supplier</h2>
            </div>
            <span class="close-modal" id="closeDeleteSupplierModal">&times;</span>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_id" name="id">
                <div class="form-row">
                    <p>Are you sure you want to delete the supplier <span id="delete_supplier_name" style="font-weight:bold;"></span>?</p>
                </div>
                <div class="form-row form-actions">
                    <button type="submit" class="add-button" style="background:#d32f2f;">Delete</button>
                    <button type="button" class="add-button" id="cancelDeleteSupplier" style="background:#bdbdbd;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal controls
            const openBtn = document.getElementById('openAddSupplierModal');
            const closeBtn = document.getElementById('closeAddSupplierModal');
            const modal = document.getElementById('addSupplierModal');
            
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
            
            // Edit modal
            const editBtns = document.querySelectorAll('.edit-supplier-btn');
            const editModal = document.getElementById('editSupplierModal');
            const closeEditBtn = document.getElementById('closeEditSupplierModal');
            
            editBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_id').value = btn.getAttribute('data-id');
                    document.getElementById('edit_name').value = btn.getAttribute('data-name');
                    document.getElementById('edit_contact_person').value = btn.getAttribute('data-contact');
                    document.getElementById('edit_email').value = btn.getAttribute('data-email');
                    document.getElementById('edit_phone').value = btn.getAttribute('data-phone');
                    document.getElementById('edit_address').value = btn.getAttribute('data-address');
                    document.getElementById('edit_tax_id').value = btn.getAttribute('data-tax');
                    document.getElementById('edit_payment_terms').value = btn.getAttribute('data-terms');
                    editModal.style.display = 'flex';
                });
            });
            
            if (closeEditBtn && editModal) {
                closeEditBtn.onclick = function() {
                    editModal.style.display = 'none';
                };
            }
            
            // Delete modal
            const deleteBtns = document.querySelectorAll('.delete-supplier-btn');
            const deleteModal = document.getElementById('deleteSupplierModal');
            const closeDeleteBtn = document.getElementById('closeDeleteSupplierModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteSupplier');
            
            deleteBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('delete_id').value = btn.getAttribute('data-id');
                    document.getElementById('delete_supplier_name').textContent = btn.getAttribute('data-name');
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