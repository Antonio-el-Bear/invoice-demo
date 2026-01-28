<?php
/**
 * CloudUko Invoice System - Automated Reminder Scheduler
 * This script should be run via cron job (e.g., daily at 9:00 AM)
 * 
 * Cron Job Example (Linux):
 * 0 9 * * * /usr/bin/php /path/to/Invoice-System/cron-send-reminders.php
 * 
 * Windows Task Scheduler:
 * Action: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\clouduko-invoice\cron-send-reminders.php
 * 
 * Or call via web: http://localhost:8080/clouduko-invoice/cron-send-reminders.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Log to file, not output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/cron-errors.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

include_once('includes/config.php');
include_once('enhanced-functions.php');

// Log file for this cron job
$log_file = __DIR__ . '/logs/cron-reminders-' . date('Y-m-d') . '.log';

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

log_message("=== Reminder Scheduler Started ===");

// Connect to database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    log_message("Database Error: " . $mysqli->connect_error);
    exit(1);
}

log_message("Database connected successfully");

// Get all overdue invoices
$overdue_invoices = getOverdueInvoices();
log_message("Found " . count($overdue_invoices) . " overdue invoices");

$reminders_sent = 0;
$reminders_failed = 0;

foreach ($overdue_invoices as $invoice) {
    $invoice_id = $invoice['id'];
    $invoice_number = $invoice['invoice'];
    $customer_email = $invoice['email'];
    $customer_name = $invoice['name'];
    $last_reminder_sent = $invoice['last_reminder_sent'];
    
    // Check if reminder was sent less than 3 days ago
    if (!empty($last_reminder_sent)) {
        $days_since_reminder = strtotime('now') - strtotime($last_reminder_sent);
        $days_since_reminder = floor($days_since_reminder / (60 * 60 * 24));
        
        if ($days_since_reminder < 3) {
            log_message("Skipping Invoice #$invoice_number (reminder sent $days_since_reminder days ago)");
            continue;
        }
    }
    
    // Send reminder
    log_message("Sending reminder for Invoice #$invoice_number to $customer_email");
    
    if (sendOverdueReminder($invoice_id, $customer_email)) {
        log_message("✓ Reminder sent successfully for Invoice #$invoice_number");
        $reminders_sent++;
    } else {
        log_message("✗ Failed to send reminder for Invoice #$invoice_number");
        $reminders_failed++;
    }
}

log_message("=== Reminder Scheduler Completed ===");
log_message("Reminders Sent: $reminders_sent | Failed: $reminders_failed");
log_message("");

$mysqli->close();

// Return appropriate exit code
exit($reminders_failed > 0 ? 1 : 0);
?>
