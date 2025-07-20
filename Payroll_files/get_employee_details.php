<?php
require_once '../includes_files/auth_check.php';
requireLogin();
header('Content-Type: application/json');
include '../includes_files/connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid employee ID']);
    exit;
}

$employee_id = intval($_GET['id']);

$sql = 'SELECT * FROM payroll_employees WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

$employee = $result->fetch_assoc();

// Calculate age from birthday
$age = '';
if (!empty($employee['birthday']) && $employee['birthday'] !== '0000-00-00') {
    $birthday = new DateTime($employee['birthday']);
    $today = new DateTime();
    $age = $today->diff($birthday)->y;
}

// Calculate years of service
$years_of_service = '';
if (!empty($employee['date_hired']) && $employee['date_hired'] !== '0000-00-00') {
    $hired_date = new DateTime($employee['date_hired']);
    $today = new DateTime();
    $years_of_service = $today->diff($hired_date)->y;
}

// Format dates for display
$formatted_birthday = !empty($employee['birthday']) && $employee['birthday'] !== '0000-00-00' ? date('F j, Y', strtotime($employee['birthday'])) : 'Not specified';
$formatted_hired_date = !empty($employee['date_hired']) && $employee['date_hired'] !== '0000-00-00' ? date('F j, Y', strtotime($employee['date_hired'])) : 'Not specified';

// Prepare response data
$response = [
    'id' => $employee['id'],
    'first_name' => $employee['first_name'],
    'last_name' => $employee['last_name'],
    'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
    'department' => $employee['department'] ?? 'Not specified',
    'job_title' => $employee['job_title'] ?? 'Not specified',
    'email' => $employee['email'] ?? 'Not specified',
    'phone_number' => $employee['phone_number'] ?? 'Not specified',
    'address' => $employee['address'] ?? 'Not specified',
    'birthday' => $formatted_birthday,
    'age' => $age ? $age . ' years old' : 'Not available',
    'gender' => $employee['gender'] ?? 'Not specified',
    'date_hired' => $formatted_hired_date,
    'years_of_service' => $years_of_service ? $years_of_service . ' years' : 'Not available',
    'status' => $employee['status'] ?? 'active',
    'photo_path' => $employee['photo_path'] ?? '',
    'created_at' => date('F j, Y g:i A', strtotime($employee['created_at'])),
    'updated_at' => date('F j, Y g:i A', strtotime($employee['updated_at']))
];

echo json_encode($response);
?> 