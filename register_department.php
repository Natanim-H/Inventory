<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

// Get all colleges for the dropdown
$colleges = $conn->query("SELECT * FROM colleges ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $college_id = $_POST['college_id'];

    $stmt = $conn->prepare("INSERT INTO departments (name, college_id) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $college_id);

    if ($stmt->execute()) {
        header("Location: index.php?success=3");
    } else {
        $error = "Error adding department: " . $stmt->error;
    }

    $stmt->close();
}

// Get user info for header
$display_name = isset($_SESSION['full_name']) && !empty($_SESSION['full_name']) 
    ? $_SESSION['full_name'] 
    : $_SESSION['username'];
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Department - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2><i class="fas fa-building"></i> Register New Department</h2>
            <div class="user-info">
                <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="filter-form" style="max-width:400px;margin:auto;flex-direction:column;align-items:stretch;gap:18px;">
            <div style="position:relative;">
                <input type="text" name="name" placeholder="0e2 Department Name (e.g., Computer Science)" required style="padding-left:36px;">
                <i class="fas fa-building-columns" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <select name="college_id" required style="padding-left:36px;">
                    <option value="">Select College</option>
                    <?php while ($college = $colleges->fetch_assoc()): ?>
                        <option value="<?php echo $college['id']; ?>"><?php echo htmlspecialchars($college['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <i class="fas fa-university" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <button type="submit"><i class="fas fa-plus-circle"></i> Register Department</button>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </form>
    </div>
</body>
</html> 