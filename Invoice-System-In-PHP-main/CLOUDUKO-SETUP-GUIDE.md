# CloudUko Invoice Management System - Setup Guide

## 📋 Overview
This is a complete invoice management system customized for CloudUko. It allows you to create invoices, manage customers, track payments, and generate PDF invoices.

## 🔧 Prerequisites
- **XAMPP** (or similar: WAMP, MAMP) installed on your computer
- **PHP 5.6 or newer** (comes with XAMPP)
- **MySQL database** (comes with XAMPP)
- A web browser (Chrome, Firefox, Edge, etc.)

## 📦 Installation Steps

### Step 1: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** service (click "Start" button)
3. Start **MySQL** service (click "Start" button)
4. Both should show green "Running" status

### Step 2: Create Database
1. Open your web browser
2. Go to: `http://localhost/phpmyadmin/`
3. Click on "**New**" in the left sidebar
4. Database name: `invoicemgsys`
5. Collation: `utf8mb4_general_ci`
6. Click "**Create**"

### Step 3: Import Database
1. In phpMyAdmin, click on the newly created `invoicemgsys` database
2. Click on the "**Import**" tab
3. Click "**Choose File**"
4. Navigate to: `Invoice-System-In-PHP-main/DATABASE FILE/invoicemgsys.sql`
5. Click "**Go**" at the bottom
6. Wait for "Import has been successfully finished" message

### Step 4: Access the System
1. Open your web browser
2. Go to: `http://localhost/Invoice-System-In-PHP-main/Invoice-System-In-PHP-main/`
3. You should see the CloudUko login page

### Step 5: Login
**Default Admin Credentials:**
- **Username:** `admin`
- **Password:** `Password@123`

⚠️ **IMPORTANT:** Change the admin password after first login for security!

## 🎨 Customization Completed

The following has already been customized for CloudUko:

✅ **Company Name:** Changed to "CloudUko"
✅ **Invoice Prefix:** Changed to "CU" (invoices will be: CU-1000, CU-1001, etc.)
✅ **Invoice Theme Color:** Changed to blue (#0066cc)
✅ **Page Title:** Updated to "CloudUko - Invoice Management System"
✅ **Email Settings:** Updated for CloudUko branding
✅ **Footer:** Changed to CloudUko

## 🖼️ Add Your Logo

1. Create or obtain your CloudUko logo (PNG format recommended)
2. Resize to approximately 300x90 pixels
3. Save as: `images/clouduko-logo.png`
4. The logo will appear on the login page and invoices

**Temporary Solution:** Until you have your logo, copy any PNG image to `images/clouduko-logo.png`

## 📝 Additional Configuration Needed

Edit the file: `includes/config.php` to update:

1. **Company Address** (Lines 15-19):
   ```php
   define('COMPANY_ADDRESS_1','Your Business Address');
   define('COMPANY_ADDRESS_2','City, State, ZIP');
   define('COMPANY_ADDRESS_3','Country');
   define('COMPANY_POSTCODE','00000');
   ```

2. **Company Registration Numbers** (Lines 21-22):
   ```php
   define('COMPANY_NUMBER','Company No: [Your Company Number]');
   define('COMPANY_VAT', 'VAT No: [Your VAT Number]');
   ```

3. **Email Configuration** (Lines 24-30):
   ```php
   define('EMAIL_FROM', 'invoices@clouduko.com');
   ```

4. **Timezone** (Line 36): Change if you're not in Pacific Time
   ```php
   define('TIMEZONE', 'America/Los_Angeles');
   ```
   See: http://php.net/manual/en/timezones.php

5. **Payment Details** (Line 45):
   ```php
   define('PAYMENT_DETAILS', 'CloudUko<br>Bank: [Your Bank]<br>Account: [Your Account Number]');
   ```

## 🚀 Features Available

- ✅ Customer Management (Add, Edit, List)
- ✅ Product/Service Management
- ✅ Invoice Creation & Editing
- ✅ Invoice PDF Generation
- ✅ Invoice Status Tracking (Open, Paid)
- ✅ User Management
- ✅ Dashboard with Statistics
- ✅ Invoice Export to CSV
- ✅ Tax/VAT Calculation
- ✅ Discount Management

## 🔐 Security Recommendations

1. **Change Default Password Immediately**
2. **Update Email Configuration** for password resets
3. **Keep PHP and MySQL Updated**
4. **Use Strong Passwords** for all users
5. **Regular Backups** of the database

## 📁 Important Files

- `includes/config.php` - Main configuration file
- `DATABASE FILE/invoicemgsys.sql` - Database structure
- `images/` - Logo and image files
- `invoices/` - Generated invoice PDFs

## 🆘 Troubleshooting

### Can't access localhost
- Make sure Apache is running in XAMPP
- Try: `http://127.0.0.1/Invoice-System-In-PHP-main/Invoice-System-In-PHP-main/`

### Database connection error
- Verify MySQL is running in XAMPP
- Check database name is `invoicemgsys`
- Verify database was imported successfully

### Blank page after login
- Check for PHP errors in XAMPP logs
- Ensure session.php file exists

### Can't create invoices
- Check write permissions on `invoices/` folder
- Verify database tables were created

## 📞 Support

For issues specific to CloudUko customization, please contact your system administrator.

## 📄 License

This is a customized version of an open-source invoice management system.
Use for educational and business purposes.

---

**Version:** 1.0 - CloudUko Edition
**Date:** January 2026
**Customized for:** CloudUko
