<?php
require "../auth/protect.php";
require "../db.php";

// Get all payments with invoice and customer info
$query = "
    SELECT p.*, i.invoice_number, c.company_name
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN customers c ON i.customer_id = c.id
    ORDER BY p.payment_date DESC
";
$payments = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payments</title>
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
    <h1 class="page-title">Payments</h1>

    <a href="add.php" class="btn">+ Record Payment</a>
     
    <br><br>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Payment Date</th>
                    <th>Amount (R)</th>
                    <th>Method</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($payments->num_rows > 0): ?>
                    <?php while($row = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['invoice_number']); ?></td>
                        <td><?= htmlspecialchars($row['company_name']); ?></td>
                        <td><?= htmlspecialchars($row['payment_date']); ?></td>
                        <td>R <?= number_format($row['amount'],2); ?></td>
                        <td><?= htmlspecialchars($row['method']); ?></td>
                        <td><?= htmlspecialchars($row['notes']); ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this payment?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
