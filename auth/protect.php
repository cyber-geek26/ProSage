<?php
session_start();

// If not logged in → redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
