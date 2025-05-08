<?php
require_once 'config.php';

echo "<h2>Checking and fixing user accounts...</h2>";

// Function to create or update a user
function createOrUpdateUser($conn, $username, $password, $full_name, $email, $role) {
    // Check if user exists
    $check_sql = "SELECT * FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO users (username, password, full_name, email, role, status) 
                      VALUES (?, ?, ?, ?, ?, 'active')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);
        
        if ($insert_stmt->execute()) {
            echo "Created $role account: $username<br>";
        } else {
            echo "Error creating $role account: " . $insert_stmt->error . "<br>";
        }
    } else {
        // Update existing user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET 
                      password = ?,
                      full_name = ?,
                      email = ?,
                      role = ?,
                      status = 'active'
                      WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssss", $hashed_password, $full_name, $email, $role, $username);
        
        if ($update_stmt->execute()) {
            echo "Updated $role account: $username<br>";
        } else {
            echo "Error updating $role account: " . $update_stmt->error . "<br>";
        }
    }
}

// Create/Update admin user
createOrUpdateUser(
    $conn,
    'admin',
    'admin123',
    'System Administrator',
    'admin@system.com',
    'admin'
);

// Create/Update regular user
createOrUpdateUser(
    $conn,
    'user',
    'user123',
    'Regular User',
    'user@system.com',
    'user'
);

// Display current users
echo "<h2>Current Users in Database:</h2>";
$result = $conn->query("SELECT username, role, status FROM users");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Username</th><th>Role</th><th>Status</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><br>";
echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "<h2>Login Credentials:</h2>";
echo "<h3>Admin Account:</h3>";
echo "<ul>";
echo "<li>Username: admin</li>";
echo "<li>Password: admin123</li>";
echo "<li>Role: admin</li>";
echo "</ul>";

echo "<h3>Regular User Account:</h3>";
echo "<ul>";
echo "<li>Username: user</li>";
echo "<li>Password: user123</li>";
echo "<li>Role: user</li>";
echo "</ul>";
echo "</div>";

echo "<br><p>You can now <a href='login.php'>login</a> with either of these accounts.</p>";
?> 