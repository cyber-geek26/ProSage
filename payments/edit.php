<?php
require "../auth/protect.php";
require "../db.php";

// Check if payment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch payment info
$stmt = $conn->prepare("
    SELECT p.*, i.invoice_number, c.company_name
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN customers c ON i.customer_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    header("Location: index.php");
    exit;
}

// Update payment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_date = $_POST['payment_date'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];
    $notes = $_POST['notes'];

    $stmtUpdate = $conn->prepare("
        UPDATE payments 
        SET payment_date=?, amount=?, method=?, notes=?
        WHERE id=?
    ");
    $stmtUpdate->bind_param("sdssi", $payment_date, $amount, $method, $notes, $id);
    if ($stmtUpdate->execute()) {
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Payment</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="../invoices/index.php">Invoices</a>
    <a href="index.php">Payments</a>
    <a href="../reports/statement.php">Statement Of Accounts</a>
    <a href="../logout.php" class="logout">Logout</a>
    
</div>

<div class="main-content">
    <h1 class="page-title">Edit Payment</h1>

    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label>Invoice</label>
                <input type="text" value="<?= htmlspecialchars($payment['invoice_number']) . ' - ' . htmlspecialchars($payment['company_name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Payment Date</label>
                <input type="date" name="payment_date" value="<?= htmlspecialchars($payment['payment_date']); ?>" required>
            </div>

            <div class="form-group">
                <label>Amount (R)</label>
                <input type="number" step="0.01" name="amount" value="<?= number_format($payment['amount'],2,'.',''); ?>" required>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <input type="text" name="method" value="<?= htmlspecialchars($payment['method']); ?>">
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes"><?= htmlspecialchars($payment['notes']); ?></textarea>
            </div>

            <button type="submit" class="btn">Update Payment</button>
        </form>
    </div>
</div>

</body>
</html>
