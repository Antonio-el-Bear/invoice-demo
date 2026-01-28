# PDF Regeneration Tool

## Problem
If you see errors like:
```
Not Found
The requested resource /invoices/3.pdf was not found on this server.
```

This means the PDF file is missing even though the invoice exists in the database.

## Solution

### Quick Fix - Regenerate Missing PDFs

1. **Make sure the system is running**:
   - Double-click `START-INVOICE-SYSTEM.bat`
   - Wait for browser to open

2. **Run the regeneration tool**:
   - Navigate to: http://localhost:8000/regenerate-pdfs.php
   - The tool will automatically:
     - Scan all invoices in the database
     - Check which PDFs are missing
     - Regenerate missing PDFs
     - Show you a summary

3. **Results**:
   - ✓ Green = PDF regenerated successfully
   - ⚠ Orange = PDF already exists (skipped)
   - ✗ Red = Error (check the message)

## When PDFs Get Deleted

PDFs can go missing if:
- Invoice was deleted but database operation failed partway
- `invoices/` folder was manually cleaned
- System crashed during invoice creation
- File permissions prevented PDF creation

## Prevention

The system automatically creates PDFs when you:
- Create a new invoice
- Update an existing invoice

If you ever need to regenerate PDFs:
1. Go to http://localhost:8000/regenerate-pdfs.php
2. Wait for completion
3. All invoices will have PDFs again

## Technical Details

**Script Location**: `regenerate-pdfs.php`

**What it does**:
```
1. Connects to database
2. Fetches all invoices with customer data
3. For each invoice:
   - Checks if PDF exists
   - If missing, generates new PDF
   - Saves to invoices/ folder
4. Shows summary report
```

**Safe to run multiple times** - Already existing PDFs are skipped.

---

**Created**: January 23, 2026  
**For**: CloudUko Invoice System
