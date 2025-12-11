<?php
require "../auth/protect.php";
require "../db.php";

$result = $conn->query("SELECT * FROM customers ORDER BY company_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
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
    <h1 class="page-title">Customer List</h1>

    <a href="add.php" class="btn">+ Add Customer</a>
    
    <br><br>


    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>VAT Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['company_name']); ?></td>
                        <td><?= htmlspecialchars($row['contact_person']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['vat_number']); ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger"
                                   onclick="return confirm('Delete this invoice?');">Delete</a>
                            <!-- Delete button can be added later -->
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
