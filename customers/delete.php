<?php
require "../auth/protect.php";
require "../db.php";

// Check if 'id' exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']); // Make sure it's a number

// Delete customer
$stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: index.php");
    exit;
} else {
    echo "Error deleting customer.";
}
?>
