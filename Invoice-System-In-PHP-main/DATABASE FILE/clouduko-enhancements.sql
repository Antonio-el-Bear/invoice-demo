-- CloudUko Enhancement SQL
-- Add payment tracking and reminder system tables

-- Alter invoices table to add payment tracking
ALTER TABLE `invoices` ADD COLUMN `amount_paid` DECIMAL(10,2) DEFAULT 0 AFTER `total`;
ALTER TABLE `invoices` ADD COLUMN `last_payment_date` VARCHAR(255) DEFAULT NULL AFTER `amount_paid`;
ALTER TABLE `invoices` ADD COLUMN `last_reminder_sent` VARCHAR(255) DEFAULT NULL AFTER `last_payment_date`;

-- Create payments table for payment history
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` varchar(255) NOT NULL,
  `payment_method` varchar(100) DEFAULT 'Manual',
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice` (`invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create reminders table for tracking sent reminders
CREATE TABLE IF NOT EXISTS `reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `reminder_type` varchar(50) DEFAULT 'overdue',
  `sent_date` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice` (`invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create invoice_templates table for custom email templates
CREATE TABLE IF NOT EXISTS `invoice_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `invoice_type` varchar(100),
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert default reminder email template
INSERT INTO `invoice_templates` (`template_name`, `invoice_type`, `subject`, `body`, `is_default`) VALUES
('Overdue Reminder', 'invoice', 'Payment Reminder - Invoice {INVOICE_NUMBER} is Overdue', 'Dear {CUSTOMER_NAME},\n\nThis is a friendly reminder that Invoice {INVOICE_NUMBER} dated {INVOICE_DATE} is now overdue.\n\nInvoice Details:\nAmount Due: {CURRENCY}{AMOUNT_DUE}\nDue Date: {DUE_DATE}\nDays Overdue: {DAYS_OVERDUE}\n\nPlease settle this invoice at your earliest convenience.\n\nThank you for your business!\n\nBest regards,\nCloudUko', 1);
