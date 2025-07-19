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
                <i class="fa-solid fa-user-plus"></i>
                Add Employee
            </button>
        </div>
        <!-- Add Employee Modal -->
        <div id="addEmployeeModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add New Employee</h2>
                    <button type="button" class="add-button" id="uploadResumeBtn">
                        <i class="fa-solid fa-upload"></i> Upload Resume
                    </button>
                    <input type="file" id="resumeInput" accept=".pdf,.docx" style="display:none;" />
                </div>
                <span class="close-modal" id="closeAddEmployeeModal">&times;</span>
                <form class="add-employee-form" method="POST" action="save_employee.php">
                    <div class="form-row">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-row">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
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
                        <label for="birthday">Birthday</label>
                        <input type="date" id="birthday" name="birthday" required>
                    </div>
                    <div class="form-row">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="civil_status">Civil Status</label>
                        <select id="civil_status" name="civil_status" required>
                            <option value="">Select Civil Status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="widowed">Widowed</option>
                            <option value="divorced">Divorced</option>
                        </select>
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
                        <label for="highest_education">Highest Educational Attainment</label>
                        <input type="text" id="highest_education" name="highest_education" required>
                    </div>
                    <div class="form-row">
                        <label for="nationality">Nationality</label>
                        <input type="text" id="nationality" name="nationality" required>
                    </div>
                    <div class="form-row">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="form-row">
                        <label for="emergency_contact_name">Emergency Contact Name</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" required>
                    </div>
                    <div class="form-row">
                        <label for="emergency_contact_number">Emergency Contact Number</label>
                        <input type="text" id="emergency_contact_number" name="emergency_contact_number" required>
                    </div>
                    <div class="form-row">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Employee List Table -->
        <?php
        include '../includes_files/connection.php';
        $sql = "SELECT e.employee_id, e.first_name, e.last_name, u.email_address, u.job_title, e.department, e.date_hired, e.status FROM employee_info e JOIN user_login u ON e.user_id = u.user_id ORDER BY e.date_hired DESC";
        $result = $conn->query($sql);
        ?>
        <div class="employee-table-container">
        <h2 class="employee-table-title">Employee List</h2>
        <table class="employee-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Job Title</th>
                    <th>Department</th>
                    <th>Date Hired</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['email_address']) ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['job_title']))) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['date_hired']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                        <td style="white-space: nowrap;">
                            <button class="edit-account-btn"><i class="fa-solid fa-pen"></i> Edit</button>
                            <button class="delete-account-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No employees found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
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

            // Resume upload logic
            const uploadResumeBtn = document.getElementById('uploadResumeBtn');
            const resumeInput = document.getElementById('resumeInput');

            if (uploadResumeBtn && resumeInput) {
                uploadResumeBtn.onclick = function() {
                    resumeInput.click();
                };

                resumeInput.onchange = async function(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const fileType = file.name.split('.').pop().toLowerCase();
                    let extractedText = '';
                    const loadingModal = document.getElementById('loadingModal');
                    if (loadingModal) loadingModal.style.display = 'flex';
                    try {
                        if (fileType === 'pdf') {
                            extractedText = await new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const typedarray = new Uint8Array(e.target.result);
                                    pdfjsLib.getDocument({data: typedarray}).promise.then(function(pdf) {
                                        let textPromises = [];
                                        for (let i = 1; i <= pdf.numPages; i++) {
                                            textPromises.push(
                                                pdf.getPage(i).then(function(page) {
                                                    return page.getTextContent().then(function(textContent) {
                                                        return textContent.items.map(item => item.str).join(' ');
                                                    });
                                                })
                                            );
                                        }
                                        Promise.all(textPromises).then(function(pagesText) {
                                            resolve(pagesText.join('\n'));
                                        });
                                    });
                                };
                                reader.onerror = reject;
                                reader.readAsArrayBuffer(file);
                            });
                        } else if (fileType === 'docx') {
                            extractedText = await new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    mammoth.extractRawText({arrayBuffer: e.target.result})
                                        .then(function(result) {
                                            resolve(result.value);
                                        });
                                };
                                reader.onerror = reject;
                                reader.readAsArrayBuffer(file);
                            });
                        } else {
                            alert('Unsupported file type. Please upload a PDF or DOCX.');
                            if (loadingModal) loadingModal.style.display = 'none';
                            return;
                        }
                        // Send to Gemini API
                        const apiKey = 'AIzaSyAd9g6BsW_RMpVaSADoytsMTxsUmYfdcco';
                        const endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' + apiKey;
                        const prompt = `Extract the following fields from this resume and return as JSON with these keys: first_name, last_name, email, birthday, gender, civil_status, job_title, highest_education, nationality, phone_number, emergency_contact_name, emergency_contact_number, address. If a field is missing, leave it blank.\n\nResume:\n${extractedText}`;
                        const body = {
                            contents: [{ parts: [{ text: prompt }] }]
                        };
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(body)
                        });
                        const data = await response.json();
                        let jsonString = '';
                        if (data && data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts && data.candidates[0].content.parts[0].text) {
                            jsonString = data.candidates[0].content.parts[0].text;
                        }
                        let extracted = {};
                        try {
                            extracted = JSON.parse(jsonString);
                        } catch (e) {
                            // Try to extract JSON from text if Gemini returns as code block
                            const match = jsonString.match(/\{[\s\S]*\}/);
                            if (match) {
                                try { extracted = JSON.parse(match[0]); } catch (e2) {}
                            }
                        }
                        // Fill form fields
                        if (extracted) {
                            const map = {
                                first_name: 'first_name',
                                last_name: 'last_name',
                                email: 'email',
                                birthday: 'birthday',
                                gender: 'gender',
                                civil_status: 'civil_status',
                                job_title: 'job_title',
                                highest_education: 'highest_education',
                                nationality: 'nationality',
                                phone_number: 'phone_number',
                                emergency_contact_name: 'emergency_contact_name',
                                emergency_contact_number: 'emergency_contact_number',
                                address: 'address'
                            };
                            // Helper: add autofilled-success and event to remove if cleared
                            function markAutofilled(el) {
                                el.classList.add('autofilled-success');
                                // Remove green border if field is cleared
                                const removeIfEmpty = function() {
                                    if (!el.value) el.classList.remove('autofilled-success');
                                };
                                el.removeEventListener('input', removeIfEmpty); // Prevent stacking
                                el.addEventListener('input', removeIfEmpty);
                            }
                            for (const key in map) {
                                const el = document.getElementById(map[key]);
                                if (el && extracted[key]) {
                                    // Special handling for birthday: accept yyyy-mm-dd or 'Month DD, YYYY' for <input type='date'>
                                    if (key === 'birthday') {
                                        let dateVal = extracted[key].trim();
                                        let valid = false;
                                        // If yyyy-mm-dd, use directly
                                        if (/^\d{4}-\d{2}-\d{2}$/.test(dateVal)) {
                                            valid = true;
                                        } else {
                                            // Try to parse 'Month DD, YYYY' or 'Mon DD, YYYY'
                                            const monthNames = [
                                                'january','february','march','april','may','june','july','august','september','october','november','december',
                                                'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'
                                            ];
                                            const regex = /^([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/;
                                            const match = dateVal.match(regex);
                                            if (match) {
                                                let month = match[1].toLowerCase();
                                                let day = match[2].padStart(2, '0');
                                                let year = match[3];
                                                let monthIdx = monthNames.indexOf(month);
                                                if (monthIdx !== -1) {
                                                    if (monthIdx > 11) monthIdx -= 12; // handle short names
                                                    let mm = String(monthIdx + 1).padStart(2, '0');
                                                    dateVal = `${year}-${mm}-${day}`;
                                                    valid = true;
                                                }
                                            }
                                        }
                                        if (valid) {
                                            el.value = dateVal;
                                            markAutofilled(el);
                                        } else {
                                            el.value = '';
                                            el.classList.remove('autofilled-success');
                                        }
                                        continue;
                                    }
                                    if (el.tagName === 'SELECT') {
                                        // Try to match option value (case-insensitive)
                                        let found = false;
                                        for (const opt of el.options) {
                                            if (opt.value.toLowerCase() === extracted[key].toLowerCase()) {
                                                el.value = opt.value;
                                                found = true;
                                                break;
                                            }
                                        }
                                        if (!found) el.value = '';
                                        if (found) markAutofilled(el);
                                    } else {
                                        el.value = extracted[key];
                                        markAutofilled(el);
                                    }
                                }
                            }
                        }
                    } catch (err) {
                        alert('An error occurred while analyzing the resume.');
                        console.error(err);
                    } finally {
                        if (loadingModal) loadingModal.style.display = 'none';
                    }
                };
            } else {
                if (!uploadResumeBtn) {
                    console.error("Element with ID 'uploadResumeBtn' not found.");
                }
                if (!resumeInput) {
                    console.error("Element with ID 'resumeInput' not found.");
                }
            }

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
        });
        </script>
        <!-- User Roles content goes here -->
    </div>
</body>
</html> 