<?php
require "../auth/protect.php";
require "../db.php";
require "../vendor/autoload.php";

use Dompdf\Dompdf;

$customer_id = $_GET['customer_id'] ?? 0;

if (!$customer_id) {
    die("No customer selected.");
}

// Fetch customer
$customer = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();

// Fetch ledger (same logic as statement.php)
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

$result = $conn->query($sql);

// Build HTML
$html = "
<style>
body { font-family: Arial, sans-serif; font-size: 13px; }
h2, h3 { margin-bottom: 5px; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { border: 1px solid #444; padding: 6px; }
th { background: #f0f0f0; }
</style>

<h2>Statement of Account</h2>

<h3>{$customer['company_name']}</h3>
<p>
<strong>Contact:</strong> {$customer['contact_person']}<br>
<strong>Email:</strong> {$customer['email']}<br>
<strong>Phone:</strong> {$customer['phone']}<br>
</p>

<table>
<tr>
    <th>Date</th>
    <th>Reference</th>
    <th>Description</th>
    <th>Debit (R)</th>
    <th>Credit (R)</th>
    <th>Balance (R)</th>
</tr>
";

$balance = 0;

while ($row = $result->fetch_assoc()) {

    $debit = $row['debit'] ? number_format($row['debit'], 2) : "-";
    $credit = $row['credit'] ? number_format($row['credit'], 2) : "-";

    $balance += ($row['debit'] - $row['credit']);

    $html .= "
    <tr>
        <td>{$row['tdate']}</td>
        <td>{$row['ref']}</td>
        <td>{$row['type']}</td>
        <td>$debit</td>
        <td>$credit</td>
        <td><strong>" . number_format($balance, 2) . "</strong></td>
    </tr>";
}

$html .= "
</table>

<h3>Total Outstanding: R " . number_format($balance, 2) . "</h3>
";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

$dompdf->stream("Statement_{$customer['company_name']}.pdf", ["Attachment" => true]);
