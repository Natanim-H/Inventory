<?php
require_once 'config.php';

// First, check if the status column exists
$check_column = "SHOW COLUMNS FROM users LIKE 'status'";
$result = $conn->query($check_column);

if ($result->num_rows == 0) {
    // Add status column if it doesn't exist
    $alter_sql = "ALTER TABLE users 
                  ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active',
                  ADD COLUMN full_name VARCHAR(100) NOT NULL DEFAULT 'User',
                  ADD COLUMN email VARCHAR(100) NOT NULL DEFAULT 'user@system.com',
                  ADD COLUMN department VARCHAR(100),
                  ADD COLUMN last_login TIMESTAMP NULL";
    
    if ($conn->query($alter_sql) === TRUE) {
        echo "Added new columns successfully<br>";
    } else {
        echo "Error adding new columns: " . $conn->error . "<br>";
    }
}

// Update existing users to have default values
$update_sql = "UPDATE users SET 
               full_name = CASE 
                   WHEN full_name = 'User' THEN username 
                   ELSE full_name 
               END,
               email = CASE 
                   WHEN email = 'user@system.com' THEN CONCAT(username, '@system.com')
                   ELSE email 
               END,
               status = 'active'";
               
if ($conn->query($update_sql) === TRUE) {
    echo "Updated existing users successfully<br>";
} else {
    echo "Error updating users: " . $conn->error . "<br>";
}

// Ensure admin user exists with correct role
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO users (username, password, full_name, email, role, status) 
                   VALUES ('admin', ?, 'System Administrator', 'admin@system.com', 'admin', 'active')";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully<br>";
    } else {
        echo "Error creating admin user: " . $stmt->error . "<br>";
    }
} else {
    // Update admin user if exists
    $update_admin = "UPDATE users SET 
                     role = 'admin',
                     status = 'active',
                     full_name = 'System Administrator',
                     email = 'admin@system.com'
                     WHERE username = 'admin'";
    if ($conn->query($update_admin) === TRUE) {
        echo "Admin user updated successfully<br>";
    } else {
        echo "Error updating admin user: " . $conn->error . "<br>";
    }
}

echo "<br>Update completed. You can now <a href='login.php'>login</a> with your credentials.";
?> 