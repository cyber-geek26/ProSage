<?php
require "../auth/protect.php";
require "../db.php";

// Check if 'id' exists in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // redirect back to customer list if no ID is provided
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']); // ensure it's a number to prevent SQL injection

// Get customer data
$result = $conn->query("SELECT * FROM customers WHERE id = $id");

if (!$result || $result->num_rows == 0) {
    // no customer found, redirect back
    header("Location: index.php");
    exit;
}

$customer = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company = $_POST['company_name'];
    $person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $vat = $_POST['vat_number'];

    $stmt = $conn->prepare("UPDATE customers SET company_name=?, contact_person=?, phone=?, email=?, address=?, vat_number=? WHERE id=?");
    $stmt->bind_param("ssssssi", $company, $person, $phone, $email, $address, $vat, $id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php">Customers</a>
    <a href="../reports/statement.php">Statement Of Accounts</a>

    <div class="logout-container">
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</div>

<div class="main-content">
    <h1 class="page-title">Edit Customer</h1>

    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= $customer['company_name']; ?>" required>
            </div>

            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact_person" value="<?= $customer['contact_person']; ?>">
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= $customer['phone']; ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= $customer['email']; ?>">
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address"><?= $customer['address']; ?></textarea>
            </div>

            <div class="form-group">
                <label>VAT Number</label>
                <input type="text" name="vat_number" value="<?= $customer['vat_number']; ?>">
            </div>

            <button class="btn" type="submit">Update Customer</button>
        </form>
    </div>

</div>

</body>
</html>
