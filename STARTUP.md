# 🚀 STARTUP GUIDE - CloudUko Invoice System

## Quick Start (60 seconds)

### Option 1: Double-Click Script (Easiest)
1. Navigate to: `C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice\`
2. **Double-click:** `START-INVOICE-SYSTEM.ps1`
3. System launches automatically!

### Option 2: PowerShell Command
```powershell
cd "C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice"
.\START-INVOICE-SYSTEM.ps1
```

---

## 📋 What The Startup Script Does

✅ Starts MariaDB database server  
✅ Configures PHP environment  
✅ Launches PHP web server  
✅ Opens your browser automatically  

---

## 🌐 Access Your System

**URL:** http://localhost:8000

**Default Login:**
- Username: `admin`
- Password: `Password@123`

⚠️ **Important:** Change your password after first login!

---

## ⏹️ How to Stop the System

When you're finished, run:
```powershell
.\STOP-INVOICE-SYSTEM.ps1
```

Or simply close the PHP server window.

---

## ✅ System is Running When You See:

1. **PHP Server Window** shows:
   ```
   [Fri Jan 23 10:13:00 2026] PHP 8.3.30 Development Server started
   http://localhost:8000
   ```

2. **Browser Opens** to login page with CloudUko logo

3. **No Error Messages** in terminal windows

---

## 🆘 Troubleshooting

### Can't find the script?
Located at:
- `C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice\START-INVOICE-SYSTEM.ps1`
- OR: `C:\Users\User\Documents\cloud uko\apps\Invoice-System-In-PHP-main\START-INVOICE-SYSTEM.ps1`

### Port 8000 already in use?
Edit `START-INVOICE-SYSTEM.ps1` and change:
```powershell
php -S localhost:8000
```
To a different port (e.g., 8080):
```powershell
php -S localhost:8080
```

### Database won't start?
Check that MariaDB is installed properly:
```powershell
Test-Path "C:\Users\User\Documents\Software\xampp\mysql\bin\mysqld.exe"
```

### Browser doesn't open?
Manually navigate to: **http://localhost:8000**

---

## 📝 System Features

Once logged in, you can:
- ✅ Create professional invoices
- ✅ Manage customers
- ✅ Track payments
- ✅ Generate PDF invoices
- ✅ View reports
- ✅ Manage products/services

---

## 🔐 Login Credentials

```
URL:      http://localhost:8000
Username: admin
Password: Password@123
```

---

## 📁 Installation Locations

**Web Files:**
```
C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice\
```

**Database:**
```
localhost (MariaDB)
Database: invoicemgsys
User: root
```

**Source Files:**
```
C:\Users\User\Documents\cloud uko\apps\Invoice-System-In-PHP-main\
```

---

## 🎯 First Time Setup

1. **Start System**
   ```powershell
   .\START-INVOICE-SYSTEM.ps1
   ```

2. **Login**
   - Username: admin
   - Password: Password@123

3. **Change Password** (recommended)
   - Click your username in top right
   - Select "Change Password"

4. **Update Company Info** (optional)
   - Go to Settings
   - Update your company details

5. **Add Your Logo** (optional)
   - Replace `images/logo-01.png` with your company logo

---

## 💡 Pro Tips

### Customize Configuration
Edit: `includes/config.php`
- Change company name
- Set currency symbol
- Adjust tax rate
- Customize invoice prefix

### Backup Your Data
```powershell
cd "C:\Users\User\Documents\Software\xampp\mysql\bin"
.\mysqldump.exe -u root invoicemgsys > backup.sql
```

### Keep Terminal Window Open
Don't close the PHP server window while using the system!

---

## ❌ Stopping the System

**Option 1: Run Stop Script**
```powershell
.\STOP-INVOICE-SYSTEM.ps1
```

**Option 2: Close PHP Server Window**
- Closes the web server immediately

**Option 3: PowerShell Command**
```powershell
Get-Process mysqld, php | Stop-Process
```

---

## 📞 Support Files

For more information, see:
- **SYSTEM-READY.md** - Complete user guide
- **CODE-REVIEW-COMPLETE.md** - Technical documentation
- **includes/config.php** - Configuration options

---

## ✨ You're All Set!

Your CloudUko Invoice System is ready to use.

**Start now with:**
```powershell
.\START-INVOICE-SYSTEM.ps1
```

**Happy Invoicing! 🧾**

---

*Last Updated: January 23, 2026*  
*System Version: CloudUko v1.0 - Enhanced*
