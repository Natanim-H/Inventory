<?php
require_once 'config.php';

// Function to check if a column exists
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return $result->num_rows > 0;
}

// Function to add a column if it doesn't exist
function addColumnIfNotExists($conn, $table, $column, $definition) {
    if (!columnExists($conn, $table, $column)) {
        $sql = "ALTER TABLE $table ADD COLUMN $column $definition";
        if ($conn->query($sql) === TRUE) {
            echo "Added column $column to $table successfully<br>";
        } else {
            echo "Error adding column $column to $table: " . $conn->error . "<br>";
        }
    } else {
        echo "Column $column already exists in $table<br>";
    }
}

// Check and add necessary columns to users table
echo "<h2>Checking and fixing users table structure...</h2>";

// Add status column
addColumnIfNotExists($conn, 'users', 'status', "ENUM('active', 'inactive') DEFAULT 'active'");

// Add full_name column
addColumnIfNotExists($conn, 'users', 'full_name', "VARCHAR(100) NOT NULL DEFAULT 'User'");

// Add email column
addColumnIfNotExists($conn, 'users', 'email', "VARCHAR(100) NOT NULL DEFAULT 'user@system.com'");

// Add department column
addColumnIfNotExists($conn, 'users', 'department', "VARCHAR(100)");

// Add last_login column
addColumnIfNotExists($conn, 'users', 'last_login', "TIMESTAMP NULL");

// Update existing users with default values
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
    echo "Updated existing users with default values successfully<br>";
} else {
    echo "Error updating users: " . $conn->error . "<br>";
}

// Verify admin user
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

// Display current table structure
echo "<h2>Current users table structure:</h2>";
$result = $conn->query("DESCRIBE users");
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";

echo "<br>Database structure has been verified and fixed. You can now <a href='login.php'>login</a>.";
?> 