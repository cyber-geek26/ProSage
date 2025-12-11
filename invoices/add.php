<?php
require "../auth/protect.php";
require "../db.php";

// Get customers for dropdown
$customers = $conn->query("SELECT * FROM customers ORDER BY company_name ASC");

// Auto-generate invoice number
$lastInvoice = $conn->query("SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
$last = $lastInvoice->fetch_assoc();
if ($last) {
    $num = intval(substr($last['invoice_number'],4)) + 1;
} else {
    $num = 1;
}
$invoice_number = "INV-" . str_pad($num, 4, "0", STR_PAD_LEFT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $date_created = $_POST['date_created'];
    $due_date = $_POST['due_date'];
    $notes = $_POST['notes'];

    $subtotal = 0;
    $total_amount = 0;
    $descriptions = $_POST['description'];
    $qtys = $_POST['quantity'];
    $prices = $_POST['unit_price'];

    // Insert invoice first with 0 total (will update later)
    $stmt = $conn->prepare("INSERT INTO invoices (customer_id, invoice_number, date_created, due_date, total_amount, notes)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $dummy_total = 0;
    $stmt->bind_param("isssds", $customer_id, $invoice_number, $date_created, $due_date, $dummy_total, $notes);
    $stmt->execute();
    $invoice_id = $conn->insert_id;

    // Insert items with VAT
    foreach($descriptions as $key => $desc) {
        $line_total = $qtys[$key] * $prices[$key];        // line total without VAT
        $line_vat = $line_total * 0.15;                   // 15% VAT
        $line_total_incl_vat = $line_total + $line_vat;   // total including VAT
        $subtotal += $line_total;
        $total_amount += $line_total_incl_vat;

        $stmtItem = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total)
                                    VALUES (?, ?, ?, ?, ?)");
        $stmtItem->bind_param("isidd", $invoice_id, $desc, $qtys[$key], $prices[$key], $line_total_incl_vat);
        $stmtItem->execute();
    }

    // Update invoice total
    $update = $conn->prepare("UPDATE invoices SET total_amount=? WHERE id=?");
    $update->bind_param("di", $total_amount, $invoice_id);
    $update->execute();

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Invoice</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
    function addRow() {
        let container = document.getElementById('items-container');
        let row = document.createElement('div');
        row.classList.add('form-group', 'line-item-row');
        row.innerHTML = `
            <input type="text" name="description[]" placeholder="Description" required>
            <input type="number" name="quantity[]" placeholder="Qty" min="1" required>
            <input type="number" name="unit_price[]" placeholder="Unit Price (R)" step="0.01" required>
            <button type="button" onclick="this.parentElement.remove();" class="btn btn-danger">Remove</button>
        `;
        container.appendChild(row);
    }
    </script>
</head>
<body>

<div class="sidebar">
    <h2>ProSage</h2>
    <a href="../dashboard.php">Dashboard</a>
    <a href="index.php">Invoices</a>
    <a href="../reports/statement.php">Statement Of Accounts</a>
    <a href="../logout.php" class="logout">Logout</a>
  
</div>

<div class="main-content">
    <h1 class="page-title">Create Invoice</h1>

    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" value="<?= $invoice_number; ?>" readonly>
            </div>

            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php while($row = $customers->fetch_assoc()): ?>
                        <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['company_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Date Created</label>
                <input type="date" name="date_created" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" required>
            </div>

            <div id="items-container">
                <h3>Line Items</h3>
                <div class="form-group line-item-row">
                    <input type="text" name="description[]" placeholder="Description" required>
                    <input type="number" name="quantity[]" placeholder="Qty" min="1" required>
                    <input type="number" name="unit_price[]" placeholder="Unit Price (R)" step="0.01" required>
                </div>
            </div>
            <button type="button" class="btn" onclick="addRow()">+ Add Item</button>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes"></textarea>
            </div>

            <button type="submit" class="btn">Save Invoice</button>
        </form>
    </div>
</div>

</body>
</html>
