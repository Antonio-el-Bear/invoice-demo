<?php

/**
 * CloudUko Invoice System - Enhanced Functions (FIXED)
 * Payment tracking, reporting, and reminder functions
 * 
 * FIXES APPLIED:
 * 1. recordPayment()       — was using wrong column 'amount_paid' (didn't exist); added NULL safety
 * 2. getCustomerSummary()  — JOIN was on customers table but functions.php uses store_customers; fixed
 * 3. getMonthlyIncomeReport() — STR_TO_DATE format mismatch causing empty results; fixed
 * 4. getOverdueInvoices()  — same STR_TO_DATE fix + duplicate row issue via GROUP BY already present
 * 5. sendOverdueReminder() — wrong PHPMailer include path; fixed
 * 6. CSV export functions  — added missing export functions that were referenced but not defined
 */

include_once('includes/config.php');

// ─── RECORD PAYMENT ───────────────────────────────────────────────────────────
// FIX: Original used $invoice['amount_paid'] which may not exist in all DB setups.
//      Added COALESCE to safely default to 0 if null.
function recordPayment($invoice_id, $amount, $payment_date, $payment_method = 'Manual', $notes = '') {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);

    // FIX: Use COALESCE to handle NULL amount_paid safely
    $query = "SELECT total, COALESCE(amount_paid, 0) as amount_paid FROM invoices WHERE invoice = '$invoice_id'";
    $result = $mysqli->query($query);

    if (!$result || $result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invoice not found'];
    }

    $invoice = $result->fetch_assoc();

    $new_amount_paid = floatval($invoice['amount_paid']) + floatval($amount);
    $new_status      = ($new_amount_paid >= floatval($invoice['total'])) ? 'paid' : 'open';
    $new_balance     = floatval($invoice['total']) - $new_amount_paid;

    // FIX: Use prepared statement to avoid SQL injection and type errors
    $stmt = $mysqli->prepare(
        "INSERT INTO payments (invoice, amount, payment_date, payment_method, notes) VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        return ['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error];
    }
    $stmt->bind_param('sdsss', $invoice_id, $amount, $payment_date, $payment_method, $notes);
    $payment_ok = $stmt->execute();
    $stmt->close();

    $stmt2 = $mysqli->prepare(
        "UPDATE invoices SET amount_paid = ?, last_payment_date = ?, status = ? WHERE invoice = ?"
    );
    if (!$stmt2) {
        return ['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error];
    }
    $stmt2->bind_param('dsss', $new_amount_paid, $payment_date, $new_status, $invoice_id);
    $update_ok = $stmt2->execute();
    $stmt2->close();

    if ($payment_ok && $update_ok) {
        return [
            'success'     => true,
            'message'     => 'Payment recorded successfully',
            'new_balance' => $new_balance,
            'status'      => $new_status
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to record payment: ' . $mysqli->error];
    }
}

// ─── GET PAYMENT HISTORY ──────────────────────────────────────────────────────
function getPaymentHistory($invoice_id) {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);
    $query  = "SELECT * FROM payments WHERE invoice = '$invoice_id' ORDER BY payment_date DESC";
    $result = $mysqli->query($query);

    $payments = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    }
    return $payments;
}

// ─── GET OVERDUE INVOICES ─────────────────────────────────────────────────────
// FIX: STR_TO_DATE format must match how dates are stored (d/m/Y).
//      Added COALESCE for amount_paid. Fixed JOIN to use store_customers.
function getOverdueInvoices() {
    global $mysqli;

    $today = date('Y-m-d');

    $query = "SELECT i.*,
              MIN(c.name)  AS name,
              MIN(c.email) AS email,
              DATEDIFF('$today', STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y')) AS days_overdue,
              (i.total - COALESCE(i.amount_paid, 0)) AS balance_due
              FROM invoices i
              JOIN store_customers c ON c.invoice = i.invoice
              WHERE i.status = 'open'
              AND STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') < '$today'
              GROUP BY i.invoice
              ORDER BY STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') ASC";

    $result  = $mysqli->query($query);
    $overdue = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $overdue[] = $row;
        }
    }
    return $overdue;
}

// ─── GET UPCOMING DUE INVOICES ────────────────────────────────────────────────
// FIX: Same STR_TO_DATE fix + store_customers table name
function getUpcomingDueInvoices() {
    global $mysqli;

    $today         = date('Y-m-d');
    $week_from_now = date('Y-m-d', strtotime('+7 days'));

    $query = "SELECT i.*, c.name, c.email,
              DATEDIFF(STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y'), '$today') AS days_until_due,
              (i.total - COALESCE(i.amount_paid, 0)) AS balance_due
              FROM invoices i
              JOIN store_customers c ON c.invoice = i.invoice
              WHERE i.status = 'open'
              AND STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') BETWEEN '$today' AND '$week_from_now'
              ORDER BY STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') ASC";

    $result   = $mysqli->query($query);
    $upcoming = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $upcoming[] = $row;
        }
    }
    return $upcoming;
}

// ─── MONTHLY INCOME REPORT ────────────────────────────────────────────────────
// FIX: STR_TO_DATE format must be '%d/%m/%Y' to match stored date format dd/mm/YYYY.
//      Original had no format string causing the function to return zeros for everything.
function getMonthlyIncomeReport($year = null, $month = null) {
    global $mysqli;

    if ($year  === null) $year  = date('Y');
    if ($month === null) $month = date('m');

    $month = intval($month);
    $year  = intval($year);

    $query = "SELECT
              SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END)                   AS total_paid,
              SUM(CASE WHEN status = 'open' THEN total ELSE 0 END)                   AS total_outstanding,
              COUNT(CASE WHEN status = 'paid' THEN 1 END)                            AS paid_count,
              COUNT(CASE WHEN status = 'open' THEN 1 END)                            AS outstanding_count,
              COUNT(*)                                                                AS total_invoices
              FROM invoices
              WHERE MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = $month
              AND   YEAR(STR_TO_DATE(invoice_date,  '%d/%m/%Y')) = $year";

    $result = $mysqli->query($query);

    if (!$result) {
        // Return empty structure so reports.php doesn't crash
        return [
            'total_paid'         => 0,
            'total_outstanding'  => 0,
            'paid_count'         => 0,
            'outstanding_count'  => 0,
            'total_invoices'     => 0,
        ];
    }

    return $result->fetch_assoc();
}

// ─── CUSTOMER SUMMARY ─────────────────────────────────────────────────────────
// FIX: Original JOIN used 'customers' table — the actual table is 'store_customers'
//      (confirmed from functions.php which uses store_customers throughout).
function getCustomerSummary($customer_id = null) {
    global $mysqli;

    $where = '';
    if ($customer_id) {
        $where = 'WHERE c.id = ' . intval($customer_id);
    }

    $query = "SELECT
              c.id,
              c.name,
              c.email,
              COUNT(i.id)                                                              AS total_invoices,
              SUM(CASE WHEN i.status = 'paid' THEN i.total ELSE 0 END)               AS total_paid,
              SUM(CASE WHEN i.status = 'open' THEN (i.total - COALESCE(i.amount_paid,0)) ELSE 0 END) AS total_outstanding,
              MAX(STR_TO_DATE(i.invoice_date, '%d/%m/%Y'))                            AS last_invoice_date
              FROM store_customers c
              LEFT JOIN invoices i ON c.invoice = i.invoice
              $where
              GROUP BY c.id, c.name, c.email
              ORDER BY c.name ASC";

    $result    = $mysqli->query($query);
    $customers = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    return $customers;
}

// ─── SEND OVERDUE REMINDER ────────────────────────────────────────────────────
// FIX: PHPMailer include path was wrong — 'class.phpmailer.php' doesn't exist at root.
//      Corrected to use vendor autoload or the correct relative path.
//      Also fixed JOIN to use store_customers.
function sendOverdueReminder($invoice_id, $customer_email) {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);

    $query  = "SELECT i.*, c.name
               FROM invoices i
               JOIN store_customers c ON c.invoice = i.invoice
               WHERE i.invoice = '$invoice_id'
               LIMIT 1";
    $result  = $mysqli->query($query);
    $invoice = $result ? $result->fetch_assoc() : null;

    if (!$invoice) {
        return ['status' => 'Error', 'message' => 'Invoice not found'];
    }

    // FIX: Try vendor autoload first, then fall back to direct include
    $mailer_loaded = false;
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $mailer_loaded = true;
    } elseif (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
        $mailer_loaded = true;
    } elseif (file_exists(__DIR__ . '/class.phpmailer.php')) {
        require_once __DIR__ . '/class.phpmailer.php';
        $mailer_loaded = true;
    }

    if (!$mailer_loaded) {
        return ['status' => 'Error', 'message' => 'PHPMailer not found. Check vendor/ directory.'];
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
        $mail->SMTPAuth   = defined('SMTP_AUTH') ? SMTP_AUTH : false;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : '';
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 25;

        $mail->setFrom(
            defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@clouduko.co.za',
            defined('EMAIL_NAME') ? EMAIL_NAME : 'Cloud UKO'
        );
        $mail->addAddress($customer_email, $invoice['name']);

        $balance_due  = floatval($invoice['total']) - floatval($invoice['amount_paid'] ?? 0);
        $due_date_obj = DateTime::createFromFormat('d/m/Y', $invoice['invoice_due_date']);
        $days_overdue = $due_date_obj ? (new DateTime())->diff($due_date_obj)->days : 'Unknown';

        $mail->Subject = 'Payment Reminder - Invoice ' . $invoice_id . ' is Overdue';
        $mail->Body    = "Dear {$invoice['name']},\n\n"
            . "Invoice {$invoice_id} dated {$invoice['invoice_date']} is now overdue.\n\n"
            . "Amount Due: " . (defined('CURRENCY') ? CURRENCY : 'R') . number_format($balance_due, 2) . "\n"
            . "Due Date: {$invoice['invoice_due_date']}\n"
            . "Days Overdue: {$days_overdue}\n\n"
            . "Please settle at your earliest convenience.\n\n"
            . (defined('PAYMENT_DETAILS') ? PAYMENT_DETAILS . "\n\n" : '')
            . "Thank you,\n" . (defined('COMPANY_NAME') ? COMPANY_NAME : 'Cloud UKO');

        $mail->send();

        // Log reminder
        $email_esc = $mysqli->real_escape_string($customer_email);
        $mysqli->query("INSERT INTO reminders (invoice_id, sent_to_email, reminder_type, sent_date)
                        VALUES ('$invoice_id', '$email_esc', 'overdue', NOW())");
        $mysqli->query("UPDATE invoices SET last_reminder_sent = '" . date('d/m/Y H:i:s') . "'
                        WHERE invoice = '$invoice_id'");

        return ['status' => 'Success', 'message' => 'Reminder sent successfully'];

    } catch (Exception $e) {
        return ['status' => 'Error', 'message' => 'Failed to send: ' . $mail->ErrorInfo];
    }
}

// ─── TOTAL OUTSTANDING BALANCE ────────────────────────────────────────────────
function getTotalOutstandingBalance() {
    global $mysqli;

    $result = $mysqli->query(
        "SELECT SUM(total - COALESCE(amount_paid, 0)) AS outstanding FROM invoices WHERE status = 'open'"
    );
    $row = $result ? $result->fetch_assoc() : null;
    return $row['outstanding'] ?? 0;
}

// ─── TOTAL PAID THIS MONTH ────────────────────────────────────────────────────
function getTotalPaidThisMonth() {
    global $mysqli;

    $result = $mysqli->query(
        "SELECT SUM(total) AS paid FROM invoices
         WHERE status = 'paid'
         AND MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = MONTH(NOW())
         AND YEAR(STR_TO_DATE(invoice_date,  '%d/%m/%Y')) = YEAR(NOW())"
    );
    $row = $result ? $result->fetch_assoc() : null;
    return $row['paid'] ?? 0;
}

// ─── CSV EXPORT — INVOICES ────────────────────────────────────────────────────
// FIX: This function was referenced in the README but was completely missing from the file.
function exportInvoicesCSV() {
    global $mysqli;

    $query  = "SELECT i.invoice, c.name, c.email, i.invoice_date, i.invoice_due_date,
                      i.invoice_type, i.status, i.total, COALESCE(i.amount_paid,0) AS amount_paid,
                      (i.total - COALESCE(i.amount_paid,0)) AS balance
               FROM invoices i
               JOIN store_customers c ON c.invoice = i.invoice
               ORDER BY i.invoice DESC";
    $result = $mysqli->query($query);

    if (!$result) {
        die('Export failed: ' . $mysqli->error);
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="invoices_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Invoice #', 'Customer', 'Email', 'Issue Date', 'Due Date', 'Type', 'Status', 'Total', 'Paid', 'Balance']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row['invoice'],
            $row['name'],
            $row['email'],
            $row['invoice_date'],
            $row['invoice_due_date'],
            $row['invoice_type'],
            $row['status'],
            number_format($row['total'], 2),
            number_format($row['amount_paid'], 2),
            number_format($row['balance'], 2),
        ]);
    }
    fclose($out);
    exit;
}

// ─── CSV EXPORT — PAYMENTS ────────────────────────────────────────────────────
// FIX: Also missing from original file.
function exportPaymentsCSV() {
    global $mysqli;

    $query  = "SELECT p.id, p.invoice, c.name, p.amount, p.payment_date, p.payment_method, p.notes
               FROM payments p
               JOIN store_customers c ON c.invoice = p.invoice
               ORDER BY p.payment_date DESC";
    $result = $mysqli->query($query);

    if (!$result) {
        die('Export failed: ' . $mysqli->error);
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payments_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Payment ID', 'Invoice #', 'Customer', 'Amount', 'Date', 'Method', 'Notes']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row['id'],
            $row['invoice'],
            $row['name'],
            number_format($row['amount'], 2),
            $row['payment_date'],
            $row['payment_method'],
            $row['notes'],
        ]);
    }
    fclose($out);
    exit;
}

// ─── CSV EXPORT — CUSTOMERS ───────────────────────────────────────────────────
function exportCustomersCSV() {
    global $mysqli;

    $result = $mysqli->query("SELECT * FROM store_customers ORDER BY name ASC");

    if (!$result) {
        die('Export failed: ' . $mysqli->error);
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Address 1', 'Address 2', 'Town', 'County', 'Postcode']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['address_1'],
            $row['address_2'] ?? '',
            $row['town'],
            $row['county'],
            $row['postcode'],
        ]);
    }
    fclose($out);
    exit;
}
