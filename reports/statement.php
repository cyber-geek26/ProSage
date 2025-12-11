<?php
require "../auth/protect.php";
require "../db.php";

$customer_id = $_GET['customer_id'] ?? 0;

// Fetch customers for dropdown
$customers = $conn->query("SELECT id, company_name FROM customers ORDER BY company_name ASC");

// If customer selected, load data
$customer = null;
$transactions = [];

if ($customer_id) {

    // Get customer details
    $customer = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();

    // Correct SQL using date_created and joining payments properly
    $sql = "
        (
            SELECT 
                invoice_number AS ref,
                date_created AS tdate,
                total_amount AS debit,
                0 AS credit,
                'Invoice' AS type
            FROM invoices
            WHERE customer_id = $customer_id
        )

        UNION ALL

        (
            SELECT 
                CONCAT('PAY-', payments.id) AS ref,
                payment_date AS tdate,
                0 AS debit,
                payments.amount AS credit,
                'Payment' AS type
            FROM payments
            INNER JOIN invoices 
                ON payments.invoice_id = invoices.id
            WHERE invoices.customer_id = $customer_id
        )

        ORDER BY tdate ASC
    ";

    $transactions = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Statement of Account</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="../customers/index.php">Customers</a>
    <a href="../invoices/index.php">Invoices</a>
    <a href="../payments/index.php">Payments</a>
    <a href="statement.php" class="active">Statement of Account</a>
    <a href="../logout.php" class="logout">Logout</a>
</div>

<div class="main-content">

    <h1>Statement of Account</h1>

    <!-- Customer selection -->
    <form method="GET" class="form-card">
        <label>Select Customer</label>
        <select name="customer_id" required>
            <option value="">-- Select --</option>
            <?php while ($c = $customers->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $customer_id == $c['id'] ? 'selected' : '' ?>>
                    <?= $c['company_name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button class="btn">Load Statement</button>

    </form><a href="statement_pdf.php?customer_id=<?= $customer_id ?>" class="btn" style="margin-bottom:15px;">
    Download PDF
</a>


    <?php if ($customer): ?>

        <div class="card">
            <h2><?= $customer['company_name'] ?></h2>
            <p><strong>Contact:</strong> <?= $customer['contact_person'] ?></p>
            <p><strong>Phone:</strong> <?= $customer['phone'] ?></p>
            <p><strong>Email:</strong> <?= $customer['email'] ?></p>
        </div>

        <table class="table">
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Debit (R)</th>
                <th>Credit (R)</th>
                <th>Balance (R)</th>
            </tr>

            <?php
                $balance = 0;

                while ($t = $transactions->fetch_assoc()):
                    $balance += ($t['debit'] - $t['credit']);
            ?>
                <tr>
                    <td><?= $t['tdate'] ?></td>
                    <td><?= $t['ref'] ?></td>
                    <td><?= $t['type'] ?></td>
                    <td><?= $t['debit'] ? number_format($t['debit'], 2) : '-' ?></td>
                    <td><?= $t['credit'] ? number_format($t['credit'], 2) : '-' ?></td>
                    <td><strong><?= number_format($balance, 2) ?></strong></td>
                </tr>
            <?php endwhile; ?>

        </table>

        <div class="total-box">
            <h2>Total Outstanding: R <?= number_format($balance, 2) ?></h2>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
