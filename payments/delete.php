<?php
require "../auth/protect.php";
require "../db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM payments WHERE id=$id");

header("Location: index.php");
exit;
?>
