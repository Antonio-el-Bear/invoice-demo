# CloudUko Invoice System - Quick Installation Guide

## ⚠️ XAMPP Not Detected

You need to install XAMPP to run this PHP application.

## 📥 Step-by-Step Installation

### 1. Download XAMPP

**Windows Download Link:**
https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe/download

Or visit: https://www.apachefriends.org/download.html

### 2. Install XAMPP

1. Run the installer you just downloaded
2. **Important:** Install to `C:\xampp` (default location)
3. Select these components (at minimum):
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP
   - ✅ phpMyAdmin
4. Click "Next" through the installation
5. Complete the installation

### 3. Copy Project to XAMPP Folder

After XAMPP is installed, run this command in PowerShell:

```powershell
Copy-Item "C:\Users\User\Documents\cloud uko\apps\Invoice-System-In-PHP-main\Invoice-System-In-PHP-main\*" -Destination "C:\xampp\htdocs\clouduko-invoice" -Recurse -Force
```

Or manually:
- Navigate to: `C:\Users\User\Documents\cloud uko\apps\Invoice-System-In-PHP-main\Invoice-System-In-PHP-main`
- Copy all files inside this folder
- Paste into: `C:\xampp\htdocs\clouduko-invoice` (create the clouduko-invoice folder)

### 4. Start XAMPP

1. Search for "XAMPP Control Panel" in Windows Start menu
2. Click to open it
3. Click **Start** button next to **Apache** - should turn green
4. Click **Start** button next to **MySQL** - should turn green

### 5. Create Database

1. Open browser: http://localhost/phpmyadmin/
2. Click "**New**" in left sidebar
3. Enter database name: `invoicemgsys`
4. Select Collation: `utf8mb4_general_ci`
5. Click "**Create**"

### 6. Import Database Structure

1. In phpMyAdmin, click on the `invoicemgsys` database (left sidebar)
2. Click the "**Import**" tab at the top
3. Click "**Choose File**"
4. Navigate to: `C:\xampp\htdocs\clouduko-invoice\DATABASE FILE\invoicemgsys.sql`
5. Click "**Go**" at the bottom
6. Wait for success message: "Import has been successfully finished"

### 7. Access Your CloudUko Invoice System

**URL:** http://localhost/clouduko-invoice/

**Default Login:**
- Username: `admin`
- Password: `Password@123`

## 🎉 You're Ready!

Once logged in, you can:
- Create invoices
- Manage customers
- Track payments
- Generate PDF invoices
- Manage products/services

## 🔧 Troubleshooting

### Apache won't start
- Port 80 might be in use by another program (Skype, IIS, etc.)
- In XAMPP Control Panel, click "Config" next to Apache
- Change port from 80 to 8080
- Access as: http://localhost:8080/clouduko-invoice/

### MySQL won't start
- Port 3306 might be in use
- In XAMPP Control Panel, click "Config" next to MySQL
- Change port if needed

### Blank page after login
- Make sure database was imported successfully
- Check that all files were copied correctly

---

**Need Help?** Make sure:
✅ XAMPP is installed
✅ Apache & MySQL are running (green in XAMPP Control Panel)
✅ Database `invoicemgsys` is created and imported
✅ Files are in `C:\xampp\htdocs\clouduko-invoice\`
