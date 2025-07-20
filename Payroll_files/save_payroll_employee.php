<?php
require_once '../includes_files/auth_check.php';
requireLogin();
include '../includes_files/connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $job_title = trim($_POST['job_title'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $date_hired = date('Y-m-d'); // Set current date as hire date
    $status = trim($_POST['status'] ?? 'active');
    $address = trim($_POST['address'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $photo_path = '';
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['photo']['size'] <= $maxSize) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = uniqid('emp_', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photo_path = 'uploads/' . $filename;
            }
        }
    }
    $stmt = $conn->prepare("INSERT INTO payroll_employees (first_name, last_name, department, job_title, email, phone_number, date_hired, status, photo_path, address, birthday, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssssssss', $first_name, $last_name, $department, $job_title, $email, $phone_number, $date_hired, $status, $photo_path, $address, $birthday, $gender);
    if ($stmt->execute()) {
        header('Location: employees_form.php?success=Employee added successfully');
        exit;
    } else {
        header('Location: employees_form.php?error=Failed to add employee');
        exit;
    }
}
header('Location: employees_form.php');
exit; 