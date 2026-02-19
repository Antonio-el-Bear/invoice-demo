<?phpInvoice System - PHP-Based Invoicing & Management Solution

namespace Tests\Feature; invoicing system built with **PHP, MySQL, Bootstrap, and JavaScript**. This application helps businesses manage invoices, clients, products, payments, and generate detailed financial reports.

use App\Jobs\Util\WebhookHandler;
use App\Models\Company;
use App\Models\Invoice;-is-this)
use App\Models\User;y-features)
use App\Models\Webhook;(#system-requirements)
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;-to-use)
- [File Structure](#file-structure)
class InvoiceRestoreWebhookTest extends TestCase
{ [Key Features Explained](#key-features-explained)
    public function test_restore_dispatches_webhook_when_subscription_exists(): void
    {
        // Arrange
        Bus::fake();
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();
        $invoice = Invoice::factory()->for($company)->for($user)->create([edium-sized businesses. It provides:
            'is_deleted' => true,
            'deleted_at' => now(),te, edit, send, and track invoices
        ]);nt Management** - Manage customer information and contact details
- 📦 **Product/Service Catalog** - Maintain products and services with pricing
        Webhook::factory()->create([nd track payments against invoices
            'company_id' => $company->id,thly income, customer summaries, and overdue analysis
            'event_id' => Webhook::EVENT_RESTORE_INVOICE,ure login
        ]);l Notifications** - Send invoices and payment reminders via email
- 🤖 **Automation** - Automated daily reminders for overdue invoices
        // Acty** - Bcrypt password hashing and SQL injection prevention
        $invoice->restore();
---
        // Assert
        Bus::assertDispatched(WebhookHandler::class, function ($job) use ($invoice) {
            return $job->event_id === Webhook::EVENT_RESTORE_INVOICE
                && $job->entity->id === $invoice->id;
        });-------------|
    }ser Authentication** | Secure login with bcrypt password hashing and session management |
} **Multi-User Support** | Admin and user roles with different permission levels |
| **Client Management** | Add, edit, delete, and organize client/customer information |
DB_CONNECTION=mysqln** | Create invoices with custom items, taxes, discounts, and calculations |
DB_HOST=127.0.0.1ing** | Monitor invoice status (Draft, Sent, Paid, Overdue) |
DB_PORT=3306Recording** | Record partial or full payments with dates and references |
DB_DATABASE=securedoc| Generate and download invoices as PDF files |
DB_USERNAME=rootation** | Send invoices directly via email with PDF attachments |
DB_PASSWORD=Tracking** | Automatic identification and tracking of overdue invoices |
| **Payment Reminders** | Send automated email reminders for unpaid/overdue invoices |
cd "C:\Users\User\Documents\cloud uko\apps\document-reader\backend"verdue analysis |
$env:Path = "C:\Users\User\Documents\Software\xampp\php;$env:Path"; php artisan migrate
| **Audit Logging** | Track all system activities for security and compliance |
| **Responsive Design** | Works on desktop, tablet, and mobile devices |

---

## 💻 System Requirements

### Server Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache with .htaccess support (or Nginx)
- **PHP Extensions**:
  - `mysql` or `mysqli`
  - `json`
  - `gd` (for image handling)
  - `openssl` (for HTTPS)

### Browser Requirements
- Modern browsers (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- Cookies enabled for session management

### Optional (for email functionality)
- SMTP server access
- PHPMailer library (included in `vendor/` directory)

---

## 🚀 Installation

### Step 1: Extract Files
```bash
Extract the project to your web server directory:
C:\xampp\htdocs\invoice-system\    (Windows)
/var/www/html/invoice-system/      (Linux)
```

### Step 2: Create Database
1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Create a new database named `invoicemgsys`
3. Import the SQL file:
   - Right-click database → Import
   - Select: `Invoice-System-In-PHP-main/DATABASE FILE/invoicemgsys.sql`
   - Click Import

### Step 3: Configure Database Connection
Edit `Invoice-System-In-PHP-main/functions.php`:
```php
// Find and update these lines:
$dbhost = "localhost";     // Your database host
$dbuser = "root";          // Your database username
$dbpass = "";              // Your database password
$db = "invoicemgsys";      // Your database name
```

### Step 4: Setup Email (Optional)
Edit `Invoice-System-In-PHP-main/enhanced-functions.php` (search for `sendOverdueReminder`):
```php
$mail->Host = 'your-smtp-server.com';
$mail->Username = 'your-email@example.com';
$mail->Password = 'your-password';
```

### Step 5: Run the System
1. Start Apache and MySQL (XAMPP, WAMP, or your server)
2. Navigate to: `http://localhost/invoice-system/`
3. You're ready to go!

---

## 📖 How To Use

### 1️⃣ Login
- Navigate to the login page
- Use default credentials (see below)
- Click "Login"

### 2️⃣ Dashboard
- View overview of invoices, payments, and system status
- See upcoming due invoices
- Monitor overdue invoices
- Quick access to key functions

### 3️⃣ Manage Customers/Clients
1. Go to **Customers** menu
2. Click **Add Customer** button
3. Fill in customer details:
   - Name
   - Email
   - Phone
   - Address
   - City/State/Postal Code
4. Click **Save**

### 4️⃣ Create Invoice
1. Go to **Invoices** → **Create Invoice**
2. Select a customer
3. Add line items:
   - Select product/service
   - Enter quantity
   - Unit price auto-fills
4. Add taxes (if applicable)
5. Apply discounts (if applicable)
6. Click **Create Invoice**

### 5️⃣ Send Invoice
1. Go to **Invoices** → **View All**
2. Find the invoice
3. Click the **Email Icon** (✉️) next to the invoice
4. Invoice PDF is sent to customer's email

### 6️⃣ Record Payment
1. Open an invoice
2. Click **Record Payment** button
3. Enter payment amount and date
4. Click **Save Payment**
5. Invoice status automatically updates

### 7️⃣ View Reports
1. Go to **Invoices** → **Reports**
2. Select report type:
   - Monthly Income Report
   - Customer Summary Report
   - Overdue Analysis Report
3. Select month/year if needed
4. View or download as CSV

### 8️⃣ Track Overdue Invoices
1. Dashboard shows overdue invoices automatically
2. Click **Send Reminder** to email customer
3. System prevents duplicate reminders within 3 days

### 9️⃣ Export Data
1. Go to relevant section (Invoices, Customers, Products)
2. Click **Download CSV** button
3. Open in Excel or Google Sheets

---

## 📁 File Structure

```
invoice-system/
├── Invoice-System-In-PHP-main/        # Main application folder
│   ├── index.php                      # Dashboard/Home page
│   ├── login.php                      # Login page
│   ├── logout.php                     # Logout handler
│   ├── functions.php                  # Core database functions
│   ├── enhanced-functions.php         # Advanced features (payments, reports)
│   ├── security-functions.php         # Security & encryption functions
│   ├── session.php                    # Session management
│   │
│   ├── Invoices/
│   │   ├── invoice-create.php         # Create new invoice
│   │   ├── invoice-list.php           # View all invoices
│   │   ├── invoice-edit.php           # Edit invoice
│   │   ├── invoice.php                # Invoice details/view
│   │
│   ├── Customers/
│   │   ├── customer-add.php           # Add new customer
│   │   ├── customer-list.php          # View all customers
│   │   ├── customer-edit.php          # Edit customer
│   │
│   ├── Products/
│   │   ├── product-add.php            # Add new product
│   │   ├── product-list.php           # View all products
│   │   ├── product-edit.php           # Edit product
│   │
│   ├── Reports/
│   │   ├── reports.php                # All reports (income, customer, overdue)
│   │   ├── payments-list.php          # Payment tracking
│   │   ├── reminders-list.php         # Email reminders history
│   │
│   ├── Automation/
│   │   ├── automation.php             # Automated tasks
│   │   ├── cron-send-reminders.php    # Daily reminder scheduler
│   │   ├── migrate-passwords.php      # Password migration utility
│   │
│   ├── css/                           # Stylesheets (Bootstrap, custom)
│   ├── js/                            # JavaScript files
│   ├── images/                        # Images and logo
│   ├── fonts/                         # Font files
│   ├── downloads/                     # Generated PDF/CSV files
│   │
│   ├── DATABASE FILE/
│   │   ├── invoicemgsys.sql          # Database structure & data
│   │   └── clouduko-enhancements.sql # Additional features
│   │
│   └── Documentation/
│       ├── README.md
│       ├── QUICK-START.md
│       ├── INSTALLATION-STEPS.md
│       └── Other guides...
│
├── vendor/                            # Composer dependencies (PHPMailer, etc)
├── tests/                             # Unit tests
└── composer.json                      # PHP package manager config
```

---

## 🔑 Default Credentials

After first installation, use these credentials to login:

| Type | Value |
|------|-------|
| **Username** | `admin` |
| **Password** | `admin123` |
| **Role** | Admin |

⚠️ **IMPORTANT**: Change the default password immediately after first login!

---

## 🎓 Key Features Explained

### Dashboard
- Shows quick summary of system status
- Displays recent invoices
- Shows overdue invoices requiring attention
- Quick action buttons for common tasks

### Invoice Management
- **Create**: Design invoices with items, taxes, discounts
- **Edit**: Modify draft invoices before sending
- **Send**: Email invoices as PDF to customers
- **Track**: Monitor status (Draft → Sent → Paid)
- **View**: See all transaction details

### Payment Tracking
- Record partial or full payments
- Automatic invoice status updates
- Payment history per invoice
- Tax report calculations

### Financial Reports
- **Monthly Income**: Revenue by month
- **Customer Summary**: Total sales per customer
- **Overdue Analysis**: List of unpaid invoices by age

### Email & Reminders
- Send invoices directly to customer email
- Automated reminders for overdue invoices
- Reminder history tracking
- Prevents duplicate reminders

### Security Features
- **Bcrypt Password Hashing**: Industry-standard encryption
- **SQL Injection Prevention**: Prepared statements
- **Session Management**: Secure session handling
- **CSRF Protection**: Token-based protection
- **Audit Logging**: Track all user actions

---

## 🆘 Troubleshooting

### Issue: "Database Connection Error"
**Solution:**
1. Check database credentials in `functions.php`
2. Ensure MySQL is running
3. Verify database exists: `invoicemgsys`

### Issue: "Login Not Working"
**Solution:**
1. Clear browser cookies
2. Check session.php is in correct location
3. Verify PHP session support is enabled

### Issue: "Can't Send Emails"
**Solution:**
1. Verify SMTP settings in `enhanced-functions.php`
2. Check email credentials are correct
3. Disable email and use manual sending temporarily

### Issue: "Blank Pages / Errors"
**Solution:**
1. Enable error reporting in PHP:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check PHP version (minimum 8.0)
3. Check file permissions (should be 755/644)

### Issue: "PDF Generation Not Working"
**Solution:**
1. Verify `gd` extension is enabled in PHP
2. Check `downloads/` folder exists and is writable
3. Ensure temporary directory has write permissions

### Issue: "CSV Export Not Working"
**Solution:**
1. Check server can write to `downloads/` folder
2. Verify PHP `fopen` and file functions are enabled
3. Check browser download settings

---

## 📞 Support & Contributions

- **Issues**: Report bugs on GitHub
- **Suggestions**: Create feature requests
- **Contributions**: Submit pull requests
- **Security**: Report vulnerabilities responsibly

---

## 📄 License

This project is provided as-is for educational and business use.

---

## 🎉 What's New?

### Latest Features Added
✅ Payment recording system  
✅ Automated reminder scheduler  
✅ Financial reports module  
✅ Enhanced security with bcrypt  
✅ Audit logging system  
✅ Email integration  
✅ CSV export functionality  
✅ Responsive mobile design  

---

## 🚀 Quick Commands

```bash
# Start the system (XAMPP)
xampp\apache_start.bat
xampp\mysql_start.bat

# Stop the system
xampp\apache_stop.bat
xampp\mysql_stop.bat

# Access the system
http://localhost/invoice-system/

# Check database
Open phpMyAdmin: http://localhost/phpmyadmin
```

---

## 📚 Additional Resources

- [QUICK-START.md](Invoice-System-In-PHP-main/QUICK-START.md) - Feature summary
- [INSTALLATION-STEPS.md](Invoice-System-In-PHP-main/INSTALLATION-STEPS.md) - Detailed setup guide
- [DATABASE SCHEMA](Invoice-System-In-PHP-main/DATABASE%20FILE/invoicemgsys.sql) - Database structure

---

**Version**: 2.0  
**Last Updated**: January 2026  
**Status**: ✅ Production Ready

Happy invoicing! 📊💼

