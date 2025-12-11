<?php
require "../auth/protect.php";
require "../db.php";

// Get unpaid invoices for dropdown
$invoices = $conn->query("
    SELECT i.id, i.invoice_number, c.company_name, i.total_amount,
           COALESCE(SUM(p.amount),0) AS paid
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    LEFT JOIN payments p ON i.id = p.invoice_id
    GROUP BY i.id
    HAVING (i.total_amount - paid) > 0
    ORDER BY i.date_created DESC
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $invoice_id = $_POST['invoice_id'];
    $payment_date = $_POST['payment_date'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];
    $notes = $_POST['notes'];

    // INSERT PAYMENT
    $stmt = $conn->prepare("INSERT INTO payments (invoice_id, payment_date, amount, method, notes)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $invoice_id, $payment_date, $amount, $method, $notes);

    if ($stmt->execute()) {

        // --------------------------------------------------------------
        // 🔥 AUTO UPDATE INVOICE STATUS AFTER PAYMENT
        // --------------------------------------------------------------

        // 1. Get total paid for this invoice
        $sumQuery = $conn->query("SELECT SUM(amount) AS paid FROM payments WHERE invoice_id = $invoice_id");
        $paid = $sumQuery->fetch_assoc()['paid'];

        // 2. Get total invoice amount
        $invQuery = $conn->query("SELECT total_amount FROM invoices WHERE id = $invoice_id");
        $total = $invQuery->fetch_assoc()['total_amount'];

        // 3. Determine status
        if ($paid >= $total) {
            $newStatus = "Paid";
        } elseif ($paid > 0) {
            $newStatus = "Partially Paid";
        } else {
            $newStatus = "Pending";
        }

        // 4. Update invoice status in DB
        $conn->query("UPDATE invoices SET status = '$newStatus' WHERE id = $invoice_id");

        // --------------------------------------------------------------

        header("Location: ../invoices/index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Record Payment</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="../invoices/index.php">Invoices</a>
    <a href="../reports/statement.php">Statement Of Accounts</a>
    <a href="../logout.php" class="logout">Logout</a>
</div>

<div class="main-content">
    <h1 class="page-title">Record Payment</h1>

    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label>Select Invoice</label>
                <select name="invoice_id" required>
                    <option value="">-- Select Invoice --</option>
                    <?php while($row = $invoices->fetch_assoc()): ?>
                        <?php $balance = $row['total_amount'] - $row['paid']; ?>
                        <option value="<?= $row['id']; ?>">
                            <?= $row['invoice_number'] ?> - <?= $row['company_name'] ?> 
                            (Balance: R <?= number_format($balance,2); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Payment Date</label>
                <input type="date" name="payment_date" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label>Amount (R)</label>
                <input type="number" step="0.01" name="amount" required>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <input type="text" name="method">
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes"></textarea>
            </div>

            <button type="submit" class="btn">Save Payment</button>
        </form>
    </div>
</div>

</body>
</html>
