<?php
require_once 'config.php';
require_once 'auth_check.php';
checkAdmin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=3");
    } else {
        header("Location: index.php?error=1");
    }
    
    $stmt->close();
} else {
    header("Location: index.php");
}

$conn->close();
?> 