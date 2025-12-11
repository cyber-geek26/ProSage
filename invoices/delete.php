<?php
require "../auth/protect.php";
require "../db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Delete invoice items first (optional if foreign key with CASCADE exists)
$conn->query("DELETE FROM invoice_items WHERE invoice_id=$id");

// Delete invoice
$conn->query("DELETE FROM invoices WHERE id=$id");

header("Location: index.php");
exit;
?>
