<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create default admin user
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, email, role) 
            VALUES ('admin', '$hashed_password', 'System Administrator', 'admin@system.com', 'admin')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Default admin user created successfully<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

// Create default regular user
$check_user = "SELECT * FROM users WHERE username = 'user'";
$result = $conn->query($check_user);

if ($result->num_rows == 0) {
    $hashed_password = password_hash('user123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, email, role) 
            VALUES ('user', '$hashed_password', 'Regular User', 'user@system.com', 'user')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Default regular user created successfully<br>";
    } else {
        echo "Error creating regular user: " . $conn->error . "<br>";
    }
} else {
    echo "Regular user already exists<br>";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users Table Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .credentials {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials h2 {
            margin-top: 0;
        }
        .credentials ul {
            list-style: none;
            padding: 0;
        }
        .credentials li {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Users Table Setup Complete</h1>
    
    <div class="credentials">
        <h2>Default Login Credentials</h2>
        
        <h3>Admin Account:</h3>
        <ul>
            <li>Username: admin</li>
            <li>Password: admin123</li>
            <li>Role: admin</li>
        </ul>

        <h3>Regular User Account:</h3>
        <ul>
            <li>Username: user</li>
            <li>Password: user123</li>
            <li>Role: user</li>
        </ul>
    </div>

    <p>You can now <a href="login.php">login</a> with either of these accounts.</p>
</body>
</html> 