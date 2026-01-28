-- CloudUko Invoice System - Database Enhancement Script
-- 
-- This script adds payment tracking and reminder features to the invoices system.
-- 
-- IMPORTANT: Back up your database before running this script!
-- 
-- Usage:
-- 1. Open phpMyAdmin
-- 2. Select your database (invoicemgsys)
-- 3. Go to SQL tab
-- 4. Paste this entire script
-- 5. Click Go

-- Step 1: Add payment tracking columns to invoices table
ALTER TABLE invoices ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0 AFTER total;
ALTER TABLE invoices ADD COLUMN last_payment_date DATE NULL AFTER amount_paid;
ALTER TABLE invoices ADD COLUMN last_reminder_sent DATETIME NULL AFTER last_payment_date;

-- Step 2: Create payments tracking table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    notes LONGTEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice) REFERENCES invoices(invoice) ON DELETE CASCADE,
    INDEX idx_invoice (invoice),
    INDEX idx_payment_date (payment_date)
);

-- Step 3: Create reminders tracking table
CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice VARCHAR(50) NOT NULL,
    reminder_type VARCHAR(50) DEFAULT 'overdue',
    sent_to_email VARCHAR(100),
    sent_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice) REFERENCES invoices(invoice) ON DELETE CASCADE,
    INDEX idx_invoice (invoice),
    INDEX idx_sent_date (sent_date)
);

-- Step 4: Create email templates table
CREATE TABLE IF NOT EXISTS invoice_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    body LONGTEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_template_name (name)
);

-- Step 5: Insert default reminder email template
INSERT INTO invoice_templates (name, subject, body) VALUES 
('Overdue Reminder', 'Payment Reminder - Invoice {{invoice_number}}', 
'Dear {{customer_name}},

This is a friendly reminder that your invoice {{invoice_number}} dated {{invoice_date}} is now overdue.

INVOICE DETAILS:
Invoice Number: {{invoice_number}}
Amount Due: {{amount_due}}
Due Date: {{due_date}}
Days Overdue: {{days_overdue}}

Please arrange payment at your earliest convenience.

If payment has already been made, please disregard this notice.

Thank you for your business.

Best regards,
CloudUko Invoice Management System');

-- Step 6: Create an index for faster overdue invoice queries
CREATE INDEX IF NOT EXISTS idx_invoices_status_due_date ON invoices(status, invoice_due_date);

-- Step 7: Verify the changes
SELECT 'Database enhancement completed successfully!' as status;
SELECT 'New tables created:' as info;
SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('payments', 'reminders', 'invoice_templates');
