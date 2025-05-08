<?php
require_once 'config.php';

function checkAuth() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    // Check if user exists and is active
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, role, status FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        session_destroy();
        header("Location: login.php?error=invalid_user");
        exit();
    }
    
    $user = $result->fetch_assoc();
    if ($user['status'] !== 'active') {
        session_destroy();
        header("Location: login.php?error=inactive");
        exit();
    }
}

function checkAdmin() {
    checkAuth(); // First check if user is authenticated
    if (!isAdmin()) {
        header("Location: index.php?error=unauthorized");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getUserInfo() {
    global $conn;
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $sql = "SELECT id, username, full_name, email, role, department, status 
            FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to check if user has permission for specific actions
function hasPermission($action) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    // Define permissions for each role
    $permissions = [
        'admin' => [
            'manage_items' => true,
            'manage_users' => true,
            'manage_colleges' => true,
            'manage_departments' => true,
            'manage_offices' => true,
            'manage_suppliers' => true,
            'view_reports' => true,
            'view_transactions' => true,
            'search_items' => true
        ],
        'user' => [
            'manage_items' => false,
            'manage_users' => false,
            'manage_colleges' => false,
            'manage_departments' => false,
            'manage_offices' => false,
            'manage_suppliers' => false,
            'view_reports' => false,
            'view_transactions' => true,
            'search_items' => true
        ]
    ];
    
    return isset($permissions[$_SESSION['role']][$action]) 
           ? $permissions[$_SESSION['role']][$action] 
           : false;
}
?> 