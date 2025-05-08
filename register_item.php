<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

// Get all categories and suppliers for the dropdowns
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $condition = $_POST['condition'];
    $min_stock_level = $_POST['min_stock_level'];
    
    // Determine stock level based on quantity
    $stock_level = 'in_stock';
    if ($quantity <= 0) {
        $stock_level = 'out_of_stock';
    } elseif ($quantity <= $min_stock_level) {
        $stock_level = 'low_stock';
    }

    $stmt = $conn->prepare("INSERT INTO items (name, description, category_id, supplier_id, quantity, unit_price, `condition`, stock_level, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiidssi", $name, $description, $category_id, $supplier_id, $quantity, $unit_price, $condition, $stock_level, $min_stock_level);

    if ($stmt->execute()) {
        header("Location: index.php?success=5");
    } else {
        $error = "Error adding item: " . $stmt->error;
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
    <title>Register Item - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2>Register New Item</h2>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="filter-form" style="max-width:600px;margin:auto;flex-direction:column;align-items:stretch;">
            <input type="text" name="name" placeholder="Item Name" required>
            <textarea name="description" placeholder="Item Description"></textarea>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo $supplier['id']; ?>">
                        <?php echo htmlspecialchars($supplier['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <input type="number" name="unit_price" step="0.01" placeholder="Unit Price" required>
            <select name="condition" required>
                <option value="new">New</option>
                <option value="used">Used</option>
                <option value="refurbished">Refurbished</option>
                <option value="damaged">Damaged</option>
            </select>
            <input type="number" name="min_stock_level" placeholder="Minimum Stock Level" value="10" required>
            <button type="submit">Register Item</button>
            <a href="index.php" class="back-btn">Back to Dashboard</a>
        </form>
    </div>
</body>
</html> 