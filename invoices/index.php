<?php
require "../auth/protect.php";
require "../db.php";

// --- FILTER LOGIC ---
$where = "";
if (isset($_GET['customer_id']) && $_GET['customer_id'] != "") {
    $customer_id = intval($_GET['customer_id']);
    $where = "WHERE i.customer_id = $customer_id";
}

// --- GET ALL CUSTOMERS FOR FILTER DROPDOWN ---
$customers = $conn->query("SELECT id, company_name FROM customers ORDER BY company_name ASC");

// --- GET INVOICES (+ FILTER IF SELECTED) ---
$query = "
    SELECT i.*, c.company_name 
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    $where
    ORDER BY i.date_created DESC
";
$invoices = $conn->query($query);

// --- UPDATE OVERDUE STATUS ---
$today = date('Y-m-d');
$conn->query("UPDATE invoices SET status='Overdue' WHERE status='Pending' AND due_date < '$today'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoices</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php" class="active">Invoices</a>
    <a href="../payments/index.php">Payments</a>
    <a href="../reports/statement.php">Statement Of Accounts</a>
    <a href="../logout.php" class="logout">Logout</a>
</div>

<div class="main-content">
    <h1 class="page-title">Invoices</h1>

    <a href="add.php" class="btn">+ Create Invoice</a>
    <br><br>

    <!-- FILTER BAR -->
    <form method="GET" style="margin-bottom:20px;">
        <label><strong>Filter by Company:</strong></label>
        <select name="customer_id" onchange="this.form.submit()">
            <option value="">-- All Companies --</option>

            <?php while ($c = $customers->fetch_assoc()): ?>
                <option value="<?= $c['id']; ?>"
                    <?= (isset($_GET['customer_id']) && $_GET['customer_id'] == $c['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($c['company_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Date Created</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($invoices->num_rows > 0): ?>
                    <?php while ($row = $invoices->fetch_assoc()): ?>
                        <tr <?php if ($row['status'] == 'Overdue') echo 'style="background-color:#ffe6e6;"'; ?>>
                            <td><?= htmlspecialchars($row['invoice_number']); ?></td>
                            <td><?= htmlspecialchars($row['company_name']); ?></td>
                            <td><?= htmlspecialchars($row['date_created']); ?></td>
                            <td><?= htmlspecialchars($row['due_date']); ?></td>
                            <td>R <?= number_format($row['total_amount'], 2); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td>
                                <a href="view.php?id=<?= $row['id']; ?>" class="btn btn-edit">View</a>
                                <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger"
                                   onclick="return confirm('Delete this invoice?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No invoices found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
