<?php
/**
 * CloudUko Invoice System - CSV Export Handler (NEW FILE)
 * 
 * This file was missing entirely. It handles all CSV download requests.
 * 
 * Usage:
 *   export.php?type=invoices
 *   export.php?type=payments
 *   export.php?type=customers
 */

include_once('includes/config.php');
include_once('enhanced-functions.php');

// Must be logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : '';

switch ($type) {
    case 'invoices':
        exportInvoicesCSV();
        break;

    case 'payments':
        exportPaymentsCSV();
        break;

    case 'customers':
        exportCustomersCSV();
        break;

    default:
        header('Location: index.php');
        exit;
}
