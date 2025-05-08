<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAuth();

// Get all items and offices for filters
$items = $conn->query("SELECT * FROM items ORDER BY name");
$offices = $conn->query("SELECT o.*, d.name as department_name, c.name as college_name 
                        FROM offices o 
                        JOIN departments d ON o.department_id = d.id 
                        JOIN colleges c ON d.college_id = c.id 
                        ORDER BY c.name, d.name, o.name");

// Build search query
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['item_id']) && $_GET['item_id'] != '') {
    $where_conditions[] = "t.item_id = ?";
    $params[] = $_GET['item_id'];
    $types .= "i";
}

if (isset($_GET['office_id']) && $_GET['office_id'] != '') {
    $where_conditions[] = "t.office_id = ?";
    $params[] = $_GET['office_id'];
    $types .= "i";
}

if (isset($_GET['type']) && $_GET['type'] != '') {
    $where_conditions[] = "t.type = ?";
    $params[] = $_GET['type'];
    $types .= "s";
}

if (isset($_GET['date_from']) && $_GET['date_from'] != '') {
    $where_conditions[] = "DATE(t.created_at) >= ?";
    $params[] = $_GET['date_from'];
    $types .= "s";
}

if (isset($_GET['date_to']) && $_GET['date_to'] != '') {
    $where_conditions[] = "DATE(t.created_at) <= ?";
    $params[] = $_GET['date_to'];
    $types .= "s";
}

// Base query
$query = "SELECT t.*, i.name as item_name, o.name as office_name, 
          d.name as department_name, c.name as college_name,
          u.username as created_by_name
          FROM transactions t
          LEFT JOIN items i ON t.item_id = i.id
          LEFT JOIN offices o ON t.office_id = o.id
          LEFT JOIN departments d ON o.department_id = d.id
          LEFT JOIN colleges c ON d.college_id = c.id
          LEFT JOIN users u ON t.created_by = u.id";

// Add where conditions if any
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY t.created_at DESC";

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
    <title>View Transactions - Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="transactions-container">
        <div class="transactions-header">
            <h2>View Transactions</h2>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($display_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <form method="GET" action="" class="filter-form">
            <select name="item_id">
                <option value="">All Items</option>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <option value="<?php echo $item['id']; ?>" <?php echo (isset($_GET['item_id']) && $_GET['item_id'] == $item['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="office_id">
                <option value="">All Offices</option>
                <?php while ($office = $offices->fetch_assoc()): ?>
                    <option value="<?php echo $office['id']; ?>" <?php echo (isset($_GET['office_id']) && $_GET['office_id'] == $office['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($office['college_name'] . ' - ' . $office['department_name'] . ' - ' . $office['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="type">
                <option value="">All Types</option>
                <option value="received" <?php echo (isset($_GET['type']) && $_GET['type'] == 'received') ? 'selected' : ''; ?>>Received</option>
                <option value="withdrawn" <?php echo (isset($_GET['type']) && $_GET['type'] == 'withdrawn') ? 'selected' : ''; ?>>Withdrawn</option>
            </select>

            <input type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>" placeholder="From Date">
            <input type="date" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>" placeholder="To Date">

            <button type="submit">Filter</button>
            <a href="view_transactions.php" class="button clear-link">Clear Filters</a>
        </form>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Office</th>
                    <th>Notes</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($transaction = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($transaction['item_name']); ?></td>
                    <td class="<?php echo $transaction['type'] == 'received' ? 'success' : 'error'; ?>">
                        <?php echo ucfirst($transaction['type']); ?>
                    </td>
                    <td><?php echo $transaction['quantity']; ?></td>
                    <td>
                        <?php 
                        echo htmlspecialchars($transaction['college_name'] . ' - ' . 
                                            $transaction['department_name'] . ' - ' . 
                                            $transaction['office_name']); 
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($transaction['notes']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['created_by_name']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html> 