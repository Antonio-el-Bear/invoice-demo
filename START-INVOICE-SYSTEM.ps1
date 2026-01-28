# ============================================
# CLOUDUKO INVOICE SYSTEM - OFFLINE LAUNCHER
# ============================================
#
# This script starts the CloudUko Invoice Management System
# for offline use without requiring XAMPP Control Panel.
#
# What it does:
# 1. Starts MariaDB/MySQL database server
# 2. Refreshes environment PATH for PHP
# 3. Launches PHP built-in web server
# 4. Opens your default web browser to the login page
#
# Requirements:
# - PHP 8.x installed (via winget)
# - MariaDB/MySQL installed (via XAMPP)
# - Database 'invoicemgsys' already created and imported
#
# ============================================

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "CLOUDUKO INVOICE SYSTEM - OFFLINE LAUNCHER" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ============================================
# STEP 1: START MARIADB/MYSQL DATABASE
# ============================================
Write-Host "[1/4] Starting MariaDB/MySQL Database Server..." -ForegroundColor Yellow

# Path to MariaDB executable in XAMPP installation
$mysqlPath = "C:\Users\User\Documents\Software\xampp\mysql\bin\mysqld.exe"

# Check if MariaDB is already running
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($mysqlRunning) {
    Write-Host "      ✓ MariaDB is already running (PID: $($mysqlRunning.Id))" -ForegroundColor Green
} else {
    # Check if mysqld.exe exists
    if (Test-Path $mysqlPath) {
        # Start MariaDB in background (console mode for visibility)
        Start-Process -FilePath $mysqlPath -ArgumentList "--console" -WindowStyle Minimized
        Write-Host "      ✓ MariaDB started successfully" -ForegroundColor Green
        
        # Wait for database to fully initialize (5 seconds)
        Write-Host "      ⏳ Waiting for database to initialize..." -ForegroundColor Gray
        Start-Sleep -Seconds 5
    } else {
        Write-Host "      ✗ ERROR: MariaDB not found at: $mysqlPath" -ForegroundColor Red
        Write-Host "      Please check your XAMPP installation" -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
}

Write-Host ""

# ============================================
# STEP 2: REFRESH PHP ENVIRONMENT PATH
# ============================================
Write-Host "[2/4] Configuring PHP Environment..." -ForegroundColor Yellow

# Refresh PATH to include newly installed PHP
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Verify PHP is accessible
$phpVersion = php -v 2>&1 | Select-Object -First 1
if ($phpVersion -match "PHP") {
    Write-Host "      ✓ PHP is ready: $phpVersion" -ForegroundColor Green
} else {
    Write-Host "      ✗ ERROR: PHP not found in PATH" -ForegroundColor Red
    Write-Host "      Please ensure PHP is installed via winget" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# ============================================
# STEP 3: START PHP WEB SERVER
# ============================================
Write-Host "[3/4] Starting PHP Web Server..." -ForegroundColor Yellow

# Path to invoice system (adjust if your installation is different)
$webRoot = "C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice"

# Verify web root exists
if (Test-Path $webRoot) {
    Write-Host "      Web Root: $webRoot" -ForegroundColor Gray
    
    # Start PHP built-in server on localhost:8000
    # Server runs in background and logs to console
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$webRoot'; php -S localhost:8000" -WindowStyle Normal
    
    Write-Host "      ✓ PHP Server started on http://localhost:8000" -ForegroundColor Green
    
    # Wait for server to start (3 seconds)
    Write-Host "      ⏳ Waiting for web server to start..." -ForegroundColor Gray
    Start-Sleep -Seconds 3
} else {
    Write-Host "      ✗ ERROR: Web root not found at: $webRoot" -ForegroundColor Red
    Write-Host "      Please verify installation path" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# ============================================
# STEP 4: OPEN WEB BROWSER
# ============================================
Write-Host "[4/4] Launching CloudUko Invoice System..." -ForegroundColor Yellow

# Open default web browser to login page
Start-Process "http://localhost:8000"

Write-Host "      ✓ Browser launched" -ForegroundColor Green
Write-Host ""

# ============================================
# SUCCESS MESSAGE
# ============================================
Write-Host "============================================" -ForegroundColor Green
Write-Host "✓ SYSTEM STARTED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "Access your invoice system at:" -ForegroundColor White
Write-Host "  → http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Default Login Credentials:" -ForegroundColor White
Write-Host "  Username: admin" -ForegroundColor Cyan
Write-Host "  Password: Password@123" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANT: Do not close this window!" -ForegroundColor Yellow
Write-Host "The PHP server is running in a separate window." -ForegroundColor Yellow
Write-Host "Closing that window will stop the web server." -ForegroundColor Yellow
Write-Host ""
Write-Host "To stop the system:" -ForegroundColor White
Write-Host "  1. Close the PHP server window" -ForegroundColor Gray
Write-Host "  2. Stop MariaDB: Get-Process mysqld | Stop-Process" -ForegroundColor Gray
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan

# Keep this window open for reference
Read-Host "Press Enter to close this launcher (system will keep running)"
