<?php
require_once 'config.php';
require_once 'auth_check.php';

checkAuth();

$is_admin = isAdmin();
$user_role = getUserRole();
$user_info = getUserInfo();

// Get user display name
$display_name = isset($_SESSION['full_name']) && !empty($_SESSION['full_name']) 
    ? $_SESSION['full_name'] 
    : $_SESSION['username'];

// Initialize stats array
$stats = array(
    'total_items' => 0,
    'total_transactions' => 0,
    'total_users' => 0
);

// Get basic statistics with error handling
try {
    // Total items
    $sql = "SELECT COUNT(*) as total FROM products";
    $result = $conn->query($sql);
    if ($result) {
        $stats['total_items'] = $result->fetch_assoc()['total'];
    }
    
    // Total transactions
    $sql = "SELECT COUNT(*) as total FROM transactions";
    $result = $conn->query($sql);
    if ($result) {
        $stats['total_transactions'] = $result->fetch_assoc()['total'];
    }
    
    if (hasPermission('manage_users')) {
        // Total users
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $conn->query($sql);
        if ($result) {
            $stats['total_users'] = $result->fetch_assoc()['total'];
        }
    }
} catch (Exception $e) {
    $error = "Error fetching statistics: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <h1><i class="fas fa-boxes"></i> Inventory Management System</h1>
                <div class="user-info">
                    <span class="welcome-text"><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($display_name); ?></span>
                    <span class="user-role"><i class="fas fa-user-tag"></i> <?php echo ucfirst($user_role); ?></span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </header>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                switch($_GET['error']) {
                    case 'invalid_product':
                        echo "Invalid product selected.";
                        break;
                    case 'product_not_found':
                        echo "Product not found.";
                        break;
                    default:
                        echo "An error occurred.";
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                switch($_GET['success']) {
                    case '1':
                        echo "Product added successfully.";
                        break;
                    case '2':
                        echo "Product updated successfully.";
                        break;
                    case '3':
                        echo "Product deleted successfully.";
                        break;
                    default:
                        echo "Operation completed successfully.";
                }
                ?>
            </div>
        <?php endif; ?>

        <nav class="main-nav">
            <ul class="nav-menu">
                <?php if (hasPermission('search_items')): ?>
                <li><a href="search_items.php" class="nav-link"><i class="fas fa-search"></i> Search Items</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('view_transactions')): ?>
                <li><a href="view_transactions.php" class="nav-link"><i class="fas fa-history"></i> View Transactions</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_items')): ?>
                <li class="nav-group">
                    <span class="nav-group-title"><i class="fas fa-box"></i> Product Management</span>
                    <ul class="nav-submenu">
                        <li><a href="add_product.php" class="nav-link"><i class="fas fa-plus"></i> Add Product</a></li>
                        <li><a href="edit_product.php" class="nav-link"><i class="fas fa-edit"></i> Edit Product</a></li>
                        <li><a href="delete_product.php" class="nav-link"><i class="fas fa-trash"></i> Delete Product</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_colleges')): ?>
                <li><a href="register_college.php" class="nav-link"><i class="fas fa-university"></i> Register College</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_departments')): ?>
                <li><a href="register_department.php" class="nav-link"><i class="fas fa-building"></i> Register Department</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_offices')): ?>
                <li><a href="register_office.php" class="nav-link"><i class="fas fa-door-open"></i> Register Office</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_suppliers')): ?>
                <li><a href="register_supplier.php" class="nav-link"><i class="fas fa-truck"></i> Register Supplier</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_items')): ?>
                <li><a href="register_item.php" class="nav-link"><i class="fas fa-box-open"></i> Register Item</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main class="dashboard">
            <h2 class="dashboard-title"><i class="fas fa-chart-line"></i> Dashboard Overview</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-stats">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Items</h3>
                        <p class="stat-number"><?php echo $stats['total_items']; ?></p>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Transactions</h3>
                        <p class="stat-number"><?php echo $stats['total_transactions']; ?></p>
                    </div>
                </div>
                
                <?php if (hasPermission('manage_users')): ?>
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Users</h3>
                        <p class="stat-number"><?php echo $stats['total_users']; ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 