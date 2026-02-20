<?php
/*
 * PDF Download Handler
 * Handles downloading of invoices, quotes, and receipts
 * Forces file download instead of opening in browser
 */

include_once('includes/config.php');
include_once('session.php');

// Check if user is logged in
if (!$user_login) {
    die('Unauthorized access');
}

// Get the file ID from the URL parameter
$file_id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id']) : '';

// Validate file ID
if (empty($file_id)) {
    die('Invalid file ID');
}

// Build the full file path
$file_path = 'invoices/' . $file_id . '.pdf';

// Check if file exists
if (!file_exists($file_path)) {
    die('File not found');
}

// Get file size for proper headers
$file_size = filesize($file_path);

// Set headers to force download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output the file
readfile($file_path);
exit;
?>
