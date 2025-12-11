<?php
ob_clean();
ob_start();

require "../auth/protect.php";
require "../db.php";
require "../vendor/autoload.php";

use Dompdf\Dompdf;

if (!isset($_GET['id'])) {
    die("No invoice selected.");
}

$invoice_id = intval($_GET['id']);

// Fetch invoice
$invoice = $conn->query("SELECT * FROM invoices WHERE id=$invoice_id")->fetch_assoc();
$customer_id = $invoice['customer_id'];

// Fetch customer
$customer = $conn->query("SELECT * FROM customers WHERE id=$customer_id")->fetch_assoc();

// Fetch invoice items
$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id=$invoice_id");

// Calculate totals
$subtotal = 0;
while ($row = $items->fetch_assoc()) {
    $subtotal += $row['quantity'] * $row['unit_price'];
}
$items->data_seek(0);

$vat = $subtotal * 0.15;
$total = $subtotal + $vat;

// Build PDF
$html = "
<style>
body { font-family: Arial, sans-serif; font-size: 13px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #555; padding: 6px; }
h2 { margin-bottom: 5px; }
</style>

<h2>Invoice #{$invoice['invoice_number']}</h2>
<p><strong>Date:</strong> {$invoice['date_created']}</p>

<h3>Bill To:</h3>
<p>
<strong>{$customer['company_name']}</strong><br>
{$customer['address']}<br>
{$customer['email']}<br>
{$customer['phone']}<br>
VAT No: {$customer['vat_number']}<br>
</p>

<h3>Items</h3>

<table>
<tr>
    <th>Description</th>
    <th>Qty</th>
    <th>Unit Price (R)</th>
    <th>Total (R)</th>
</tr>";

while ($item = $items->fetch_assoc()) {
    $line_total = $item['quantity'] * $item['unit_price'];
    $html .= "
    <tr>
        <td>{$item['description']}</td>
        <td>{$item['quantity']}</td>
        <td>" . number_format($item['unit_price'], 2) . "</td>
        <td>" . number_format($line_total, 2) . "</td>
    </tr>";
}

$html .= "</table>

<h3>Totals</h3>
<table>
    <tr><th>Subtotal</th><td>R " . number_format($subtotal, 2) . "</td></tr>
    <tr><th>VAT (15%)</th><td>R " . number_format($vat, 2) . "</td></tr>
    <tr><th>Total</th><td><strong>R " . number_format($total, 2) . "</strong></td></tr>
</table>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

ob_end_clean();
$dompdf->stream("Invoice_{$invoice['invoice_number']}.pdf", ["Attachment" => true]);
exit;
