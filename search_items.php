<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAuth();

// Get all categories and suppliers for filters
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name");

// Build search query
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where_conditions[] = "(i.name LIKE ? OR i.description LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
    $where_conditions[] = "i.category_id = ?";
    $params[] = $_GET['category_id'];
    $types .= "i";
}

if (isset($_GET['supplier_id']) && $_GET['supplier_id'] != '') {
    $where_conditions[] = "i.supplier_id = ?";
    $params[] = $_GET['supplier_id'];
    $types .= "i";
}

if (isset($_GET['stock_level']) && $_GET['stock_level'] != '') {
    $where_conditions[] = "i.stock_level = ?";
    $params[] = $_GET['stock_level'];
    $types .= "s";
}

if (isset($_GET['condition']) && $_GET['condition'] != '') {
    $where_conditions[] = "i.`condition` = ?";
    $params[] = $_GET['condition'];
    $types .= "s";
}

// Base query
$query = "SELECT i.*, c.name as category_name, s.name as supplier_name 
          FROM items i 
          LEFT JOIN categories c ON i.category_id = c.id 
          LEFT JOIN suppliers s ON i.supplier_id = s.id";

// Add where conditions if any
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY i.name";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

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
    <title>Search Items - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2>Search Items</h2>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <form method="GET" action="" class="filter-form">
            <input type="text" name="search" placeholder="Search by name or description" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <select name="category_id">
                <option value="">All Categories</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="supplier_id">
                <option value="">All Suppliers</option>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo $supplier['id']; ?>" <?php echo (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($supplier['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="stock_level">
                <option value="">All Stock Levels</option>
                <option value="in_stock" <?php echo (isset($_GET['stock_level']) && $_GET['stock_level'] == 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                <option value="low_stock" <?php echo (isset($_GET['stock_level']) && $_GET['stock_level'] == 'low_stock') ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out_of_stock" <?php echo (isset($_GET['stock_level']) && $_GET['stock_level'] == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
            <select name="condition">
                <option value="">All Conditions</option>
                <option value="new" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'new') ? 'selected' : ''; ?>>New</option>
                <option value="used" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'used') ? 'selected' : ''; ?>>Used</option>
                <option value="refurbished" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'refurbished') ? 'selected' : ''; ?>>Refurbished</option>
                <option value="damaged" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'damaged') ? 'selected' : ''; ?>>Damaged</option>
            </select>
            <button type="submit">Search</button>
            <a href="search_items.php" class="button clear-link">Clear Filters</a>
        </form>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Stock Level</th>
                    <th>Condition</th>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'store_keeper'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['supplier_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="<?php echo $item['stock_level']; ?>">
                        <?php 
                        echo ucwords(str_replace('_', ' ', $item['stock_level']));
                        if ($item['stock_level'] == 'low_stock') {
                            echo " (Min: " . $item['min_stock_level'] . ")";
                        }
                        ?>
                    </td>
                    <td><?php echo ucfirst($item['condition']); ?></td>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'store_keeper'): ?>
                    <td>
                        <a href="edit_product.php?id=<?php echo $item['id']; ?>" class="button">Edit</a>
                        <a href="delete_product.php?id=<?php echo $item['id']; ?>" class="button" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html> 