<?php include 'sidebar_form.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users & Roles</title>
    <link rel="stylesheet" href="users_roles_style.css">
    <!-- PDF.js and Mammoth.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.2.0/mammoth.browser.min.js"></script>
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-users-cog"></i> Users & Roles</h1>
        </div>
        <div class="action-buttons">
            <button class="add-button" id="openAddEmployeeModal">
                <i class="fa-solid fa-plus"></i>
                Add User
            </button>
        </div>
        <!-- Add User Modal -->
        <div id="addEmployeeModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add New Account</h2>

                    <!-- Remove the resume upload input from the Add User modal -->
                    <!-- <input type="file" id="resumeInput" accept=".pdf,.docx" style="display:none;" /> -->
                </div>
                <span class="close-modal" id="closeAddEmployeeModal">&times;</span>
                <form class="add-employee-form" method="POST" action="save_user.php">
                    <div class="form-row">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-row">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <div id="passwordError" class="password-error-message" style="display:none;"></div>
                    </div>
                    <div class="form-row">
                        <label for="job_title">Job Title</label>
                        <select id="job_title" name="job_title" required>
                            <option value="">Select Job Title</option>
                            <option value="executives">Executives</option>
                            <option value="senior_manager">Senior Manager</option>
                            <option value="middle_manager">Middle Manager</option>
                            <option value="workers">Workers</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Add User</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- User List Table -->
        <?php
        include '../includes_files/connection.php';
        $sql = "SELECT user_id, email_address, job_title, status FROM user_login WHERE job_title != 'executives' ORDER BY user_id DESC";
        $result = $conn->query($sql);
        ?>
        <div class="employee-table-container">
        <h2 class="employee-table-title">User List</h2>
        <table class="employee-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (
                $result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr data-user-id="<?= $row['user_id'] ?>" data-email="<?= htmlspecialchars($row['email_address']) ?>" data-job-title="<?= htmlspecialchars($row['job_title']) ?>" data-status="<?= htmlspecialchars($row['status']) ?>">
                        <td><?= htmlspecialchars($row['email_address']) ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['job_title']))) ?></td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="status-toggle" data-user-id="<?= $row['user_id'] ?>" <?= $row['status'] === 'active' ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </td>
                        <td style="white-space: nowrap; position:relative;">
                            <button class="actions-btn" type="button">Choose</button>
                            <div class="actions-dropdown" style="display:none; position:absolute; right:0; top:100%; z-index:10; min-width:120px; background:#fff; box-shadow:0 2px 8px rgba(25,118,210,0.12); border-radius:8px; overflow:hidden;">
                                <button class="edit-account-btn" type="button"><i class="fa-solid fa-pen"></i> Edit</button>
                                <button class="delete-account-btn" type="button"><i class="fa-solid fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <!-- Edit User Modal -->
        <div id="editUserModal" class="modal-overlay">
            <div class="modal-content" style="max-width:400px;min-width:320px;">
                <div class="modal-header-row">
                    <h2>Edit User</h2>
                </div>
                <span class="close-modal" id="closeEditUserModal">&times;</span>
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="form-row">
                        <label for="edit_email">Email Address</label>
                        <input type="email" id="edit_email" name="email" required readonly>
                    </div>
                    <div class="form-row">
                        <label for="edit_job_title">Job Title</label>
                        <select id="edit_job_title" name="job_title" required>
                            <option value="executives">Executives</option>
                            <option value="senior_manager">Senior Manager</option>
                            <option value="middle_manager">Middle Manager</option>
                            <option value="workers">Workers</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Delete User Modal -->
        <div id="deleteUserModal" class="modal-overlay">
            <div class="modal-content delete-modal">
                <div class="modal-header-row" style="justify-content:center;">
                    <span class="delete-icon" style="color:#d32f2f; font-size:2.2rem; margin-right:10px;"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <h2 style="color:#d32f2f; margin:0;">Delete User</h2>
                </div>
                <span class="close-modal" id="closeDeleteUserModal">&times;</span>
                <div class="delete-modal-body">
                    <p style="font-size:1.15rem; color:#333; margin: 18px 0 24px 0;">Are you sure you want to <span style="color:#d32f2f; font-weight:600;">delete</span> this user?</p>
                    <div class="delete-user-email-row" style="margin-bottom:18px;">
                        <span class="delete-user-label" style="color:#888; font-size:1rem;">User Email:</span><br>
                        <span id="delete_user_email" style="font-weight:bold; color:#1976d2; font-size:1.08rem;"></span>
                    </div>
                </div>
                <div class="form-row form-actions delete-actions-row">
                    <button id="confirmDeleteUserBtn" class="add-button" style="background:#d32f2f; color:#fff; min-width:110px;">Delete</button>
                    <button id="cancelDeleteUserBtn" class="add-button" style="background:#bdbdbd; color:#333; min-width:110px;">Cancel</button>
                </div>
            </div>
        </div>
        <!-- Loading Modal -->
        <div id="loadingModal" class="modal-overlay" style="display:none; z-index:2000; background:rgba(255,255,255,0.8);">
            <div class="modal-content" style="min-width:300px; max-width:400px; text-align:center; align-items:center; justify-content:center;">
                <div style="margin: 30px auto;">
                    <div class="spinner" style="margin-bottom:18px; width:40px; height:40px; border:5px solid #1976d2; border-top:5px solid #fff; border-radius:50%; animation:spin 1s linear infinite;"></div>
                    <div style="font-size:1.2rem; color:#1976d2;">Analyzing resume, please wait…</div>
                </div>
            </div>
        </div>
        <style>
        @keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
        .autofilled-success {
            border: 2px solid #43a047 !important;
            box-shadow: 0 0 0 2px rgba(67, 160, 71, 0.15);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .password-error {
            border: 2px solid #d32f2f !important;
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.10);
        }
        .password-error-message {
            color: #d32f2f;
            font-size: 0.95rem;
            margin-top: 2px;
            min-height: 18px;
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
        .switch {
          position: relative;
          display: inline-block;
          width: 48px;
          height: 24px;
        }
        .switch input {display:none;}
        .slider {
          position: absolute;
          cursor: pointer;
          top: 0; left: 0; right: 0; bottom: 0;
          background-color: #d32f2f;
          transition: .4s;
          border-radius: 24px;
        }
        .slider:before {
          position: absolute;
          content: "";
          height: 18px;
          width: 18px;
          left: 3px;
          bottom: 3px;
          background-color: white;
          transition: .4s;
          border-radius: 50%;
        }
        input:checked + .slider {
          background-color: #43a047;
        }
        input:checked + .slider:before {
          transform: translateX(24px);
        }
        .actions-btn {
          background: #1976d2;
          color: #fff;
          border: none;
          border-radius: 6px;
          padding: 8px 18px;
          font-size: 1rem;
          font-weight: 600;
          cursor: pointer;
          transition: background 0.18s, color 0.18s;
          box-shadow: 0 1.5px 4px rgba(25,118,210,0.08);
          letter-spacing: 0.5px;
        }
        .actions-btn:hover {
          background: #1565c0;
          color: #fff;
        }
        .actions-dropdown {
          min-width: 120px;
          background: #fff;
          box-shadow: 0 2px 8px rgba(25,118,210,0.12);
          border-radius: 8px;
          overflow: hidden;
          position: absolute;
          right: 0;
          top: 100%;
          z-index: 10;
          display: none;
        }
        .actions-dropdown button {
          width: 100%;
          border-radius: 0;
          border: none;
          background: none;
          color: #1976d2;
          padding: 10px 18px;
          text-align: left;
          font-size: 1rem;
          cursor: pointer;
          transition: background 0.18s, color 0.18s;
        }
        .actions-dropdown button:hover {
          background: #f0f6ff;
        }
        .actions-dropdown .delete-account-btn {
          color: #d32f2f;
        }
        .actions-dropdown .delete-account-btn:hover {
          background: #fff0f0;
        }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal open/close logic
            const openBtn = document.getElementById('openAddEmployeeModal');
            const closeBtn = document.getElementById('closeAddEmployeeModal');
            const modal = document.getElementById('addEmployeeModal');
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
            // (Resume upload logic removed)
            // Password real-time validation
            const passwordInput = document.getElementById('password');
            const passwordError = document.getElementById('passwordError');
            if (passwordInput && passwordError) {
                function validatePassword(pw) {
                    const errors = [];
                    if (pw.length < 12) errors.push('At least 12 characters');
                    if (!/[A-Z]/.test(pw)) errors.push('At least one uppercase letter');
                    if (!/[a-z]/.test(pw)) errors.push('At least one lowercase letter');
                    if (!/[0-9]/.test(pw)) errors.push('At least one number');
                    if (!/[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/.test(pw)) errors.push('At least one special character');
                    return errors;
                }
                function showPasswordError(errors) {
                    if (errors.length > 0) {
                        passwordInput.classList.add('password-error');
                        passwordInput.classList.remove('autofilled-success');
                        passwordError.style.display = 'block';
                        passwordError.textContent = 'Password must have: ' + errors.join(', ');
                    } else {
                        passwordInput.classList.remove('password-error');
                        passwordInput.classList.add('autofilled-success');
                        passwordError.style.display = 'none';
                        passwordError.textContent = '';
                    }
                }
                passwordInput.addEventListener('input', function() {
                    const errors = validatePassword(passwordInput.value);
                    showPasswordError(errors);
                });
                // Initial validation in case of autofill
                showPasswordError(validatePassword(passwordInput.value));
            }

            document.querySelectorAll('.status-toggle').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    var userId = this.getAttribute('data-user-id');
                    var newStatus = this.checked ? 'active' : 'inactive';
                    fetch('update_user_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'user_id=' + encodeURIComponent(userId) + '&status=' + encodeURIComponent(newStatus)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Failed to update status: ' + (data.error || 'Unknown error'));
                            this.checked = !this.checked; // revert
                        }
                    })
                    .catch(() => {
                        alert('Failed to update status due to network error.');
                        this.checked = !this.checked; // revert
                    });
                });
            });

            // Actions dropdown logic
            document.querySelectorAll('.actions-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Close all other dropdowns
                    document.querySelectorAll('.actions-dropdown').forEach(function(drop) { drop.style.display = 'none'; });
                    // Open this one
                    var dropdown = btn.nextElementSibling;
                    if (dropdown) {
                        dropdown.style.display = 'block';
                    }
                });
            });
            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.actions-dropdown').forEach(function(drop) { drop.style.display = 'none'; });
            });
            // Prevent dropdown from closing when clicking inside
            document.querySelectorAll('.actions-dropdown').forEach(function(drop) {
                drop.addEventListener('click', function(e) { e.stopPropagation(); });
            });

            // Edit button logic
            document.querySelectorAll('.edit-account-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var row = btn.closest('tr');
                    var userId = row.getAttribute('data-user-id');
                    var email = row.getAttribute('data-email');
                    var jobTitle = row.getAttribute('data-job-title');
                    var status = row.getAttribute('data-status');
                    document.getElementById('edit_user_id').value = userId;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_job_title').value = jobTitle;
                    document.getElementById('edit_status').value = status;
                    document.getElementById('editUserModal').style.display = 'flex';
                });
            });
            document.getElementById('closeEditUserModal').onclick = function() {
                document.getElementById('editUserModal').style.display = 'none';
            };
            // Edit form submit
            document.getElementById('editUserForm').onsubmit = function(e) {
                e.preventDefault();
                var form = e.target;
                var formData = new FormData(form);
                fetch('update_user_status.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update user: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => {
                    alert('Failed to update user due to network error.');
                });
            };
            // Delete button logic
            document.querySelectorAll('.delete-account-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var row = btn.closest('tr');
                    var userId = row.getAttribute('data-user-id');
                    var email = row.getAttribute('data-email');
                    document.getElementById('delete_user_email').textContent = email;
                    document.getElementById('deleteUserModal').setAttribute('data-user-id', userId);
                    document.getElementById('deleteUserModal').style.display = 'flex';
                });
            });
            document.getElementById('closeDeleteUserModal').onclick = function() {
                document.getElementById('deleteUserModal').style.display = 'none';
            };
            document.getElementById('cancelDeleteUserBtn').onclick = function() {
                document.getElementById('deleteUserModal').style.display = 'none';
            };
            document.getElementById('confirmDeleteUserBtn').onclick = function(e) {
                e.preventDefault();
                var modal = document.getElementById('deleteUserModal');
                var userId = modal.getAttribute('data-user-id');
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'user_id=' + encodeURIComponent(userId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete user: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => {
                    alert('Failed to delete user due to network error.');
                });
            };
        });
        </script>
        <!-- User Roles content goes here -->
    </div>
</body>
</html> 