<?php

/**
 * CloudUko Invoice System - Enhanced Functions
 * Payment tracking, reporting, and reminder functions
 */

include_once('includes/config.php');

// Record payment for an invoice
function recordPayment($invoice_id, $amount, $payment_date, $payment_method = 'Manual', $notes = '') {
    global $mysqli;
    
    // Get current invoice total
    $query = "SELECT total, amount_paid FROM invoices WHERE invoice = '" . $mysqli->real_escape_string($invoice_id) . "'";
    $result = $mysqli->query($query);
    $invoice = $result->fetch_assoc();
    
    if (!$invoice) {
        return array('success' => false, 'message' => 'Invoice not found');
    }
    
    $new_amount_paid = $invoice['amount_paid'] + $amount;
    $new_status = ($new_amount_paid >= $invoice['total']) ? 'paid' : 'open';
    $new_balance = $invoice['total'] - $new_amount_paid;
    
    // Insert payment record
    $payment_query = "INSERT INTO payments (invoice, amount, payment_date, payment_method, notes) 
                      VALUES ('" . $mysqli->real_escape_string($invoice_id) . "', 
                              '" . floatval($amount) . "', 
                              '" . $mysqli->real_escape_string($payment_date) . "', 
                              '" . $mysqli->real_escape_string($payment_method) . "', 
                              '" . $mysqli->real_escape_string($notes) . "')";
    
    // Update invoice
    $update_query = "UPDATE invoices SET amount_paid = '" . floatval($new_amount_paid) . "', 
                     last_payment_date = '" . $mysqli->real_escape_string($payment_date) . "', 
                     status = '" . $new_status . "' 
                     WHERE invoice = '" . $mysqli->real_escape_string($invoice_id) . "'";
    
    if ($mysqli->query($payment_query) && $mysqli->query($update_query)) {
        return array('success' => true, 'message' => 'Payment recorded successfully', 'new_balance' => $new_balance, 'status' => $new_status);
    } else {
        return array('success' => false, 'message' => 'Failed to record payment: ' . $mysqli->error);
    }
}

// Get payment history for an invoice
function getPaymentHistory($invoice_id) {
    global $mysqli;
    
    $query = "SELECT * FROM payments WHERE invoice = '" . $mysqli->real_escape_string($invoice_id) . "' ORDER BY payment_date DESC";
    $result = $mysqli->query($query);
    
    $payments = array();
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    return $payments;
}

// Get overdue invoices
function getOverdueInvoices() {
    global $mysqli;
    
    $today = date('Y-m-d');
    // Use GROUP BY to avoid duplicate rows when multiple customer records share the same invoice
    $query = "SELECT i.*, 
              MIN(c.name)  AS name,
              MIN(c.email) AS email,
              DATEDIFF('" . $today . "', STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y')) as days_overdue,
              (i.total - i.amount_paid) as balance_due
              FROM invoices i
              JOIN customers c ON c.invoice = i.invoice
              WHERE i.status = 'open' 
              AND STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') < '" . $today . "'
              GROUP BY i.invoice
              ORDER BY i.invoice_due_date ASC";
    
    $result = $mysqli->query($query);
    
    $overdue = array();
    while ($row = $result->fetch_assoc()) {
        $overdue[] = $row;
    }
    
    return $overdue;
}

// Get upcoming due invoices (within 7 days)
function getUpcomingDueInvoices() {
    global $mysqli;
    
    $today = date('Y-m-d');
    $week_from_now = date('Y-m-d', strtotime('+7 days'));
    
    $query = "SELECT i.*, c.name, c.email,
              DATEDIFF(STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y'), '" . $today . "') as days_until_due,
              (i.total - i.amount_paid) as balance_due
              FROM invoices i
              JOIN customers c ON c.invoice = i.invoice
              WHERE i.status = 'open'
              AND STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') BETWEEN '" . $today . "' AND '" . $week_from_now . "'
              ORDER BY i.invoice_due_date ASC";
    
    $result = $mysqli->query($query);
    
    $upcoming = array();
    while ($row = $result->fetch_assoc()) {
        $upcoming[] = $row;
    }
    
    return $upcoming;
}

// Get monthly income report
function getMonthlyIncomeReport($year = null, $month = null) {
    global $mysqli;
    
    if ($year === null) $year = date('Y');
    if ($month === null) $month = date('m');
    
    $query = "SELECT 
              SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as total_paid,
              SUM(CASE WHEN status = 'open' THEN total ELSE 0 END) as total_outstanding,
              COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
              COUNT(CASE WHEN status = 'open' THEN 1 END) as outstanding_count,
              COUNT(*) as total_invoices
              FROM invoices
              WHERE MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = " . intval($month) . "
              AND YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = " . intval($year);
    
    $result = $mysqli->query($query);
    return $result->fetch_assoc();
}

// Get customer summary
function getCustomerSummary($customer_id = null) {
    global $mysqli;
    
    $where = '';
    if ($customer_id) {
        $where = "WHERE c.id = " . intval($customer_id);
    }
    
    $query = "SELECT 
              c.id, c.name, c.email,
              COUNT(i.id) as total_invoices,
              SUM(CASE WHEN i.status = 'paid' THEN i.total ELSE 0 END) as total_paid,
              SUM(CASE WHEN i.status = 'open' THEN (i.total - i.amount_paid) ELSE 0 END) as total_outstanding,
              MAX(STR_TO_DATE(i.invoice_date, '%d/%m/%Y')) as last_invoice_date
              FROM customers c
              LEFT JOIN invoices i ON c.invoice = i.invoice
              " . $where . "
              GROUP BY c.id, c.name, c.email
              ORDER BY c.name ASC";
    
    $result = $mysqli->query($query);
    
    $customers = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    
    return $customers;
}

// Send overdue reminder
function sendOverdueReminder($invoice_id, $customer_email) {
    global $mysqli;
    
    // Get invoice details
    $query = "SELECT i.*, c.name FROM invoices i 
              JOIN customers c ON c.invoice = i.invoice 
              WHERE i.invoice = '" . $mysqli->real_escape_string($invoice_id) . "'";
    $result = $mysqli->query($query);
    $invoice = $result->fetch_assoc();
    
    if (!$invoice) {
        return array('status' => 'Error', 'message' => 'Invoice not found');
    }
    
    require_once('class.phpmailer.php');
    
    $mail = new PHPMailer();
    $mail->AddReplyTo(EMAIL_FROM, EMAIL_NAME);
    $mail->SetFrom(EMAIL_FROM, EMAIL_NAME);
    $mail->AddAddress($customer_email, $invoice['name']);
    
    $mail->Subject = "Payment Reminder - Invoice " . $invoice_id . " is Overdue";
    
    $balance_due = $invoice['total'] - $invoice['amount_paid'];
    $due_date = DateTime::createFromFormat('d/m/Y', $invoice['invoice_due_date']);
    $days_overdue = date_diff(new DateTime(), $due_date)->days;
    
    $body = "Dear " . $invoice['name'] . ",\n\n";
    $body .= "This is a friendly reminder that Invoice " . $invoice_id . " dated " . $invoice['invoice_date'] . " is now overdue.\n\n";
    $body .= "Invoice Details:\n";
    $body .= "Amount Due: " . CURRENCY . number_format($balance_due, 2) . "\n";
    $body .= "Due Date: " . $invoice['invoice_due_date'] . "\n";
    $body .= "Days Overdue: " . $days_overdue . "\n\n";
    $body .= "Please settle this invoice at your earliest convenience.\n\n";
    $body .= PAYMENT_DETAILS . "\n\n";
    $body .= "Thank you for your business!\n\n";
    $body .= "Best regards,\n" . COMPANY_NAME;
    
    $mail->MsgHTML($body);
    
    if ($mail->Send()) {
        // Log reminder
        $log_query = "INSERT INTO reminders (invoice_id, sent_to_email, reminder_type, sent_date) 
                  VALUES ('" . $mysqli->real_escape_string($invoice_id) . "', 
                      '" . $mysqli->real_escape_string($customer_email) . "', 
                      'overdue', 
                      NOW())";
        
        // Update last reminder sent
        $update_query = "UPDATE invoices SET last_reminder_sent = '" . date('d/m/Y H:i:s') . "' 
                        WHERE invoice = '" . $mysqli->real_escape_string($invoice_id) . "'";
        
        $mysqli->query($log_query);
        $mysqli->query($update_query);
        
        return array('status' => 'Success', 'message' => 'Reminder sent successfully');
    } else {
        return array('status' => 'Error', 'message' => 'Failed to send reminder: ' . $mail->ErrorInfo);
    }
}

// Get total outstanding balance
function getTotalOutstandingBalance() {
    global $mysqli;
    
    $query = "SELECT SUM(total - amount_paid) as outstanding FROM invoices WHERE status = 'open'";
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    
    return $row['outstanding'] ?? 0;
}

// Get total paid this month
function getTotalPaidThisMonth() {
    global $mysqli;
    
    $query = "SELECT SUM(total) as paid FROM invoices 
              WHERE status = 'paid' 
              AND MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = MONTH(NOW())
              AND YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = YEAR(NOW())";
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    
    return $row['paid'] ?? 0;
}

?>
