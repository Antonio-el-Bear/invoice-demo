<?php
/**
 * CSV Download Test Script
 * Tests the CSV export functionality to ensure PHP 8.3 compatibility
 */

session_start();
require_once 'includes/config.php';

// Simulate logged-in user for testing
$_SESSION['id'] = 1;

echo "<!DOCTYPE html><html><head><title>CSV Download Test</title></head><body>";
echo "<h1>CSV Download Test</h1>";
echo "<p>Testing CSV export functionality with PHP " . phpversion() . "</p><hr>";

// Connect to database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('<p style="color:red;">Database Connection Error: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error . '</p>');
}

echo "<p style='color:green;'>✓ Database connected successfully</p>";

// Check if downloads directory exists and is writable
$downloads_dir = 'downloads';
if (!file_exists($downloads_dir)) {
    echo "<p style='color:orange;'>⚠ Downloads directory doesn't exist - creating it...</p>";
    mkdir($downloads_dir, 0777, true);
}

if (!is_writable($downloads_dir)) {
    echo "<p style='color:red;'>✗ Downloads directory is not writable</p>";
} else {
    echo "<p style='color:green;'>✓ Downloads directory is writable</p>";
}

// Test CSV generation
$file_name = 'invoice-export-test-'.date('d-m-Y-His').'.csv';
$file_path = 'downloads/'.$file_name;

echo "<h2>Step 1: Create CSV File</h2>";
$file = fopen($file_path, "w");

if (!$file) {
    die("<p style='color:red;'>✗ Failed to create CSV file</p></body></html>");
}

echo "<p style='color:green;'>✓ CSV file created: $file_path</p>";

// Query invoices
echo "<h2>Step 2: Query Database</h2>";
$query_table_columns_data = "SELECT * 
                                FROM invoices i
                                JOIN customers c
                                ON c.invoice = i.invoice
                                WHERE i.invoice = c.invoice
                                ORDER BY i.invoice";

$result_column_data = mysqli_query($mysqli, $query_table_columns_data);

if (!$result_column_data) {
    echo "<p style='color:red;'>✗ Query failed: " . $mysqli->error . "</p>";
    fclose($file);
    die("</body></html>");
}

$row_count = mysqli_num_rows($result_column_data);
echo "<p style='color:green;'>✓ Query successful - found $row_count invoice records</p>";

// Write headers (column names)
echo "<h2>Step 3: Write CSV Headers</h2>";
$first_row = true;

// Fetch field information
$field_info = mysqli_fetch_fields($result_column_data);
$headers = array();
foreach ($field_info as $field) {
    $headers[] = $field->name;
}

// Write headers to CSV
fputcsv($file, $headers, ",", '"');
echo "<p style='color:green;'>✓ CSV headers written (" . count($headers) . " columns)</p>";

// Write data rows
echo "<h2>Step 4: Write CSV Data</h2>";
$data_row_count = 0;

// Reset the result pointer
mysqli_data_seek($result_column_data, 0);

while ($column_data = $result_column_data->fetch_row()) {
    $table_column_data = array();
    foreach($column_data as $data) {
        $table_column_data[] = $data;
    }
    
    fputcsv($file, $table_column_data, ",", '"');
    $data_row_count++;
}

echo "<p style='color:green;'>✓ CSV data written - $data_row_count rows</p>";

// Close file
fclose($file);
echo "<p style='color:green;'>✓ CSV file closed</p>";

// Check file size
$file_size = filesize($file_path);
echo "<p style='color:green;'>✓ File size: " . number_format($file_size) . " bytes</p>";

// Provide download link
echo "<h2>Result</h2>";
echo "<p style='color:green; font-weight:bold;'>✓✓✓ CSV Export Successful! ✓✓✓</p>";
echo "<p><strong>File generated:</strong> $file_name</p>";
echo "<p><a href='$file_path' download style='display:inline-block; background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Download CSV File</a></p>";

// Show file preview
echo "<h2>CSV Preview (first 5 rows)</h2>";
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd; overflow-x:auto;'>";
$preview_lines = array_slice(file($file_path), 0, 6); // Get first 6 lines (header + 5 data rows)
foreach ($preview_lines as $line) {
    echo htmlspecialchars($line);
}
echo "</pre>";

// Close database connection
$mysqli->close();

echo "</body></html>";
?>
