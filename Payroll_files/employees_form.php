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
    <style>
    .employee-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
        margin-top: 32px;
    }
    .employee-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(25, 118, 210, 0.08);
        padding: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .employee-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(25, 118, 210, 0.15);
    }
    .employee-photo {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        background: #f0f0f0;
        margin-bottom: 16px;
        border: 2px solid #e3e8ee;
    }
    .employee-info {
        text-align: center;
        margin-bottom: 12px;
    }
    .employee-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1976d2;
        margin-bottom: 2px;
    }
    .employee-job {
        font-size: 1rem;
        color: #333;
        margin-bottom: 2px;
    }
    .employee-dept {
        font-size: 0.98rem;
        color: #666;
        margin-bottom: 2px;
    }
    .employee-email {
        font-size: 0.97rem;
        color: #888;
        margin-bottom: 6px;
    }
    .employee-status {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 16px;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 8px;
    }
    .status-active { background: #e8f5e8; color: #2e7d32; border: 1px solid #4caf50; }
    .status-inactive { background: #ffebee; color: #c62828; border: 1px solid #f44336; }
    .employee-card-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 8px;
    }
    .employee-card-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        font-size: 0.98rem;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
    }
    .employee-edit-btn { background: #1976d2; color: #fff; }
    .employee-edit-btn:hover { background: #1565c0; }
    .employee-delete-btn { background: #fff0f0; color: #d32f2f; border: 1px solid #d32f2f; }
    .employee-delete-btn:hover { background: #d32f2f; color: #fff; }
    .photo-upload-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 10px;
    }
    .photo-upload-preview img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e3e8ee;
        margin-bottom: 6px;
    }
    </style>
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
                    <button type="button" class="add-button" id="uploadResumeBtn">
                        <i class="fa-solid fa-upload"></i> Upload Resume
                    </button>
                    <input type="file" id="resumeInput" accept=".pdf,.docx" style="display:none;" />
                </div>
                <span class="close-modal" id="closeAddEmployeeModal">&times;</span>
                <form class="add-employee-form" method="POST" action="save_payroll_employee.php" enctype="multipart/form-data">
                    <div class="photo-upload-preview" id="photoPreviewContainer">
                        <img id="photoPreview" src="https://ui-avatars.com/api/?name=Employee&background=1976d2&color=fff" alt="Employee Photo Preview">
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="margin-top:6px;">
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
                    <div class="employee-card">
                        <img class="employee-card-img" src="<?= $emp['photo'] ? 'Payroll_files/' . htmlspecialchars($emp['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($emp['first_name'] . ' ' . $emp['last_name']) . '&background=1976d2&color=fff' ?>" alt="Employee Photo">
                        <div class="employee-info">
                            <div class="employee-name"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                            <div class="employee-job"><?= htmlspecialchars($emp['job_title']) ?></div>
                            <div class="employee-dept"><?= htmlspecialchars($emp['department']) ?></div>
                            <div class="employee-email"><?= htmlspecialchars($emp['email']) ?></div>
                            <span class="employee-status status-<?= htmlspecialchars($emp['status']) ?>"><?= ucfirst(htmlspecialchars($emp['status'])) ?></span>
                        </div>
                        <div class="employee-card-actions">
                            <button class="employee-card-btn employee-edit-btn"><i class="fa-solid fa-pen"></i> Edit</button>
                            <button class="employee-card-btn employee-delete-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align:center; color:#3a6073; font-size:1.1rem;">No employees found.</div>
            <?php endif; ?>
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
            const photoPreview = document.getElementById('photoPreview');
            if (photoInput && photoPreview) {
                photoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            photoPreview.src = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        photoPreview.src = 'https://ui-avatars.com/api/?name=Employee&background=1976d2&color=fff';
                    }
                });
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