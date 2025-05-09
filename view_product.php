<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAuth();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('No product ID specified.');
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, s.name as supplier_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE p.id = ?");
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

if (!$product) {
    die('Product not found.');
}

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
    <title>View Product - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode">
        <i class="fas fa-sun"></i>
    </button>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2><i class="fas fa-eye"></i> View Product</h2>
            <div class="user-info">
                <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="card" style="max-width:600px;margin:auto;padding:32px 24px;">
            <h3 style="margin-bottom:18px;"><i class="fas fa-box"></i> <?php echo htmlspecialchars($product['name']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
            <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
            <p><strong>Price:</strong> <?php echo isset($product['price']) ? number_format($product['price'], 2) : '-'; ?></p>
            <p><strong>Stock Level:</strong> <span class="<?php echo $product['stock_level']; ?>"><?php echo ucwords(str_replace('_', ' ', $product['stock_level'])); ?></span></p>
            <p><strong>Condition:</strong> <?php echo ucfirst($product['condition']); ?></p>
            <p><strong>Minimum Stock Level:</strong> <?php echo $product['min_stock_level']; ?></p>
        </div>
        <a href="search_items.php" class="back-btn" style="margin-top:24px;display:inline-block;"><i class="fas fa-arrow-left"></i> Back to Product List</a>
    </div>
    <script src="theme.js"></script>
</body>
</html> 