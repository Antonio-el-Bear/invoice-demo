# CloudUko Invoice System - Complete Automation Guide

## 🤖 Overview
This system includes comprehensive automation features to streamline invoice management and reduce manual work. Here's everything that's automated and how to use it.

---

## ✅ Currently Implemented Automation

### 1. **Automatic Overdue Payment Reminders**
**Location:** `cron-send-reminders.php`  
**Status:** ✅ Fully Functional

**Features:**
- Automatically sends email reminders for overdue invoices
- Only sends reminders if last reminder was 3+ days ago (prevents spam)
- Logs all activity to `/logs` directory
- Skips invoices that are already paid
- Can be triggered manually or scheduled

**How to Use:**
1. **Manual Trigger:** Visit `http://localhost:8000/cron-send-reminders.php` or click "Send All Reminders" button on Pending Bills page
2. **Scheduled (Windows):** Set up Windows Task Scheduler
   - Open Task Scheduler → Create Basic Task
   - Name: "Invoice Payment Reminders"
   - Trigger: Daily at 9:00 AM
   - Action: Start a program
     - Program: `C:\Users\User\Documents\Software\xampp\php\php.exe`
     - Arguments: `C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice\cron-send-reminders.php`

3. **Scheduled (Linux Cron):**
   ```bash
   0 9 * * * /usr/bin/php /path/to/clouduko-invoice/cron-send-reminders.php
   ```

**View Logs:** Check `/logs/cron-reminders-YYYY-MM-DD.log`

---

### 2. **Individual Payment Reminders**
**Location:** Pending Bills page (`pending-bills.php`)  
**Status:** ✅ Just Added!

**Features:**
- Bell icon (🔔) button appears on overdue invoices only
- Click to send instant payment reminder to specific customer
- Confirms before sending
- Shows success/error message

**How to Use:**
1. Go to "Open Invoices" page
2. Find overdue invoices (marked in red)
3. Click the bell icon (🔔) button
4. Confirm the action
5. Customer receives payment reminder email immediately

---

### 3. **Automation Settings Dashboard**
**Location:** `automation.php`  
**Status:** ✅ Fully Functional

**Features:**
- View automation task status
- See last run logs
- Manual test run buttons
- Setup instructions for scheduling

**How to Access:**
- Click "Automation Settings" button on Pending Bills page
- Or navigate to: System → Automation & Tasks in sidebar

---

## 🔮 Recommended Additional Automation

### 4. **Pre-Due Date Reminders (Recommended)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Send friendly reminder 3 days before due date
- Prevent invoices from becoming overdue
- Improve cash flow timing

**Implementation Priority:** HIGH  
**Estimated Time:** 2-3 hours

---

### 5. **Automatic Invoice Generation (Recommended)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Generate recurring invoices automatically (monthly, quarterly, etc.)
- Perfect for subscription/retainer clients
- Set once, forget forever

**Implementation Priority:** MEDIUM  
**Estimated Time:** 4-5 hours

---

### 6. **Auto-Status Updates (Recommended)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Automatically mark invoices as "Overdue" when past due date
- Send notification to admin when invoice becomes overdue
- Auto-close very old invoices (optional)

**Implementation Priority:** MEDIUM  
**Estimated Time:** 2 hours

---

### 7. **Payment Confirmation Emails (Easy Win)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Automatically email customer when payment is recorded
- Include receipt/thank you message
- Update customer records

**Implementation Priority:** HIGH (Easy to implement)  
**Estimated Time:** 1 hour

---

### 8. **Weekly/Monthly Reports (Nice to Have)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Auto-generate and email weekly/monthly revenue reports
- Show: total invoiced, total paid, outstanding balance
- PDF attachment with detailed breakdown

**Implementation Priority:** LOW  
**Estimated Time:** 3-4 hours

---

### 9. **Customer Portal Auto-Notifications (Advanced)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Notify customers when new invoice is generated
- Send link to customer portal (if implemented)
- Track when customer views invoice

**Implementation Priority:** LOW (requires customer portal first)  
**Estimated Time:** 6-8 hours

---

### 10. **Smart Reminder Escalation (Advanced)**
**Status:** ⏳ Not Yet Implemented

**What It Would Do:**
- Day 1 overdue: Polite reminder
- Day 7 overdue: Firmer reminder
- Day 14 overdue: Final notice
- Day 30 overdue: Alert admin for follow-up

**Implementation Priority:** MEDIUM  
**Estimated Time:** 3-4 hours

---

## 📊 Current Automation Dashboard

| Feature | Status | Trigger | Frequency |
|---------|--------|---------|-----------|
| Overdue Reminders | ✅ Active | Manual/Scheduled | Daily (recommended) |
| Individual Reminders | ✅ Active | Manual | On-demand |
| Pre-Due Reminders | ⏳ Pending | N/A | N/A |
| Recurring Invoices | ⏳ Pending | N/A | N/A |
| Payment Confirmations | ⏳ Pending | N/A | N/A |
| Auto Reports | ⏳ Pending | N/A | N/A |

---

## 🎯 Quick Start Checklist

- [x] Email reminders for overdue invoices configured
- [x] Individual reminder buttons added to pending bills
- [x] Automation settings page accessible
- [ ] Schedule daily automatic reminders (Task Scheduler or Cron)
- [ ] Test reminder emails with real invoice
- [ ] Review logs weekly to ensure reminders are sending

---

## 🛠 Technical Details

### Files Related to Automation:
- `cron-send-reminders.php` - Main scheduler for automatic reminders
- `automation.php` - Dashboard for automation settings
- `pending-bills.php` - Displays pending invoices with reminder buttons
- `response.php` - Backend API for sending reminders (action: send_reminder)
- `enhanced-functions.php` - Helper functions for email sending
- `/logs/` - Log files directory (auto-created)

### Database Tables:
- `invoices` - Contains `last_reminder_sent` field to track reminder history
- `customers` - Email addresses for sending reminders

---

## 📧 Email Templates

Reminder emails use templates from `enhanced-functions.php`:
- Professional tone
- Include invoice details (number, amount, due date)
- Link to view/pay invoice
- Company branding (logo from config)

---

## 🔐 Security Notes

- Reminders only sent to verified customer email addresses in database
- 3-day cooldown prevents spam/abuse
- All actions logged for audit trail
- Admin can review logs at any time

---

## 💡 Pro Tips

1. **Test First:** Use "Run Now" button on automation page to test before scheduling
2. **Check Logs:** Always review logs after first scheduled run
3. **Timing Matters:** 9:00 AM is ideal - customers check email in morning
4. **Don't Over-Remind:** 3-day cooldown is optimal (more = annoying)
5. **Monitor Success:** Check if reminders improve payment rates

---

## 🆘 Troubleshooting

**Reminders not sending?**
- Check email configuration in `includes/config.php`
- Verify customer email addresses are valid
- Check `/logs/` directory for error messages
- Ensure PHP `mail()` function works or SMTP is configured

**Scheduled task not running?**
- Verify Task Scheduler/Cron is active
- Check task logs in Task Scheduler
- Ensure PHP path is correct
- Test manual run first

**Customer not receiving emails?**
- Check spam/junk folder
- Verify email address in customer record
- Test email manually via "Email" button on invoice page

---

## 📞 Next Steps

Ready to implement more automation? Contact your developer or:
1. Prioritize features from "Recommended" list above
2. Estimate time/budget for implementation
3. Test new features thoroughly before going live
4. Update this document when new features are added

---

**Last Updated:** January 26, 2026  
**System Version:** CloudUko Invoice System v2.0  
**Automation Status:** Level 2 (Basic + Individual Reminders Active)
