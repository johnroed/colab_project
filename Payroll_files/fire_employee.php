<?php
include '../includes_files/connection.php';

// Set content type to JSON for response
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate required fields
if (!isset($_POST['employee_id']) || !isset($_POST['date_fired']) || !isset($_POST['reason'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$employee_id = intval($_POST['employee_id']);
$date_fired = $_POST['date_fired'];
$reason = trim($_POST['reason']);

// Validate data
if ($employee_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid employee ID']);
    exit;
}

if (empty($reason)) {
    http_response_code(400);
    echo json_encode(['error' => 'Reason for termination is required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_fired)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // First, get the employee data from payroll_employees
    $get_employee_sql = 'SELECT * FROM payroll_employees WHERE id = ?';
    $get_employee_stmt = $conn->prepare($get_employee_sql);
    $get_employee_stmt->bind_param('i', $employee_id);
    $get_employee_stmt->execute();
    $result = $get_employee_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Employee not found');
    }
    
    $employee = $result->fetch_assoc();
    
    // Store employee data before deletion
    $employee_data = [
        'id' => $employee['id'],
        'first_name' => $employee['first_name'],
        'last_name' => $employee['last_name'],
        'department' => $employee['department'],
        'job_title' => $employee['job_title'],
        'date_hired' => $employee['date_hired']
    ];
    
    // Delete from payroll_employees table FIRST
    $delete_sql = 'DELETE FROM payroll_employees WHERE id = ?';
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $employee_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete from payroll_employees table');
    }
    
    // Check if deletion was successful
    if ($delete_stmt->affected_rows === 0) {
        throw new Exception('No employee was deleted');
    }
    
    // NOW insert into fired_employees table (with employee_id since we removed the foreign key constraint)
    $insert_fired_sql = 'INSERT INTO fired_employees (
        employee_id,
        first_name, 
        last_name, 
        department, 
        job_title, 
        date_hired, 
        date_fired, 
        reason, 
        fired_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
    
    $insert_fired_stmt = $conn->prepare($insert_fired_sql);
    $fired_by = null; // Set to null for now since we don't have session management
    
    $insert_fired_stmt->bind_param('isssssssi', 
        $employee_data['id'],
        $employee_data['first_name'],
        $employee_data['last_name'],
        $employee_data['department'],
        $employee_data['job_title'],
        $employee_data['date_hired'],
        $date_fired,
        $reason,
        $fired_by
    );
    
    if (!$insert_fired_stmt->execute()) {
        throw new Exception('Failed to insert into fired_employees table: ' . $insert_fired_stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Employee has been successfully terminated',
        'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'date_fired' => $date_fired
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to terminate employee: ' . $e->getMessage()
    ]);
    
} finally {
    // Close statements
    if (isset($get_employee_stmt)) $get_employee_stmt->close();
    if (isset($insert_fired_stmt)) $insert_fired_stmt->close();
    if (isset($delete_stmt)) $delete_stmt->close();
}

$conn->close();
?> 