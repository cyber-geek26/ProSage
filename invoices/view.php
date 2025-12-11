<?php
require "../auth/protect.php";
require "../db.php";

// Check ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Get invoice info with customer
$stmt = $conn->prepare("
    SELECT i.*, c.company_name, c.contact_person, c.phone, c.email, c.address, c.vat_number
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    WHERE i.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    header("Location: index.php");
    exit;
}

// Get line items
$stmtItems = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id=?");
$stmtItems->bind_param("i", $id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();

// Calculate subtotal and VAT (since totals stored include VAT)
$subtotal = 0;
foreach($itemsResult as $item) {
    $line_no_vat = $item['total'] / 1.15;  // remove 15% VAT
    $subtotal += $line_no_vat;
}
$total_vat = $subtotal * 0.15;
$total_amount = $subtotal + $total_vat;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice <?= htmlspecialchars($invoice['invoice_number']); ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .invoice-box { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #eee; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .invoice-box table td, th { border: 1px solid #ddd; padding: 8px; }
        .invoice-header { margin-bottom: 20px; }
        .totals { text-align: right; }
    </style>
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
    <div class="invoice-box">
    <a href="invoice_pdf.php?id=<?php echo $id ?>" class="btn">Download PDF</a>


        <div class="invoice-header">
            <h2>Invoice <?= htmlspecialchars($invoice['invoice_number']); ?></h2>
            <p><strong>Date:</strong> <?= htmlspecialchars($invoice['date_created']); ?></p>
            <p><strong>Due:</strong> <?= htmlspecialchars($invoice['due_date']); ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($invoice['status']); ?></p>
        </div>

        <h3>Customer Information</h3>
        <p><?= htmlspecialchars($invoice['company_name']); ?></p>
        <p><?= htmlspecialchars($invoice['contact_person']); ?></p>
        <p><?= htmlspecialchars($invoice['phone']); ?></p>
        <p><?= htmlspecialchars($invoice['email']); ?></p>
        <p><?= htmlspecialchars($invoice['address']); ?></p>
        <p><strong>VAT Number:</strong> <?= htmlspecialchars($invoice['vat_number']); ?></p>

        <h3>Line Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price (R)</th>
                    <th>Total (R)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset result pointer
                $itemsResult->data_seek(0);
                foreach($itemsResult as $item):
                    $line_no_vat = $item['total'] / 1.15;
                    $line_vat = $line_no_vat * 0.15;
                    $line_total = $line_no_vat + $line_vat;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['description']); ?></td>
                    <td><?= $item['quantity']; ?></td>
                    <td>R <?= number_format($item['unit_price'],2); ?></td>
                    <td>R <?= number_format($line_total,2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <p><strong>Subtotal:</strong> R <?= number_format($subtotal,2); ?></p>
            <p><strong>VAT (15%):</strong> R <?= number_format($total_vat,2); ?></p>
            <p><strong>Total:</strong> R <?= number_format($total_amount,2); ?></p>
        </div>

        <?php if(!empty($invoice['notes'])): ?>
            <h4>Notes</h4>
            <p><?= nl2br(htmlspecialchars($invoice['notes'])); ?></p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
