<?php
session_start();
require_once 'includes/config.php';

if(empty($_SESSION['id'])) {
    header('location: login.php');
    exit;
}

function generatePayslip($payment_id, $db) {
    $query = "SELECT p.*, i.invoice_number, i.subtotal, i.tax, i.total, c.name as customer_name, c.email
              FROM payments p
              LEFT JOIN invoices i ON i.invoice = p.invoice
              LEFT JOIN customers c ON c.invoice = i.invoice
              WHERE p.id = {$payment_id}";
    
    $result = $db->query($query);
    $payment = $result->fetch_assoc();
    
    if(!$payment) return false;
    
    // Create CSV content
    $csv = "PAYMENT SLIP\n";
    $csv .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    $csv .= "Payment Details\n";
    $csv .= "Payment ID," . $payment['id'] . "\n";
    $csv .= "Invoice Number," . $payment['invoice'] . "\n";
    $csv .= "Invoice Date," . $payment['invoice_date'] . "\n";
    $csv .= "Due Date," . $payment['due_date'] . "\n\n";
    
    $csv .= "Customer Information\n";
    $csv .= "Customer Name," . $payment['customer_name'] . "\n";
    $csv .= "Email," . $payment['email'] . "\n\n";
    
    $csv .= "Invoice Amount Details\n";
    $csv .= "Subtotal," . CURRENCY . number_format($payment['subtotal'], 2) . "\n";
    $csv .= "Tax," . CURRENCY . number_format($payment['tax'], 2) . "\n";
    $csv .= "Invoice Total," . CURRENCY . number_format($payment['total'], 2) . "\n\n";
    
    $csv .= "Payment Information\n";
    $csv .= "Amount Paid," . CURRENCY . number_format($payment['amount_paid'], 2) . "\n";
    $csv .= "Payment Date," . $payment['payment_date'] . "\n";
    $csv .= "Payment Method," . $payment['payment_method'] . "\n";
    $csv .= "Reference Number," . $payment['reference_number'] . "\n";
    $csv .= "Status," . $payment['status'] . "\n\n";
    
    if($payment['amount_paid'] < $payment['total']) {
        $csv .= "Balance Due," . CURRENCY . number_format($payment['total'] - $payment['amount_paid'], 2) . "\n";
    }
    
    $csv .= "\nNotes\n";
    $csv .= $payment['notes'] . "\n";
    
    return $csv;
}

// Handle download
if(isset($_GET['id'])) {
    $payment_id = intval($_GET['id']);
    $payslip = generatePayslip($payment_id, $db);
    
    if($payslip) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="payslip_'.$payment_id.'_'.date('Y-m-d').'.csv"');
        echo $payslip;
    } else {
        header('Location: payments-list.php');
    }
} 
elseif(isset($_GET['all'])) {
    // Download all payslips as ZIP
    $query = "SELECT id FROM payments ORDER BY payment_date DESC";
    $result = $db->query($query);
    
    $zip_file = 'payslips_' . date('Y-m-d_His') . '.zip';
    $zip_path = 'downloads/' . $zip_file;
    
    $zip = new ZipArchive();
    if($zip->open($zip_path, ZipArchive::CREATE) !== true) {
        header('Location: payments-list.php');
        exit;
    }
    
    while($row = $result->fetch_assoc()) {
        $payslip = generatePayslip($row['id'], $db);
        if($payslip) {
            $zip->addFromString('payslip_'.$row['id'].'.csv', $payslip);
        }
    }
    
    $zip->close();
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$zip_file.'"');
    header('Content-Length: ' . filesize($zip_path));
    readfile($zip_path);
    unlink($zip_path);
} else {
    header('Location: payments-list.php');
}
?>
