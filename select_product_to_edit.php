<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

$products_result = $conn->query("SELECT * FROM products ORDER BY name");
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
    <title>Select Product to Edit - Inventory Management System</title>
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
            <h2><i class="fas fa-edit"></i> Select Product to Edit</h2>
            <div class="user-info">
                <i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Stock Level</th>
                    <th>Condition</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <td><?php echo $product['quantity']; ?></td>
                    <td><?php echo isset($product['price']) ? number_format($product['price'], 2) : '-'; ?></td>
                    <td class="<?php echo $product['stock_level']; ?>">
                        <?php 
                        echo ucwords(str_replace('_', ' ', $product['stock_level']));
                        if ($product['stock_level'] == 'low_stock') {
                            echo " (Min: " . $product['min_stock_level'] . ")";
                        }
                        ?>
                    </td>
                    <td><?php echo ucfirst($product['condition']); ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="button" title="Edit Product" style="display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-edit"></i> Edit</a>
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="button" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete Product" style="display:inline-flex;align-items:center;gap:6px;color:#c62828;"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="back-btn" style="margin-top:24px;display:inline-block;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
    <script src="theme.js"></script>
</body>
</html> 