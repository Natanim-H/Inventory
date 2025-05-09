<?php
require_once 'config.php';

// Handle error messages
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'inactive':
            $error_message = 'Your account is inactive. Please contact administrator.';
            break;
        case 'invalid_user':
            $error_message = 'Invalid user account.';
            break;
        case 'unauthorized':
            $error_message = 'You are not authorized to access that page.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $sql = "SELECT id, username, password, role, full_name, email, department, status 
            FROM users WHERE username = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] !== 'active') {
            $error_message = "Your account is inactive. Please contact administrator.";
        } else if (password_verify($password, $user['password'])) {
            // Set all user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'] ?: $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['department'] = $user['department'];
            
            // Update last login
            $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid password";
        }
    } else {
        $error_message = "User not found or incorrect role selected";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: url('./image.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px var(--shadow-color);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .error {
            background: var(--error-bg);
            color: var(--error-color);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
        }

        .login-btn {
            background: var(--btn-bg);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background: var(--btn-hover);
        }

        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .role-option {
            flex: 1;
            padding: 15px;
            border: 2px solid var(--role-option-border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option.selected {
            border-color: var(--role-selected-border);
            background: var(--role-selected-bg);
        }

        .role-option h3 {
            margin: 0 0 10px 0;
        }

        .role-option p {
            margin: 0;
            opacity: 0.7;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode">
        <i class="fas fa-sun"></i>
    </button>

    <div class="login-container">
        <h1>Inventory Management System</h1>
        <h2>Mekelle University</h2>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="role-selector">
                <div class="role-option" onclick="selectRole('admin')">
                    <h3>Admin Login</h3>
                    <p>Full system access</p>
                </div>
                <div class="role-option" onclick="selectRole('user')">
                    <h3>User Login</h3>
                    <p>Limited access</p>
                </div>
            </div>

            <input type="hidden" name="role" id="selectedRole" value="user">
            
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>

    <script src="theme.js"></script>
    <script>
        function selectRole(role) {
            document.getElementById('selectedRole').value = role;
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }

        // Set default selection
        document.addEventListener('DOMContentLoaded', function() {
            selectRole('user');
        });
    </script>
</body>
</html> 