<?php
$host = "localhost";
$user = "root";     // change if needed
$pass = "";         // MAMP/XAMPP users leave blank
$db   = "prosage";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
