<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Set charset to ensure proper encoding
$conn->set_charset("utf8mb4");

// Create suppliers table
$sql = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating suppliers table: " . $conn->error;
}

// Create colleges table
$sql = "CREATE TABLE IF NOT EXISTS colleges (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating colleges table: " . $conn->error;
}

// Create departments table
$sql = "CREATE TABLE IF NOT EXISTS departments (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    college_id INT(6) UNSIGNED,
    code VARCHAR(20) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating departments table: " . $conn->error;
}

// Create offices table
$sql = "CREATE TABLE IF NOT EXISTS offices (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT(6) UNSIGNED,
    code VARCHAR(20) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating offices table: " . $conn->error;
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating categories table: " . $conn->error;
}

// Create items table
$sql = "CREATE TABLE IF NOT EXISTS items (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT(6) UNSIGNED,
    supplier_id INT(6) UNSIGNED,
    quantity INT(6) NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    `condition` ENUM('new', 'used', 'refurbished', 'damaged') NOT NULL DEFAULT 'new',
    stock_level ENUM('in_stock', 'low_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock',
    min_stock_level INT(6) NOT NULL DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating items table: " . $conn->error;
}

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT(6) UNSIGNED,
    type ENUM('received', 'withdrawn') NOT NULL,
    quantity INT(6) NOT NULL,
    office_id INT(6) UNSIGNED,
    notes TEXT,
    created_by INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (office_id) REFERENCES offices(id)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating transactions table: " . $conn->error;
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
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    office_id INT(6) UNSIGNED,
    FOREIGN KEY (office_id) REFERENCES offices(id)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating users table: " . $conn->error;
}

// Create default admin user if not exists
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);
if ($result->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, email, role) 
            VALUES ('admin', '$hashed_password', 'System Administrator', 'admin@system.com', 'admin')";
    $conn->query($sql);
}
?> 