<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $contact, $address);

    if ($stmt->execute()) {
        header("Location: index.php?success=6");
    } else {
        $error = "Error adding supplier: " . $stmt->error;
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
    <title>Register Supplier - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2><i class="fas fa-truck"></i> Register New Supplier</h2>
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
                <input type="text" name="name" placeholder="0f4 Supplier Name (e.g., ABC Supplies)" required style="padding-left:36px;">
                <i class="fas fa-truck" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <input type="text" name="contact" placeholder="2b6 Contact Info (e.g., John Doe, 555-1234)" required style="padding-left:36px;">
                <i class="fas fa-phone" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <textarea name="address" placeholder="015 Address (e.g., 123 Main St, City)" required style="padding-left:36px;"></textarea>
                <i class="fas fa-map-marker-alt" style="position:absolute;left:10px;top:18px;color:#4a90e2;"></i>
            </div>
            <button type="submit"><i class="fas fa-plus-circle"></i> Register Supplier</button>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </form>
    </div>
</body>
</html> 