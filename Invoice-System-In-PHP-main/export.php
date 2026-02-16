<?php
/**
 * CloudUko Invoice System - CSV Export Handler (ENHANCED)
 * 
 * Supports multiple export formats and filtering options
 * 
 * Usage:
 *   export.php?type=invoices
 *   export.php?type=invoices&format=json
 *   export.php?type=invoices&format=excel
 *   export.php?type=invoices&from_date=01/01/2025&to_date=31/12/2025
 *   export.php?type=payments&status=paid
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
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : null;
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;

switch ($type) {
    case 'invoices':
        if ($format === 'json') {
            exportInvoicesJSON($from_date, $to_date, $status);
        } elseif ($format === 'excel') {
            exportInvoicesExcel($from_date, $to_date, $status);
        } else {
            exportInvoicesCSV($from_date, $to_date, $status);
        }
        break;

    case 'payments':
        if ($format === 'json') {
            exportPaymentsJSON($from_date, $to_date);
        } elseif ($format === 'excel') {
            exportPaymentsExcel($from_date, $to_date);
        } else {
            exportPaymentsCSV($from_date, $to_date);
        }
        break;

    case 'customers':
        if ($format === 'json') {
            exportCustomersJSON();
        } elseif ($format === 'excel') {
            exportCustomersExcel();
        } else {
            exportCustomersCSV();
        }
        break;

    case 'aged-receivables':
        if ($format === 'json') {
            exportAgedReceivablesJSON();
        } else {
            exportAgedReceivablesCSV();
        }
        break;

    case 'risk-report':
        if ($format === 'json') {
            exportRiskReportJSON();
        } else {
            exportRiskReportCSV();
        }
        break;

    case 'dashboard-metrics':
        exportDashboardMetricsJSON();
        break;

    default:
        header('Location: index.php');
        exit;
}

// ─── ENHANCED CSV EXPORTS WITH FILTERING ──────────────────────────────────────

function exportInvoicesCSV($from_date = null, $to_date = null, $status = null) {
    global $mysqli;

    $where = [];
    if ($from_date) {
        $from_obj = DateTime::createFromFormat('d/m/Y', $from_date);
        $where[] = "STR_TO_DATE(i.invoice_date, '%d/%m/%Y') >= '" . $from_obj->format('Y-m-d') . "'";
    }
    if ($to_date) {
        $to_obj = DateTime::createFromFormat('d/m/Y', $to_date);
        $where[] = "STR_TO_DATE(i.invoice_date, '%d/%m/%Y') <= '" . $to_obj->format('Y-m-d') . "'";
    }
    if ($status) {
        $where[] = "i.status = '" . $mysqli->real_escape_string($status) . "'";
    }

    $where_clause = count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "";

    $query  = "SELECT i.invoice, c.name, c.email, i.invoice_date, i.invoice_due_date,
                      i.invoice_type, i.status, i.total, COALESCE(i.amount_paid,0) AS amount_paid,
                      (i.total - COALESCE(i.amount_paid,0)) AS balance
               FROM invoices i
               JOIN store_customers c ON c.invoice = i.invoice
               $where_clause
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

function exportPaymentsCSV($from_date = null, $to_date = null) {
    global $mysqli;

    $where = [];
    if ($from_date) {
        $from_obj = DateTime::createFromFormat('d/m/Y', $from_date);
        $where[] = "p.payment_date >= '" . $from_obj->format('Y-m-d') . "'";
    }
    if ($to_date) {
        $to_obj = DateTime::createFromFormat('d/m/Y', $to_date);
        $where[] = "p.payment_date <= '" . $to_obj->format('Y-m-d') . "'";
    }

    $where_clause = count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "";

    $query  = "SELECT p.id, p.invoice, c.name, p.amount, p.payment_date, p.payment_method, p.notes
               FROM payments p
               JOIN store_customers c ON c.invoice = p.invoice
               $where_clause
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
            $row['phone'] ?? '',
            $row['address_1'] ?? '',
            $row['address_2'] ?? '',
            $row['town'] ?? '',
            $row['county'] ?? '',
            $row['postcode'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

function exportAgedReceivablesCSV() {
    $data = getAgedReceivables();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="aged_receivables_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Aging Bucket', 'Invoice Count', 'Total Amount', 'Unique Customers']);

    foreach ($data as $row) {
        fputcsv($out, [
            $row['aging_bucket'],
            $row['invoice_count'],
            number_format($row['total_amount'], 2),
            $row['unique_customers'],
        ]);
    }
    fclose($out);
    exit;
}

function exportRiskReportCSV() {
    $data = getCustomerRiskReport();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="risk_report_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Customer', 'Email', 'Total Invoices', 'Overdue Count', 'Outstanding', 'Avg Days Overdue', 'Payment Rate', 'Risk Level']);

    foreach ($data as $row) {
        fputcsv($out, [
            $row['name'],
            $row['email'],
            $row['total_invoices'],
            $row['overdue_count'],
            number_format($row['outstanding_amount'], 2),
            round($row['avg_days_overdue'], 1),
            $row['payment_completion_rate'] . '%',
            $row['risk_level'],
        ]);
    }
    fclose($out);
    exit;
}

// ─── JSON EXPORTS ──────────────────────────────────────────────────────────────

function exportInvoicesJSON($from_date = null, $to_date = null, $status = null) {
    global $mysqli;

    $where = [];
    if ($from_date) {
        $from_obj = DateTime::createFromFormat('d/m/Y', $from_date);
        $where[] = "STR_TO_DATE(i.invoice_date, '%d/%m/%Y') >= '" . $from_obj->format('Y-m-d') . "'";
    }
    if ($to_date) {
        $to_obj = DateTime::createFromFormat('d/m/Y', $to_date);
        $where[] = "STR_TO_DATE(i.invoice_date, '%d/%m/%Y') <= '" . $to_obj->format('Y-m-d') . "'";
    }
    if ($status) {
        $where[] = "i.status = '" . $mysqli->real_escape_string($status) . "'";
    }

    $where_clause = count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "";

    $query  = "SELECT i.invoice, c.name, c.email, i.invoice_date, i.invoice_due_date,
                      i.invoice_type, i.status, i.total, COALESCE(i.amount_paid,0) AS amount_paid,
                      (i.total - COALESCE(i.amount_paid,0)) AS balance
               FROM invoices i
               JOIN store_customers c ON c.invoice = i.invoice
               $where_clause
               ORDER BY i.invoice DESC";
    $result = $mysqli->query($query);

    $invoices = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
    }

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="invoices_' . date('Y-m-d') . '.json"');
    echo json_encode(['invoices' => $invoices, 'export_date' => date('Y-m-d H:i:s'), 'total_count' => count($invoices)], JSON_PRETTY_PRINT);
    exit;
}

function exportPaymentsJSON($from_date = null, $to_date = null) {
    global $mysqli;

    $where = [];
    if ($from_date) {
        $from_obj = DateTime::createFromFormat('d/m/Y', $from_date);
        $where[] = "p.payment_date >= '" . $from_obj->format('Y-m-d') . "'";
    }
    if ($to_date) {
        $to_obj = DateTime::createFromFormat('d/m/Y', $to_date);
        $where[] = "p.payment_date <= '" . $to_obj->format('Y-m-d') . "'";
    }

    $where_clause = count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "";

    $query  = "SELECT p.id, p.invoice, c.name, p.amount, p.payment_date, p.payment_method, p.notes
               FROM payments p
               JOIN store_customers c ON c.invoice = p.invoice
               $where_clause
               ORDER BY p.payment_date DESC";
    $result = $mysqli->query($query);

    $payments = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    }

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="payments_' . date('Y-m-d') . '.json"');
    echo json_encode(['payments' => $payments, 'export_date' => date('Y-m-d H:i:s'), 'total_count' => count($payments)], JSON_PRETTY_PRINT);
    exit;
}

function exportCustomersJSON() {
    global $mysqli;

    $result = $mysqli->query("SELECT * FROM store_customers ORDER BY name ASC");

    $customers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.json"');
    echo json_encode(['customers' => $customers, 'export_date' => date('Y-m-d H:i:s'), 'total_count' => count($customers)], JSON_PRETTY_PRINT);
    exit;
}

function exportAgedReceivablesJSON() {
    $data = getAgedReceivables();

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="aged_receivables_' . date('Y-m-d') . '.json"');
    echo json_encode(['aged_receivables' => $data, 'export_date' => date('Y-m-d H:i:s')], JSON_PRETTY_PRINT);
    exit;
}

function exportRiskReportJSON() {
    $data = getCustomerRiskReport();

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="risk_report_' . date('Y-m-d') . '.json"');
    echo json_encode(['risk_report' => $data, 'export_date' => date('Y-m-d H:i:s')], JSON_PRETTY_PRINT);
    exit;
}

function exportDashboardMetricsJSON() {
    $metrics = getDashboardMetrics();

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="dashboard_metrics_' . date('Y-m-d') . '.json"');
    echo json_encode(['metrics' => $metrics, 'export_date' => date('Y-m-d H:i:s')], JSON_PRETTY_PRINT);
    exit;
}

// ─── EXCEL EXPORTS (using CSV format - can be opened in Excel) ────────────────
// Note: For true Excel format, consider using PHPExcel or OpenSpout library

function exportInvoicesExcel($from_date = null, $to_date = null, $status = null) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="invoices_' . date('Y-m-d') . '.xlsx"');
    exportInvoicesCSV($from_date, $to_date, $status);
}

function exportPaymentsExcel($from_date = null, $to_date = null) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="payments_' . date('Y-m-d') . '.xlsx"');
    exportPaymentsCSV($from_date, $to_date);
}

function exportCustomersExcel() {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.xlsx"');
    exportCustomersCSV();
}

?>
