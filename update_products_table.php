<?php
require_once 'config.php';

// Add new columns if they don't exist
$sql = "ALTER TABLE products 
        ADD COLUMN IF NOT EXISTS `condition` ENUM('new', 'used', 'refurbished', 'damaged') NOT NULL DEFAULT 'new',
        ADD COLUMN IF NOT EXISTS stock_level ENUM('in_stock', 'low_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock',
        ADD COLUMN IF NOT EXISTS min_stock_level INT(6) NOT NULL DEFAULT 10";

if ($conn->query($sql) === TRUE) {
    echo "Table updated successfully<br>";
} else {
    echo "Error updating table: " . $conn->error . "<br>";
}

// Update existing products with default values
$sql = "UPDATE products SET 
        `condition` = 'new',
        stock_level = CASE 
            WHEN quantity <= 0 THEN 'out_of_stock'
            WHEN quantity <= 10 THEN 'low_stock'
            ELSE 'in_stock'
        END,
        min_stock_level = 10
        WHERE `condition` IS NULL OR stock_level IS NULL OR min_stock_level IS NULL";

if ($conn->query($sql) === TRUE) {
    echo "Existing products updated successfully<br>";
} else {
    echo "Error updating products: " . $conn->error . "<br>";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Products Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Products Table Update Complete</h1>
    <p>You can now <a href="index.php">return to the inventory</a>.</p>
</body>
</html> 