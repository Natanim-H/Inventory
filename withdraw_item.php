<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

// Get all items and offices for dropdowns
$items = $conn->query("SELECT * FROM items WHERE quantity > 0 ORDER BY name");
$offices = $conn->query("SELECT o.*, d.name as department_name, c.name as college_name 
                        FROM offices o 
                        JOIN departments d ON o.department_id = d.id 
                        JOIN colleges c ON d.college_id = c.id 
                        ORDER BY c.name, d.name, o.name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $office_id = $_POST['office_id'];
    $notes = $_POST['notes'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if enough stock is available
        $stmt = $conn->prepare("SELECT quantity FROM items WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_quantity = $result->fetch_assoc()['quantity'];
        
        if ($current_quantity < $quantity) {
            throw new Exception("Not enough stock available. Current stock: " . $current_quantity);
        }
        
        // Insert transaction record
        $stmt = $conn->prepare("INSERT INTO transactions (item_id, type, quantity, office_id, notes, created_by) VALUES (?, 'withdrawn', ?, ?, ?, ?)");
        $stmt->bind_param("iiisi", $item_id, $quantity, $office_id, $notes, $_SESSION['user_id']);
        $stmt->execute();
        
        // Update item quantity and stock level
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
        
        // Update stock level based on new quantity
        $stmt = $conn->prepare("UPDATE items SET stock_level = CASE 
            WHEN quantity <= 0 THEN 'out_of_stock'
            WHEN quantity <= min_stock_level THEN 'low_stock'
            ELSE 'in_stock'
        END WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        
        $conn->commit();
        header("Location: index.php?success=7");
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error withdrawing item: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Item - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Withdraw Item</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 
                (<?php echo $_SESSION['role']; ?>)
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <select name="item_id" required>
                    <option value="">Select Item</option>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <option value="<?php echo $item['id']; ?>">
                            <?php echo htmlspecialchars($item['name']); ?> 
                            (Current Stock: <?php echo $item['quantity']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="number" name="quantity" placeholder="Quantity to Withdraw" required min="1">

                <select name="office_id" required>
                    <option value="">Select Office</option>
                    <?php while ($office = $offices->fetch_assoc()): ?>
                        <option value="<?php echo $office['id']; ?>">
                            <?php echo htmlspecialchars($office['college_name'] . ' - ' . $office['department_name'] . ' - ' . $office['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <textarea name="notes" placeholder="Notes (optional)"></textarea>

                <button type="submit">Withdraw Item</button>
            </form>
            <a href="index.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 