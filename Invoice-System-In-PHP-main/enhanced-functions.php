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
// ═══════════════════════════════════════════════════════════════════════════════
// ▓ ADVANCED REPORTING & ANALYTICS FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════

// ─── AGED RECEIVABLES ANALYSIS ────────────────────────────────────────────────
// Categorizes overdue invoices by aging bucket: 0-30, 30-60, 60-90, 90+ days
function getAgedReceivables() {
    global $mysqli;

    $today = date('Y-m-d');

    $query = "SELECT
              CASE
                WHEN DATEDIFF('$today', STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y')) BETWEEN 0 AND 30 
                  THEN '0-30 Days'
                WHEN DATEDIFF('$today', STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y')) BETWEEN 31 AND 60 
                  THEN '31-60 Days'
                WHEN DATEDIFF('$today', STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y')) BETWEEN 61 AND 90 
                  THEN '61-90 Days'
                ELSE '90+ Days'
              END AS aging_bucket,
              COUNT(i.invoice) AS invoice_count,
              SUM(i.total - COALESCE(i.amount_paid, 0)) AS total_amount,
              COUNT(DISTINCT c.id) AS unique_customers
              FROM invoices i
              JOIN store_customers c ON c.invoice = i.invoice
              WHERE i.status = 'open'
              AND STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') < '$today'
              GROUP BY aging_bucket
              ORDER BY FIELD(aging_bucket, '0-30 Days', '31-60 Days', '61-90 Days', '90+ Days')";

    $result = $mysqli->query($query);
    $data = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// ─── PAYMENT TRENDS ANALYSIS ──────────────────────────────────────────────────
// Analyzes payment patterns by month for cash flow forecasting
function getPaymentTrends($months = 12) {
    global $mysqli;

    $trend_data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $month_date = date('Y-m-01', strtotime("-$i months"));
        $year = date('Y', strtotime($month_date));
        $month = date('m', strtotime($month_date));

        $query = "SELECT
                  COUNT(*) AS payment_count,
                  SUM(amount) AS total_paid,
                  AVG(amount) AS avg_payment,
                  MAX(amount) AS highest_payment,
                  MIN(amount) AS lowest_payment
                  FROM payments
                  WHERE MONTH(payment_date) = $month AND YEAR(payment_date) = $year";

        $result = $mysqli->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $trend_data[date('M Y', strtotime($month_date))] = $row;
        }
    }
    return $trend_data;
}

// ─── CUSTOMER RISK REPORT ─────────────────────────────────────────────────────
// Identifies high-risk accounts based on payment behavior
function getCustomerRiskReport() {
    global $mysqli;

    $query = "SELECT
              c.id,
              c.name,
              c.email,
              COUNT(i.invoice) AS total_invoices,
              SUM(CASE WHEN i.status = 'paid' THEN 1 ELSE 0 END) AS paid_invoices,
              SUM(CASE WHEN i.status = 'open' AND STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y') < CURDATE() THEN 1 ELSE 0 END) AS overdue_count,
              SUM(CASE WHEN i.status = 'open' THEN (i.total - COALESCE(i.amount_paid, 0)) ELSE 0 END) AS outstanding_amount,
              AVG(DATEDIFF(CURDATE(), STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y'))) AS avg_days_overdue,
              ROUND((SUM(CASE WHEN i.status = 'paid' THEN 1 ELSE 0 END) / COUNT(i.invoice)) * 100, 2) AS payment_completion_rate,
              CASE
                WHEN AVG(DATEDIFF(CURDATE(), STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y'))) > 60 THEN 'HIGH RISK'
                WHEN AVG(DATEDIFF(CURDATE(), STR_TO_DATE(i.invoice_due_date, '%d/%m/%Y'))) > 30 THEN 'MEDIUM RISK'
                ELSE 'LOW RISK'
              END AS risk_level
              FROM store_customers c
              LEFT JOIN invoices i ON c.invoice = i.invoice
              GROUP BY c.id, c.name, c.email
              HAVING overdue_count > 0
              ORDER BY avg_days_overdue DESC";

    $result = $mysqli->query($query);
    $risks = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $risks[] = $row;
        }
    }
    return $risks;
}

// ─── CUSTOMER LIFETIME VALUE ──────────────────────────────────────────────────
function getCustomerLifetimeValue($customer_id = null) {
    global $mysqli;

    $where = '';
    if ($customer_id) {
        $where = "WHERE c.id = " . intval($customer_id);
    }

    $query = "SELECT
              c.id,
              c.name,
              c.email,
              COUNT(i.invoice) AS total_invoices,
              SUM(i.total) AS lifetime_revenue,
              SUM(CASE WHEN i.status = 'paid' THEN i.total ELSE 0 END) AS revenue_collected,
              SUM(CASE WHEN i.status = 'open' THEN i.total ELSE 0 END) AS revenue_pending,
              MIN(STR_TO_DATE(i.invoice_date, '%d/%m/%Y')) AS first_invoice_date,
              MAX(STR_TO_DATE(i.invoice_date, '%d/%m/%Y')) AS last_invoice_date,
              DATEDIFF(MAX(STR_TO_DATE(i.invoice_date, '%d/%m/%Y')), MIN(STR_TO_DATE(i.invoice_date, '%d/%m/%Y'))) AS days_as_customer,
              AVG(i.total) AS avg_invoice_value
              FROM store_customers c
              LEFT JOIN invoices i ON c.invoice = i.invoice
              $where
              GROUP BY c.id, c.name, c.email
              ORDER BY lifetime_revenue DESC";

    $result = $mysqli->query($query);
    $clv_data = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clv_data[] = $row;
        }
    }
    return $clv_data;
}

// ─── PAYMENT BEHAVIOR ANALYSIS ────────────────────────────────────────────────
// Tracks on-time vs late payment patterns per customer
function getCustomerPaymentBehavior($customer_id = null) {
    global $mysqli;

    $where = '';
    if ($customer_id) {
        $where = "WHERE c.id = " . intval($customer_id);
    }

    $query = "SELECT
              c.id,
              c.name,
              COUNT(p.id) AS total_payments,
              SUM(p.amount) AS total_paid,
              AVG(DATEDIFF(p.payment_date, i.invoice_due_date)) AS avg_days_to_pay,
              SUM(CASE WHEN DATEDIFF(p.payment_date, i.invoice_due_date) <= 0 THEN 1 ELSE 0 END) AS on_time_payments,
              SUM(CASE WHEN DATEDIFF(p.payment_date, i.invoice_due_date) > 0 THEN 1 ELSE 0 END) AS late_payments,
              ROUND((SUM(CASE WHEN DATEDIFF(p.payment_date, i.invoice_due_date) <= 0 THEN 1 ELSE 0 END) / COUNT(p.id)) * 100, 2) AS on_time_rate
              FROM store_customers c
              LEFT JOIN payments p ON c.invoice = p.invoice
              LEFT JOIN invoices i ON p.invoice = i.invoice
              $where
              GROUP BY c.id, c.name
              HAVING COUNT(p.id) > 0
              ORDER BY on_time_rate DESC";

    $result = $mysqli->query($query);
    $behavior = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $behavior[] = $row;
        }
    }
    return $behavior;
}

// ─── REVENUE FORECASTING ──────────────────────────────────────────────────────
// Predicts revenue based on historical trends
function getRevenueForecast($months_ahead = 3) {
    global $mysqli;

    $forecast = [];
    $today = new DateTime();

    for ($i = 1; $i <= $months_ahead; $i++) {
        $future_month = clone $today;
        $future_month->add(new DateInterval("P{$i}M"));
        $month = $future_month->format('m');
        $year = $future_month->format('Y');

        // Get average invoices for this month from past years
        $query = "SELECT
                  ROUND(AVG(monthly_total), 2) AS forecasted_revenue
                  FROM (
                    SELECT SUM(total) AS monthly_total
                    FROM invoices
                    WHERE MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = $month
                    AND YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y')) < $year
                    GROUP BY YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y'))
                  ) AS historical_data";

        $result = $mysqli->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $forecast[$future_month->format('M Y')] = $row['forecasted_revenue'] ?? 0;
        }
    }
    return $forecast;
}

// ═══════════════════════════════════════════════════════════════════════════════
// ▓ PAYMENT PLAN FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════

// ─── CREATE PAYMENT PLAN ──────────────────────────────────────────────────────
// Creates an installment plan for an invoice
function createPaymentPlan($invoice_id, $num_installments, $first_payment_date) {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);

    // Get invoice details
    $query = "SELECT total FROM invoices WHERE invoice = '$invoice_id'";
    $result = $mysqli->query($query);

    if (!$result || $result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invoice not found'];
    }

    $invoice = $result->fetch_assoc();
    $total = floatval($invoice['total']);
    $installment_amount = $total / intval($num_installments);

    // Insert into payment_plans table
    $stmt = $mysqli->prepare(
        "INSERT INTO payment_plans (invoice, num_installments, total_amount, installment_amount, first_payment_date, current_installment, status, created_date)
         VALUES (?, ?, ?, ?, ?, 0, 'active', NOW())"
    );
    if (!$stmt) {
        return ['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error];
    }

    $stmt->bind_param('sidds', $invoice_id, $num_installments, $total, $installment_amount, $first_payment_date);
    if ($stmt->execute()) {
        // Update invoice status to indicate it's on a payment plan
        $mysqli->query("UPDATE invoices SET payment_plan = 'yes' WHERE invoice = '$invoice_id'");
        $stmt->close();
        return ['success' => true, 'message' => 'Payment plan created', 'installment_amount' => $installment_amount];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create plan: ' . $mysqli->error];
    }
}

// ─── GET PAYMENT PLAN ──────────────────────────────────────────────────────────
function getPaymentPlan($invoice_id) {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);
    $query = "SELECT * FROM payment_plans WHERE invoice = '$invoice_id' LIMIT 1";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// ─── RECORD PLAN PAYMENT ──────────────────────────────────────────────────────
function recordPaymentPlanPayment($invoice_id, $amount, $payment_date) {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);

    $plan = getPaymentPlan($invoice_id);
    if (!$plan) {
        return ['success' => false, 'message' => 'No payment plan found for this invoice'];
    }

    $new_installment = $plan['current_installment'] + 1;

    // Record the payment
    $payment_result = recordPayment($invoice_id, $amount, $payment_date, 'Plan Payment');

    if ($payment_result['success']) {
        // Update plan progress
        $total_paid = $plan['installment_amount'] * $new_installment;
        $plan_status = ($new_installment >= $plan['num_installments']) ? 'completed' : 'active';

        $mysqli->query("UPDATE payment_plans SET current_installment = $new_installment, status = '$plan_status' WHERE invoice = '$invoice_id'");

        return [
            'success' => true,
            'message' => 'Plan payment recorded',
            'installment' => "$new_installment of {$plan['num_installments']}",
            'plan_status' => $plan_status
        ];
    } else {
        return $payment_result;
    }
}

// ─── CHECK PAYMENT PLAN STATUS ────────────────────────────────────────────────
function checkPaymentPlanStatus($invoice_id) {
    global $mysqli;

    $invoice_id = $mysqli->real_escape_string($invoice_id);
    $query = "SELECT pp.*, i.total, i.amount_paid
              FROM payment_plans pp
              JOIN invoices i ON i.invoice = pp.invoice
              WHERE pp.invoice = '$invoice_id'";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $plan = $result->fetch_assoc();
        $remaining_installments = $plan['num_installments'] - $plan['current_installment'];
        $next_payment_amount = $plan['installment_amount'];

        return [
            'invoice_id' => $invoice_id,
            'status' => $plan['status'],
            'current_installment' => $plan['current_installment'],
            'total_installments' => $plan['num_installments'],
            'remaining_installments' => $remaining_installments,
            'installment_amount' => $plan['installment_amount'],
            'total_due' => $plan['total_amount'],
            'amount_paid' => $plan['total_amount'] * ($plan['current_installment'] / $plan['num_installments']),
            'amount_remaining' => $plan['total_amount'] - floatval($plan['amount_paid']),
            'progress_percent' => round(($plan['current_installment'] / $plan['num_installments']) * 100, 2)
        ];
    }
    return null;
}

// ═══════════════════════════════════════════════════════════════════════════════
// ▓ DASHBOARD & KPI FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════

// ─── GET DASHBOARD METRICS ────────────────────────────────────────────────────
function getDashboardMetrics() {
    global $mysqli;

    $today = date('Y-m-d');
    $month_start = date('Y-m-01');
    $year_start = date('Y-01-01');

    $metrics = [
        'total_revenue' => 0,
        'paid_revenue' => 0,
        'outstanding_revenue' => 0,
        'overdue_revenue' => 0,
        'this_month_revenue' => 0,
        'this_year_revenue' => 0,
        'total_invoices' => 0,
        'paid_invoices' => 0,
        'open_invoices' => 0,
        'overdue_invoices' => 0,
        'total_customers' => 0,
        'active_customers' => 0,
        'collection_rate' => 0,
    ];

    // Total & paid revenue
    $q1 = "SELECT SUM(total) as total, SUM(amount_paid) as paid FROM invoices";
    $r1 = $mysqli->query($q1);
    if ($r1) {
        $d1 = $r1->fetch_assoc();
        $metrics['total_revenue'] = floatval($d1['total'] ?? 0);
        $metrics['paid_revenue'] = floatval($d1['paid'] ?? 0);
        $metrics['outstanding_revenue'] = $metrics['total_revenue'] - $metrics['paid_revenue'];
    }

    // This month & year
    $q2 = "SELECT
            SUM(CASE WHEN MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = MONTH('$month_start') AND YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = YEAR('$month_start') THEN total ELSE 0 END) as month_total,
            SUM(CASE WHEN YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = YEAR('$year_start') THEN total ELSE 0 END) as year_total
            FROM invoices WHERE status = 'paid'";
    $r2 = $mysqli->query($q2);
    if ($r2) {
        $d2 = $r2->fetch_assoc();
        $metrics['this_month_revenue'] = floatval($d2['month_total'] ?? 0);
        $metrics['this_year_revenue'] = floatval($d2['year_total'] ?? 0);
    }

    // Overdue
    $q3 = "SELECT SUM(total - COALESCE(amount_paid, 0)) as overdue FROM invoices
            WHERE status = 'open' AND STR_TO_DATE(invoice_due_date, '%d/%m/%Y') < '$today'";
    $r3 = $mysqli->query($q3);
    if ($r3) {
        $d3 = $r3->fetch_assoc();
        $metrics['overdue_revenue'] = floatval($d3['overdue'] ?? 0);
    }

    // Invoice counts
    $q4 = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN status = 'open' AND STR_TO_DATE(invoice_due_date, '%d/%m/%Y') < '$today' THEN 1 ELSE 0 END) as overdue
            FROM invoices";
    $r4 = $mysqli->query($q4);
    if ($r4) {
        $d4 = $r4->fetch_assoc();
        $metrics['total_invoices'] = intval($d4['total'] ?? 0);
        $metrics['paid_invoices'] = intval($d4['paid'] ?? 0);
        $metrics['open_invoices'] = intval($d4['open_count'] ?? 0);
        $metrics['overdue_invoices'] = intval($d4['overdue'] ?? 0);
    }

    // Customer metrics
    $q5 = "SELECT COUNT(*) as total, COUNT(DISTINCT CASE WHEN i.invoice IS NOT NULL THEN c.id END) as active FROM store_customers c LEFT JOIN invoices i ON c.invoice = i.invoice";
    $r5 = $mysqli->query($q5);
    if ($r5) {
        $d5 = $r5->fetch_assoc();
        $metrics['total_customers'] = intval($d5['total'] ?? 0);
        $metrics['active_customers'] = intval($d5['active'] ?? 0);
    }

    // Collection rate
    if ($metrics['total_revenue'] > 0) {
        $metrics['collection_rate'] = round(($metrics['paid_revenue'] / $metrics['total_revenue']) * 100, 2);
    }

    return $metrics;
}

// ─── GET CASH FLOW FORECAST ───────────────────────────────────────────────────
function getCashFlowForecast($months = 6) {
    global $mysqli;

    $forecast = [];

    for ($i = 0; $i < $months; $i++) {
        $month_date = date('Y-m-01', strtotime("+$i months"));
        $month = date('m', strtotime($month_date));
        $year = date('Y', strtotime($month_date));
        $month_label = date('M Y', strtotime($month_date));

        // Expected invoices
        $q_invoices = "SELECT SUM(total) as expected FROM invoices
                       WHERE MONTH(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = $month
                       AND YEAR(STR_TO_DATE(invoice_date, '%d/%m/%Y')) = $year";

        // Expected collections (based on historical payment patterns)
        $q_collections = "SELECT SUM(amount) as expected FROM payments
                          WHERE MONTH(payment_date) = $month
                          AND YEAR(payment_date) = $year";

        $r_inv = $mysqli->query($q_invoices);
        $r_col = $mysqli->query($q_collections);

        $invoices_amount = $r_inv ? floatval($r_inv->fetch_assoc()['expected'] ?? 0) : 0;
        $collections_amount = $r_col ? floatval($r_col->fetch_assoc()['expected'] ?? 0) : 0;

        $forecast[$month_label] = [
            'expected_invoices' => $invoices_amount,
            'expected_collections' => $collections_amount,
            'net_cash_flow' => $collections_amount - $invoices_amount
        ];
    }

    return $forecast;
}

// ─── GET MONTHLY COMPARISON ───────────────────────────────────────────────────
function getMonthlyComparison($current_month = null, $current_year = null) {
    global $mysqli;

    if ($current_month === null) $current_month = date('m');
    if ($current_year === null) $current_year = date('Y');

    $current_month = intval($current_month);
    $current_year = intval($current_year);
    $previous_year = $current_year - 1;

    $query = "SELECT
              'Current Year' as period,
              COALESCE(SUM(i1.total), 0) as revenue,
              COALESCE(SUM(CASE WHEN i1.status = 'paid' THEN i1.total ELSE 0 END), 0) as paid,
              COUNT(i1.invoice) as invoice_count
              FROM invoices i1
              WHERE MONTH(STR_TO_DATE(i1.invoice_date, '%d/%m/%Y')) = $current_month
              AND YEAR(STR_TO_DATE(i1.invoice_date, '%d/%m/%Y')) = $current_year
              UNION ALL
              SELECT
              'Previous Year' as period,
              COALESCE(SUM(i2.total), 0) as revenue,
              COALESCE(SUM(CASE WHEN i2.status = 'paid' THEN i2.total ELSE 0 END), 0) as paid,
              COUNT(i2.invoice) as invoice_count
              FROM invoices i2
              WHERE MONTH(STR_TO_DATE(i2.invoice_date, '%d/%m/%Y')) = $current_month
              AND YEAR(STR_TO_DATE(i2.invoice_date, '%d/%m/%Y')) = $previous_year";

    $result = $mysqli->query($query);
    $comparison = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $comparison[] = $row;
        }
    }
    return $comparison;
}

?>