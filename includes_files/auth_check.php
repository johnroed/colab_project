<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = isset($_SESSION['job_title']) ? strtolower($_SESSION['job_title']) : '';
    $required_role = strtolower($required_role);
    
    // Role hierarchy (higher roles have access to lower role functions)
    $role_hierarchy = [
        'executives' => 4,
        'senior_manager' => 3,
        'middle_manager' => 2,
        'workers' => 1
    ];
    
    $user_level = isset($role_hierarchy[$user_role]) ? $role_hierarchy[$user_role] : 0;
    $required_level = isset($role_hierarchy[$required_role]) ? $role_hierarchy[$required_role] : 0;
    
    return $user_level >= $required_level;
}

// Check if user can access management settings
function canAccessManagement() {
    return hasRole('senior_manager'); // Only senior managers and above can access management
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /colab_project/login_files/login_form.php?error=' . urlencode('Please log in to access this page.'));
        exit();
    }
}

// Redirect if user doesn't have required role
function requireRole($required_role) {
    requireLogin();
    
    if (!hasRole($required_role)) {
        header('Location: /colab_project/dashboard_things/dashboard_form.php?error=' . urlencode('You do not have permission to access this page.'));
        exit();
    }
}

// Check if user can access management settings
function requireManagementAccess() {
    requireLogin();
    
    if (!canAccessManagement()) {
        header('Location: /colab_project/dashboard_things/dashboard_form.php?error=' . urlencode('You do not have permission to access management settings.'));
        exit();
    }
}

// Get current user information
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'job_title' => $_SESSION['job_title'] ?? '',
        'role_level' => getRoleLevel($_SESSION['job_title'] ?? '')
    ];
}

// Get role level for comparison
function getRoleLevel($role) {
    $role_hierarchy = [
        'executives' => 4,
        'senior_manager' => 3,
        'middle_manager' => 2,
        'workers' => 1
    ];
    
    return isset($role_hierarchy[strtolower($role)]) ? $role_hierarchy[strtolower($role)] : 0;
}

// Log user activity (optional)
function logUserActivity($action, $details = '') {
    if (!isLoggedIn()) {
        return;
    }
    
    // You can implement logging here if needed
    // For now, we'll just return
    return;
}
?> 