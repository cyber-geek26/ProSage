<?php 
require "auth/protect.php"; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>ProSage Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="customers/">Customers</a>
    <a href="invoices/">Invoices</a>
    <a href="payments/">Payments</a>
    <a href="reports/statement.php">Statement Of Accounts</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main-content">
    <div class="top-bar">
        <span>Welcome, <?= $_SESSION['username']; ?> 👋</span>
    </div>

    <h1>Dashboard</h1>
    <p>Select a module from the left side to get started.</p>
</div>

</body>
</html>
