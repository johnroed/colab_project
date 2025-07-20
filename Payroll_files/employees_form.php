<?php include '../dashboard_things/sidebar_form.php'; ?>
<?php
include '../includes_files/connection.php';
$employees = [];
$sql = 'SELECT * FROM payroll_employees ORDER BY date_hired DESC';
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Assume photo_path column exists; if not, use a default avatar
        $row['photo'] = !empty($row['photo_path']) ? $row['photo_path'] : '';
        $employees[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
    <link rel="stylesheet" href="../dashboard_things/users_roles_style.css">
    <script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
    <!-- PDF.js and Mammoth.js for resume parsing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.2.0/mammoth.browser.min.js"></script>
    <link rel="stylesheet" href="employees.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-users"></i> Employees</h1>
        </div>
        <div class="action-buttons">
            <button class="add-button" id="openAddEmployeeModal">
                <i class="fa-solid fa-plus"></i> Add Employee
            </button>
        </div>
        <!-- Add Employee Modal -->
        <div id="addEmployeeModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add New Employee</h2>
                    <button type="button" class="add-button" id="uploadResumeBtn" style="margin-left:-12px;">
                        <i class="fa-solid fa-upload"></i> Upload Resume
                    </button>
                    <input type="file" id="resumeInput" accept=".pdf,.docx" style="display:none;" />
                </div>
                <span class="close-modal" id="closeAddEmployeeModal">&times;</span>
                <form class="add-employee-form" method="POST" action="save_payroll_employee.php" enctype="multipart/form-data">
                    <div class="photo-upload-preview" id="photoPreviewContainer">
                        <button type="button" id="uploadPhotoBtn" class="upload-btn">Upload Photo</button>
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none;">
                        <span id="selectedPhotoName" style="font-size:0.98rem; color:#1976d2; margin-left:8px;"></span>
                    </div>
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
                        <input type="text" id="job_title" name="job_title" required>
                    </div>
                    <div class="form-row">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" required>
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
                    <div class="form-row">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-row form-actions">
                        <button type="submit" class="add-button">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Employee Card Grid -->
        <div class="employee-card-grid">
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $emp): ?>
                    <?php
                    // Calculate age from birthday
                    $age = '';
                    if (!empty($emp['birthday'])) {
                        $birthday = new DateTime($emp['birthday']);
                        $today = new DateTime();
                        $age = $today->diff($birthday)->y;
                    }
                    ?>
                    <div class="employee-card">
                        <div class="employee-card-top">
                            <img class="employee-photo" src="<?= $emp['photo'] ? htmlspecialchars($emp['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($emp['first_name'] . ' ' . $emp['last_name']) . '&background=1976d2&color=fff&size=200' ?>" alt="Employee Photo">
                        </div>
                        <div class="employee-card-bottom">
                            <div class="employee-name">
                                <i class="fa-solid fa-user"></i>
                                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                            </div>
                            <div class="employee-age">
                                <i class="fa-solid fa-birthday-cake"></i>
                                <?= $age ? $age . ' years old' : 'Age not available' ?>
                            </div>
                            <div class="employee-department">
                                <i class="fa-solid fa-building"></i>
                                <?= htmlspecialchars($emp['department'] ?? 'Department not set') ?>
                            </div>
                            <div class="employee-card-actions">
                                <button class="employee-btn details-btn" onclick="viewEmployeeDetails(<?= $emp['id'] ?>)">
                                    <i class="fa-solid fa-eye"></i>
                                    Details
                                </button>
                                <button class="employee-btn fire-btn" onclick="fireEmployee(<?= $emp['id'] ?>)">
                                    <i class="fa-solid fa-fire"></i>
                                    Fire
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align:center; color:#3a6073; font-size:1.1rem;">No employees found.</div>
            <?php endif; ?>
        </div>
        
        <!-- Fire Employee Modal -->
        <div id="fireEmployeeModal" class="modal-overlay">
            <div class="modal-content fire-employee-modal">
                <div class="modal-header">
                    <h2>Fire Employee</h2>
                    <span class="close-modal" id="closeFireEmployeeModal">&times;</span>
                </div>
                <form class="fire-employee-form" id="fireEmployeeForm">
                    <div class="fire-employee-content">
                        <div class="employee-info-section">
                            <h4><i class="fa-solid fa-user"></i> Employee Information</h4>
                            <div class="employee-info-grid">
                                <div class="info-item">
                                    <label>Employee ID:</label>
                                    <input type="text" id="fireEmployeeId" name="employee_id" readonly>
                                </div>
                                <div class="info-item">
                                    <label>Full Name:</label>
                                    <input type="text" id="fireEmployeeName" name="employee_name" readonly>
                                </div>
                                <div class="info-item">
                                    <label>Department:</label>
                                    <input type="text" id="fireEmployeeDepartment" name="department" readonly>
                                </div>
                                <div class="info-item">
                                    <label>Job Title:</label>
                                    <input type="text" id="fireEmployeeJobTitle" name="job_title" readonly>
                                </div>
                                <div class="info-item">
                                    <label>Date Hired:</label>
                                    <input type="text" id="fireEmployeeDateHired" name="date_hired" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="termination-section">
                            <h4><i class="fa-solid fa-exclamation-triangle"></i> Termination Details</h4>
                            <div class="termination-form">
                                <div class="form-row">
                                    <label for="date_fired">Date Fired:</label>
                                    <input type="date" id="date_fired" name="date_fired" required>
                                </div>
                                <div class="form-row">
                                    <label for="reason">Reason for Termination:</label>
                                    <textarea id="reason" name="reason" rows="4" required placeholder="Please provide a detailed reason for termination..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="fire-employee-actions">
                        <button type="button" class="employee-btn details-btn" id="cancelFireEmployee">
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="employee-btn fire-btn">
                            <i class="fa-solid fa-fire"></i> Confirm Termination
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Employee Details Modal -->
        <div id="employeeDetailsModal" class="modal-overlay">
            <div class="modal-content employee-details-modal">
                <div class="modal-header">
                    <h2 id="employeeDetailsTitle">Employee Details</h2>
                </div>
                <div class="employee-details-content">
                    <div class="employee-details-left">
                        <div class="employee-photo-container">
                            <img id="employeeDetailsPhoto" src="" alt="Employee Photo" class="employee-details-photo">
                            <div class="employee-status-badge" id="employeeStatusBadge"></div>
                        </div>
                        <div class="employee-basic-info">
                            <h3 id="employeeDetailsName">Employee Name</h3>
                            <p id="employeeDetailsJobTitle" class="job-title">Job Title</p>
                            <p id="employeeDetailsDepartment" class="department">Department</p>
                        </div>
                        <div class="employee-details-actions">
                            <button class="employee-btn edit-btn" id="editEmployeeFromDetails">
                                <i class="fa-solid fa-pen"></i> Edit Employee
                            </button>
                            <button class="employee-btn details-btn" id="closeEmployeeDetails">
                                <i class="fa-solid fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                    <div class="employee-details-right">
                        <div class="details-section">
                            <h4><i class="fa-solid fa-user"></i> Personal Information</h4>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Full Name:</span>
                                    <span class="detail-value" id="detailFullName">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Age:</span>
                                    <span class="detail-value" id="detailAge">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Gender:</span>
                                    <span class="detail-value" id="detailGender">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Birthday:</span>
                                    <span class="detail-value" id="detailBirthday">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Address:</span>
                                    <span class="detail-value" id="detailAddress">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="details-section">
                            <h4><i class="fa-solid fa-briefcase"></i> Employment Information</h4>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Job Title:</span>
                                    <span class="detail-value" id="detailJobTitle">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Department:</span>
                                    <span class="detail-value" id="detailDepartment">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date Hired:</span>
                                    <span class="detail-value" id="detailDateHired">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Years of Service:</span>
                                    <span class="detail-value" id="detailYearsOfService">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value" id="detailStatus">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="details-section">
                            <h4><i class="fa-solid fa-address-book"></i> Contact Information</h4>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value" id="detailEmail">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Phone Number:</span>
                                    <span class="detail-value" id="detailPhone">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="details-section">
                            <h4><i class="fa-solid fa-clock"></i> System Information</h4>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Created:</span>
                                    <span class="detail-value" id="detailCreated">-</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Last Updated:</span>
                                    <span class="detail-value" id="detailUpdated">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        
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
        </style>
        <script>
        // Employee action functions
        async function viewEmployeeDetails(employeeId) {
            try {
                // Show loading state
                const modal = document.getElementById('employeeDetailsModal');
                const loadingModal = document.getElementById('loadingModal');
                if (loadingModal) loadingModal.style.display = 'flex';
                
                // Fetch employee details
                const response = await fetch(`get_employee_details.php?id=${employeeId}`);
                if (!response.ok) {
                    throw new Error('Failed to fetch employee details');
                }
                
                const employee = await response.json();
                
                // Populate modal with employee data
                document.getElementById('employeeDetailsTitle').textContent = `Employee Details - ${employee.full_name}`;
                document.getElementById('employeeDetailsName').textContent = employee.full_name;
                document.getElementById('employeeDetailsJobTitle').textContent = employee.job_title;
                document.getElementById('employeeDetailsDepartment').textContent = employee.department;
                
                // Set photo
                const photoElement = document.getElementById('employeeDetailsPhoto');
                if (employee.photo_path) {
                    photoElement.src = employee.photo_path;
                } else {
                    photoElement.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(employee.full_name)}&background=1976d2&color=fff&size=300`;
                }
                
                // Set status badge
                const statusBadge = document.getElementById('employeeStatusBadge');
                statusBadge.textContent = employee.status.charAt(0).toUpperCase() + employee.status.slice(1);
                statusBadge.className = `employee-status-badge status-${employee.status}`;
                
                // Populate details
                document.getElementById('detailFullName').textContent = employee.full_name;
                document.getElementById('detailAge').textContent = employee.age;
                document.getElementById('detailGender').textContent = employee.gender;
                document.getElementById('detailBirthday').textContent = employee.birthday;
                document.getElementById('detailAddress').textContent = employee.address;
                document.getElementById('detailJobTitle').textContent = employee.job_title;
                document.getElementById('detailDepartment').textContent = employee.department;
                document.getElementById('detailDateHired').textContent = employee.date_hired;
                document.getElementById('detailYearsOfService').textContent = employee.years_of_service;
                document.getElementById('detailStatus').textContent = employee.status.charAt(0).toUpperCase() + employee.status.slice(1);
                document.getElementById('detailEmail').textContent = employee.email;
                document.getElementById('detailPhone').textContent = employee.phone_number;
                document.getElementById('detailCreated').textContent = employee.created_at;
                document.getElementById('detailUpdated').textContent = employee.updated_at;
                
                // Store employee ID for edit functionality
                document.getElementById('editEmployeeFromDetails').setAttribute('data-employee-id', employee.id);
                
                // Show modal
                if (modal) modal.style.display = 'flex';
                
            } catch (error) {
                console.error('Error fetching employee details:', error);
                alert('Failed to load employee details. Please try again.');
            } finally {
                // Hide loading modal
                const loadingModal = document.getElementById('loadingModal');
                if (loadingModal) loadingModal.style.display = 'none';
            }
        }
        
        async function fireEmployee(employeeId) {
            try {
                // Show loading state
                const loadingModal = document.getElementById('loadingModal');
                if (loadingModal) loadingModal.style.display = 'flex';
                
                // Fetch employee details
                const response = await fetch(`get_employee_details.php?id=${employeeId}`);
                if (!response.ok) {
                    throw new Error('Failed to fetch employee details');
                }
                
                const employee = await response.json();
                
                // Populate fire employee modal with employee data
                document.getElementById('fireEmployeeId').value = employee.id;
                document.getElementById('fireEmployeeName').value = employee.full_name;
                document.getElementById('fireEmployeeDepartment').value = employee.department;
                document.getElementById('fireEmployeeJobTitle').value = employee.job_title;
                document.getElementById('fireEmployeeDateHired').value = employee.date_hired;
                
                // Set today's date as default for date fired
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('date_fired').value = today;
                
                // Show modal
                const fireModal = document.getElementById('fireEmployeeModal');
                if (fireModal) fireModal.style.display = 'flex';
                
            } catch (error) {
                console.error('Error fetching employee details:', error);
                alert('Failed to load employee details. Please try again.');
            } finally {
                // Hide loading modal
                const loadingModal = document.getElementById('loadingModal');
                if (loadingModal) loadingModal.style.display = 'none';
            }
        }
        
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
                        // Update Gemini prompt to request full_name
                        const prompt = `Extract the following fields from this resume and return as JSON with these keys: full_name, email, birthday, gender, civil_status, job_title, department, highest_education, nationality, phone_number, emergency_contact_name, emergency_contact_number, address. If a field is missing, leave it blank.\n\nResume:\n${extractedText}`;
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
                            // Handle full_name splitting for first/last name fields
                            if (extracted.full_name) {
                                const nameParts = extracted.full_name.trim().split(/\s+/);
                                if (nameParts.length === 1) {
                                    document.getElementById('first_name').value = nameParts[0];
                                    document.getElementById('last_name').value = '';
                                } else if (nameParts.length === 2) {
                                    document.getElementById('first_name').value = nameParts[0];
                                    document.getElementById('last_name').value = nameParts[1];
                                } else if (nameParts.length > 2) {
                                    document.getElementById('first_name').value = nameParts.slice(0, -1).join(' ');
                                    document.getElementById('last_name').value = nameParts.slice(-1)[0];
                                }
                                markAutofilled(document.getElementById('first_name'));
                                markAutofilled(document.getElementById('last_name'));
                            } else {
                                // Fallback to Gemini's first_name/last_name if present
                                if (extracted.first_name) {
                                    document.getElementById('first_name').value = extracted.first_name;
                                    markAutofilled(document.getElementById('first_name'));
                                }
                                if (extracted.last_name) {
                                    document.getElementById('last_name').value = extracted.last_name;
                                    markAutofilled(document.getElementById('last_name'));
                                }
                            }
                            const map = {
                                email: 'email',
                                birthday: 'birthday',
                                gender: 'gender',
                                civil_status: 'civil_status',
                                job_title: 'job_title',
                                department: 'department',
                                highest_education: 'highest_education',
                                nationality: 'nationality',
                                phone_number: 'phone_number',
                                emergency_contact_name: 'emergency_contact_name',
                                emergency_contact_number: 'emergency_contact_number',
                                address: 'address'
                            };
                            function markAutofilled(el) {
                                el.classList.add('autofilled-success');
                                const removeIfEmpty = function() {
                                    if (!el.value) el.classList.remove('autofilled-success');
                                };
                                el.removeEventListener('input', removeIfEmpty);
                                el.addEventListener('input', removeIfEmpty);
                            }
                            for (const key in map) {
                                const el = document.getElementById(map[key]);
                                if (el && extracted[key]) {
                                    if (key === 'birthday') {
                                        let dateVal = extracted[key].trim();
                                        let valid = false;
                                        if (/^\d{4}-\d{2}-\d{2}$/.test(dateVal)) {
                                            valid = true;
                                        } else {
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
                                                    if (monthIdx > 11) monthIdx -= 12;
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
            }
            // Photo upload preview
            const photoInput = document.getElementById('photoInput');
            const selectedPhotoName = document.getElementById('selectedPhotoName');
            const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
            if (uploadPhotoBtn && photoInput) {
                uploadPhotoBtn.onclick = function() {
                    photoInput.click();
                };
                photoInput.onchange = function() {
                    if (photoInput.files && photoInput.files.length > 0) {
                        selectedPhotoName.textContent = photoInput.files[0].name;
                    } else {
                        selectedPhotoName.textContent = '';
                    }
                };
            }
            // Employee Details Modal Event Listeners
            const employeeDetailsModal = document.getElementById('employeeDetailsModal');
            const closeEmployeeDetails = document.getElementById('closeEmployeeDetails');
            const editEmployeeFromDetails = document.getElementById('editEmployeeFromDetails');
            
            if (closeEmployeeDetails && employeeDetailsModal) {
                closeEmployeeDetails.onclick = function() {
                    employeeDetailsModal.style.display = 'none';
                };
            }
            
            if (editEmployeeFromDetails) {
                editEmployeeFromDetails.onclick = function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (employeeId) {
                        employeeDetailsModal.style.display = 'none';
                        editEmployee(employeeId);
                    }
                };
            }
            
            // Fire Employee Modal Event Listeners
            const fireEmployeeModal = document.getElementById('fireEmployeeModal');
            const closeFireEmployeeModal = document.getElementById('closeFireEmployeeModal');
            const cancelFireEmployee = document.getElementById('cancelFireEmployee');
            
            if (closeFireEmployeeModal && fireEmployeeModal) {
                closeFireEmployeeModal.onclick = function() {
                    fireEmployeeModal.style.display = 'none';
                };
            }
            
            if (cancelFireEmployee && fireEmployeeModal) {
                cancelFireEmployee.onclick = function() {
                    fireEmployeeModal.style.display = 'none';
                };
            }
            
            // Handle fire employee form submission
            const fireEmployeeForm = document.getElementById('fireEmployeeForm');
            if (fireEmployeeForm) {
                fireEmployeeForm.onsubmit = async function(e) {
                    e.preventDefault();
                    
                    // Show loading state
                    const loadingModal = document.getElementById('loadingModal');
                    if (loadingModal) loadingModal.style.display = 'flex';
                    
                    try {
                        const formData = new FormData(this);
                        
                        const response = await fetch('fire_employee.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Show success message
                            alert(`Success! ${result.employee_name} has been terminated on ${result.date_fired}.`);
                            
                            // Close modal
                            fireEmployeeModal.style.display = 'none';
                            
                            // Reload page to update employee list
                            window.location.reload();
                        } else {
                            // Show error message
                            alert('Error: ' + (result.error || 'Failed to terminate employee'));
                        }
                        
                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('An error occurred while processing the termination. Please try again.');
                    } finally {
                        // Hide loading modal
                        if (loadingModal) loadingModal.style.display = 'none';
                    }
                };
            }
            
            // Close modal when clicking outside (only for add employee modal)
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
                // Removed outside click for employee details modal to prevent accidental closing
            }
        });
        </script>
    </div>
</body>
</html>