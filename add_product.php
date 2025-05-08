<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $condition = $_POST['condition'];
    $min_stock_level = $_POST['min_stock_level'];
    
    // Determine stock level based on quantity
    $stock_level = 'in_stock';
    if ($quantity <= 0) {
        $stock_level = 'out_of_stock';
    } elseif ($quantity <= $min_stock_level) {
        $stock_level = 'low_stock';
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO products (name, description, quantity, price, `condition`, stock_level, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidssi", $name, $description, $quantity, $price, $condition, $stock_level, $min_stock_level);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
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
    <title>Add Product - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2><i class="fas fa-plus"></i> Add New Product</h2>
            <div class="user-info">
                <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="filter-form" style="max-width:600px;margin:auto;flex-direction:column;align-items:stretch;gap:18px;">
            <div style="position:relative;">
                <input type="text" name="name" placeholder="Product Name (e.g., Laptop)" required style="padding-left:36px;">
                <i class="fas fa-tag" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <textarea name="description" placeholder="Product Description (e.g., Dell XPS 13, 8GB RAM)" style="padding-left:36px;"></textarea>
                <i class="fas fa-align-left" style="position:absolute;left:10px;top:18px;color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <input type="number" name="quantity" placeholder="Quantity (e.g., 10)" required style="padding-left:36px;">
                <i class="fas fa-sort-numeric-up" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <input type="number" name="price" step="0.01" placeholder="Price (e.g., 499.99)" required style="padding-left:36px;">
                <i class="fas fa-dollar-sign" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <select name="condition" required style="padding-left:36px;">
                    <option value="">Select Condition</option>
                    <option value="new">New</option>
                    <option value="used">Used</option>
                    <option value="refurbished">Refurbished</option>
                    <option value="damaged">Damaged</option>
                </select>
                <i class="fas fa-clipboard-check" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#4a90e2;"></i>
            </div>
            <div style="position:relative;">
                <input type="number" name="min_stock_level" placeholder="Minimum Stock Level (e.g., 5)" required style="padding-left:36px;">
                <i class="fas fa-exclamation-triangle" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#f57c00;"></i>
            </div>
            <button type="submit"><i class="fas fa-plus-circle"></i> Add Product</button>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </form>
    </div>
</body>
</html> 