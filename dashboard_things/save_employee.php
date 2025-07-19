<?php
include '../includes_files/connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'], $_POST['job_title'])
    ) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $job_title = $_POST['job_title'];
        $birthday = isset($_POST['birthday']) ? $_POST['birthday'] : null;
        $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
        $civil_status = isset($_POST['civil_status']) ? $_POST['civil_status'] : null;
        $highest_education = isset($_POST['highest_education']) ? $_POST['highest_education'] : null;
        $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : null;
        $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : null;
        $emergency_contact_name = isset($_POST['emergency_contact_name']) ? $_POST['emergency_contact_name'] : null;
        $emergency_contact_number = isset($_POST['emergency_contact_number']) ? $_POST['emergency_contact_number'] : null;
        $address = isset($_POST['address']) ? $_POST['address'] : null;
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
        if (!$first_name || !$last_name || !$email || !$password || !$job_title) {
            echo '<script>alert("Missing required fields.");window.history.back();</script>';
            exit;
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("SELECT user_id FROM user_login WHERE email_address = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo '<script>alert("Email already exists.");window.history.back();</script>';
            exit;
        }
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO user_login (email_address, password_secret, job_title, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param('sss', $email, $password_hash, $job_title);
        if (!$stmt->execute()) {
            echo '<script>alert("Failed to create user login.");window.history.back();</script>';
            exit;
        }
        $user_id = $stmt->insert_id;
        $stmt->close();
        // Map job_title to department
        $department = null;
        if (strcasecmp($job_title, 'executives') === 0) {
            $department = 'Administration';
        } elseif (strcasecmp($job_title, 'senior_manager') === 0) {
            $department = 'Finance';
        } elseif (strcasecmp($job_title, 'middle_manager') === 0) {
            $department = 'Operations';
        } elseif (strcasecmp($job_title, 'workers') === 0) {
            $department = 'General Services';
        }
        $stmt = $conn->prepare("INSERT INTO employee_info (user_id, first_name, last_name, department, birthday, gender, civil_status, highest_education, nationality, phone_number, emergency_contact_name, emergency_contact_number, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param('issssssssssss', $user_id, $first_name, $last_name, $department, $birthday, $gender, $civil_status, $highest_education, $nationality, $phone_number, $emergency_contact_name, $emergency_contact_number, $address);
        if (!$stmt->execute()) {
            $conn->query("DELETE FROM user_login WHERE user_id = $user_id");
            die('Failed to save employee info: ' . $stmt->error);
        }
        $stmt->close();
        echo '<script>alert("Employee added successfully.");window.location.href="user_roles_form.php";</script>';
        exit;
    } else {
        echo '<script>alert("Missing required fields.");window.history.back();</script>';
        exit;
    }
} else {
    header('Location: user_roles_form.php');
    exit;
} 