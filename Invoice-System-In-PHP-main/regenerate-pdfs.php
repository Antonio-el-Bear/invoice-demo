<?php
/**
 * ============================================
 * REGENERATE MISSING INVOICE PDFs
 * ============================================
 * This script scans the database for invoices
 * and regenerates PDF files for any missing ones.
 * 
 * Usage: Run this file directly in your browser:
 * http://localhost:8000/regenerate-pdfs.php
 * ============================================
 */

// Include configuration and database connection
require('includes/config.php');

// Set default timezone
date_default_timezone_set(TIMEZONE);

// Include invoice PDF generation class
include('invoice.php');

// Connect to database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get all invoices from database
$query = "SELECT 
    i.invoice,
    i.invoice_date,
    i.invoice_due_date,
    i.subtotal,
    i.shipping,
    i.discount,
    i.vat,
    i.total,
    i.notes,
    i.invoice_type,
    i.status,
    c.name,
    c.email,
    c.address_1,
    c.address_2,
    c.town,
    c.county,
    c.postcode,
    c.phone,
    c.name_ship,
    c.address_1_ship,
    c.address_2_ship,
    c.town_ship,
    c.county_ship,
    c.postcode_ship
FROM invoices i
LEFT JOIN customers c ON i.invoice = c.invoice
ORDER BY i.invoice ASC";

$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

// Counter for regenerated PDFs
$regenerated = 0;
$skipped = 0;
$errors = 0;

echo "<html><head><title>Regenerate PDFs</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .skip { color: orange; }
    .error { color: red; }
    .summary { background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px; }
</style></head><body>";

echo "<h1>Invoice PDF Regeneration Tool</h1>";
echo "<hr>";

// Loop through all invoices
while ($invoice_data = $result->fetch_assoc()) {
    $invoice_number = $invoice_data['invoice'];
    $pdf_path = 'invoices/' . $invoice_number . '.pdf';
    
    // Check if PDF already exists
    if (file_exists($pdf_path)) {
        echo "<div class='skip'>⚠ Invoice #{$invoice_number}: PDF already exists, skipping...</div>";
        $skipped++;
        continue;
    }
    
    try {
        // Get invoice items for this invoice
        $items_query = "SELECT product, qty, price, discount, subtotal 
                       FROM invoice_items 
                       WHERE invoice = '".$invoice_number."'";
        $items_result = $mysqli->query($items_query);
        
        if (!$items_result) {
            throw new Exception("Failed to fetch invoice items: " . $mysqli->error);
        }
        
        // Create new invoice PDF instance
        $invoice = new invoicr("A4", CURRENCY, "en");
        
        // Set number formatting
        $invoice->setNumberFormat('.', ',');
        
        // Set logo
        $invoice->setLogo(COMPANY_LOGO_PDF, COMPANY_LOGO_WIDTH, COMPANY_LOGO_HEIGHT);
        
        // Set theme color
        $invoice->setColor(INVOICE_THEME);
        
        // Set type
        $invoice->setType($invoice_data['invoice_type']);
        
        // Set reference
        $invoice->setReference($invoice_number);
        
        // Set dates
        $invoice->setDate($invoice_data['invoice_date']);
        $invoice->setDue($invoice_data['invoice_due_date']);
        
        // Set from (company info)
        $invoice->setFrom(array(
            COMPANY_NAME,
            COMPANY_ADDRESS_1,
            COMPANY_ADDRESS_2,
            COMPANY_COUNTY,
            COMPANY_POSTCODE,
            COMPANY_NUMBER,
            COMPANY_VAT
        ));
        
        // Set to (customer info)
        $invoice->setTo(array(
            $invoice_data['name'],
            $invoice_data['address_1'],
            $invoice_data['address_2'],
            $invoice_data['town'],
            $invoice_data['county'],
            $invoice_data['postcode'],
            "Phone: " . $invoice_data['phone']
        ));
        
        // Ship to (if different)
        $invoice->shipTo(array(
            $invoice_data['name_ship'],
            $invoice_data['address_1_ship'],
            $invoice_data['address_2_ship'],
            $invoice_data['town_ship'],
            $invoice_data['county_ship'],
            $invoice_data['postcode_ship'],
            ''
        ));
        
        // Add invoice items
        while ($item = $items_result->fetch_assoc()) {
            $item_vat = 0;
            if (ENABLE_VAT == true) {
                $item_vat = (VAT_RATE / 100) * $item['subtotal'];
            }
            
            $invoice->addItem(
                $item['product'],
                '',
                $item['qty'],
                $item_vat,
                $item['price'],
                $item['subtotal'],
                $item['discount']
            );
        }
        
        // Add totals
        $invoice->addTotal("Total", $invoice_data['subtotal']);
        
        if (!empty($invoice_data['discount'])) {
            $invoice->addTotal("Discount", $invoice_data['discount']);
        }
        
        if (!empty($invoice_data['shipping'])) {
            $invoice->addTotal("Delivery", $invoice_data['shipping']);
        }
        
        if (ENABLE_VAT == true) {
            $invoice->addTotal("TAX/VAT " . VAT_RATE . "%", $invoice_data['vat']);
        }
        
        $invoice->addTotal("Total Due", $invoice_data['total'], true);
        
        // Add badge (status)
        $invoice->addBadge($invoice_data['status']);
        
        // Customer notes
        if (!empty($invoice_data['notes'])) {
            $invoice->addTitle("Customer Notes");
            $invoice->addParagraph($invoice_data['notes']);
        }
        
        // Payment information
        $invoice->addTitle("Payment information");
        $invoice->addParagraph(PAYMENT_DETAILS);
        
        // Footer note
        $invoice->setFooternote(FOOTER_NOTE);
        
        // Render the PDF to file
        $invoice->render($pdf_path, 'F');
        
        echo "<div class='success'>✓ Invoice #{$invoice_number}: PDF regenerated successfully</div>";
        $regenerated++;
        
    } catch (Exception $e) {
        echo "<div class='error'>✗ Invoice #{$invoice_number}: Error - " . $e->getMessage() . "</div>";
        $errors++;
    }
}

// Close database connection
$mysqli->close();

// Display summary
echo "<hr>";
echo "<div class='summary'>";
echo "<h2>Summary</h2>";
echo "<p><strong>Total Invoices Processed:</strong> " . ($regenerated + $skipped + $errors) . "</p>";
echo "<p class='success'><strong>PDFs Regenerated:</strong> $regenerated</p>";
echo "<p class='skip'><strong>Already Existed (Skipped):</strong> $skipped</p>";
echo "<p class='error'><strong>Errors:</strong> $errors</p>";
echo "</div>";

echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
