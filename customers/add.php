<?php
require "../auth/protect.php";
require "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company = $_POST['company_name'];
    $person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $vat = $_POST['vat_number'];

    $stmt = $conn->prepare("INSERT INTO customers (company_name, contact_person, phone, email, address, vat_number) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $company, $person, $phone, $email, $address, $vat);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Customer</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php">Customers</a>
    <a href="../reports/statement.php">Statement Of Accounts</a>
    <a href="../logout.php" class="logout">Logout</a>
    
</div>

<div class="main-content">
    <h1 class="page-title">Add New Customer</h1>

    <div class="form-container">
        <form method="POST">

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" required>
            </div>

            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact_person">
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address"></textarea>
            </div>

            <div class="form-group">
                <label>VAT Number</label>
                <input type="text" name="vat_number">
            </div>

            <button class="btn" type="submit">Save Customer</button>

        </form>
    </div>

</div>

</body>
</html>
