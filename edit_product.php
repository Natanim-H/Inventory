<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

// Check if id is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=invalid_product");
    exit();
}

$id = $_GET['id'];

// Validate that the product exists
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Query failed: " . $stmt->error);
}
$product = $result->fetch_assoc();
$stmt->close();

// If product doesn't exist, redirect to index
if (!$product) {
    die("Product not found for ID: " . htmlspecialchars($id));
    // header("Location: index.php?error=product_not_found");
    // exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
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

    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, quantity = ?, price = ?, `condition` = ?, stock_level = ?, min_stock_level = ? WHERE id = ?");
    $stmt->bind_param("ssidssii", $name, $description, $quantity, $price, $condition, $stock_level, $min_stock_level, $id);

    if ($stmt->execute()) {
        header("Location: index.php?success=2");
    } else {
        $error = "Error updating product: " . $stmt->error;
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
    <title>Edit Product - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2>Edit Product</h2>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="filter-form" style="max-width:600px;margin:auto;flex-direction:column;align-items:stretch;">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            <textarea name="description" placeholder="Product Description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            <input type="number" name="quantity" placeholder="Quantity" value="<?php echo $product['quantity']; ?>" required>
            <input type="number" name="price" step="0.01" placeholder="Price" value="<?php echo $product['price']; ?>" required>
            <select name="condition" required>
                <option value="new" <?php echo $product['condition'] == 'new' ? 'selected' : ''; ?>>New</option>
                <option value="used" <?php echo $product['condition'] == 'used' ? 'selected' : ''; ?>>Used</option>
                <option value="refurbished" <?php echo $product['condition'] == 'refurbished' ? 'selected' : ''; ?>>Refurbished</option>
                <option value="damaged" <?php echo $product['condition'] == 'damaged' ? 'selected' : ''; ?>>Damaged</option>
            </select>
            <input type="number" name="min_stock_level" placeholder="Minimum Stock Level" value="<?php echo $product['min_stock_level']; ?>" required>
            <button type="submit">Update Product</button>
            <a href="index.php" class="back-btn">Back to Inventory</a>
        </form>
    </div>
</body>
</html> 