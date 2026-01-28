<?php
/**
 * ============================================
 * CLOUDUKO INVOICE MANAGEMENT SYSTEM
 * Configuration File
 * ============================================
 * 
 * This file contains all system-wide configuration settings.
 * Modify these values to customize the invoice system for your business.
 * 
 * IMPORTANT: Keep this file secure and never expose it publicly.
 */

// ============================================
// DEBUGGING SETTINGS
// ============================================
// Enable full error reporting during development
// Set to 0 or E_ERROR in production for security
ini_set('error_reporting', E_ALL);

// ============================================
// DATABASE CONNECTION SETTINGS
// ============================================
// These settings connect the application to your MySQL/MariaDB database
// Only define if not already defined (prevents redefinition errors)

if (!defined('DATABASE_HOST')) define('DATABASE_HOST', 'localhost');        // Database server address (usually 'localhost')
if (!defined('DATABASE_NAME')) define('DATABASE_NAME', 'invoicemgsys');     // Name of the database
if (!defined('DATABASE_USER')) define('DATABASE_USER', 'root');             // Database username
if (!defined('DATABASE_PASS')) define('DATABASE_PASS', '');                 // Database password (empty for local development)

// ============================================
// COMPANY BRANDING & LOGO
// ============================================
// Configure your company logo for invoices and login page

// Use absolute path for PDF generation, relative path for web display
if (!defined('COMPANY_LOGO')) define('COMPANY_LOGO', 'images/logo-01.png');     // Path to company logo image (relative for web)
if (!defined('COMPANY_LOGO_PDF')) define('COMPANY_LOGO_PDF', __DIR__ . '/../images/logo-01.png'); // Absolute path for PDF generation
if (!defined('COMPANY_LOGO_WIDTH')) define('COMPANY_LOGO_WIDTH', '300');        // Logo width in pixels
if (!defined('COMPANY_LOGO_HEIGHT')) define('COMPANY_LOGO_HEIGHT', '90');       // Logo height in pixels

// ============================================
// COMPANY INFORMATION
// ============================================
// This information appears on invoices and system pages

if (!defined('COMPANY_NAME')) define('COMPANY_NAME','CloudUko');                        // Your company name
if (!defined('COMPANY_ADDRESS_1')) define('COMPANY_ADDRESS_1','Your Business Address'); // Street address
if (!defined('COMPANY_ADDRESS_2')) define('COMPANY_ADDRESS_2','City, State, ZIP');      // City, State, Postal code
if (!defined('COMPANY_ADDRESS_3')) define('COMPANY_ADDRESS_3','Country');               // Country
if (!defined('COMPANY_COUNTY')) define('COMPANY_COUNTY','US');                          // Country code (2-letter)
if (!defined('COMPANY_POSTCODE')) define('COMPANY_POSTCODE','00000');                   // Postal/ZIP code

// Company registration details (appears on invoices)
if (!defined('COMPANY_NUMBER')) define('COMPANY_NUMBER','Company No: [Your Company Number]'); // Business registration number
if (!defined('COMPANY_VAT')) define('COMPANY_VAT', 'VAT No: [Your VAT Number]');             // Tax/VAT registration number

// ============================================
// EMAIL CONFIGURATION
// ============================================
// Settings for automated invoice emails

if (!defined('EMAIL_FROM')) define('EMAIL_FROM', 'invoices@clouduko.com');                              // Sender email address
if (!defined('EMAIL_NAME')) define('EMAIL_NAME', 'CloudUko Invoicing');                                 // Sender name
if (!defined('EMAIL_SUBJECT')) define('EMAIL_SUBJECT', 'Your Invoice from CloudUko');                   // Default email subject line

// Email body templates for different document types
if (!defined('EMAIL_BODY_INVOICE')) define('EMAIL_BODY_INVOICE', 'Thank you for your business. Please find your invoice attached.');  // Invoice email message
if (!defined('EMAIL_BODY_QUOTE')) define('EMAIL_BODY_QUOTE', 'Please find your quote from CloudUko attached.');                       // Quote email message
if (!defined('EMAIL_BODY_RECEIPT')) define('EMAIL_BODY_RECEIPT', 'Thank you for your payment. Your receipt is attached.');            // Receipt email message

// ============================================
// INVOICE SETTINGS
// ============================================
// Configure how invoices are numbered and displayed

if (!defined('INVOICE_PREFIX')) define('INVOICE_PREFIX', 'CU');                    // Prefix for invoice numbers (e.g., 'CU-1000'). Leave empty '' for no prefix
if (!defined('INVOICE_INITIAL_VALUE')) define('INVOICE_INITIAL_VALUE', '1000');    // Starting invoice number (increments from here)
if (!defined('INVOICE_THEME')) define('INVOICE_THEME', '#0066cc');                 // Color theme for PDF invoices (hexadecimal color code)

// ============================================
// REGIONAL SETTINGS
// ============================================
// Timezone and date format preferences

if (!defined('TIMEZONE')) define('TIMEZONE', 'America/Los_Angeles');               // System timezone. See: http://php.net/manual/en/timezones.php
if (!defined('DATE_FORMAT')) define('DATE_FORMAT', 'DD/MM/YYYY');                  // Date display format: 'DD/MM/YYYY' (European) or 'MM/DD/YYYY' (US)

// ============================================
// CURRENCY & TAX SETTINGS
// ============================================
// Configure currency symbol and tax/VAT handling

if (!defined('CURRENCY')) define('CURRENCY', 'R');                                 // Currency symbol (e.g., '$', '€', '£', 'R')
if (!defined('ENABLE_VAT')) define('ENABLE_VAT', true);                            // Enable or disable tax/VAT calculations
if (!defined('VAT_INCLUDED')) define('VAT_INCLUDED', false);                       // Is VAT included in prices (true) or added on top (false)?
if (!defined('VAT_RATE')) define('VAT_RATE', '10');                                // Tax/VAT percentage rate (10 = 10%)

// ============================================
// PAYMENT & FOOTER INFORMATION
// ============================================
// Banking details and footer message for invoices

if (!defined('PAYMENT_DETAILS')) define('PAYMENT_DETAILS', 
    'CloudUko<br>' .
    'Bank: [Your Bank Name]<br>' .
    'Account Number: [Your Account Number]<br>' .
    'Routing: [Your Routing Number]'
); // Payment instructions that appear on invoices

if (!defined('FOOTER_NOTE')) define('FOOTER_NOTE', 'Thank you for your business - CloudUko');  // Footer message on invoices

// ============================================
// DATABASE CONNECTION
// ============================================
// Establish connection to MySQL/MariaDB database
// This connection is used throughout the application

$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// Check if connection was successful
if ($mysqli->connect_error) {
    // Connection failed - show error message
    die('Database Connection Error (' . $mysqli->connect_errno . '): ' . $mysqli->connect_error);
}

// Set character encoding to UTF-8 for proper international character support
$mysqli->set_charset("utf8");

?>