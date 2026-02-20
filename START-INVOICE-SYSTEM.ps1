# ============================================
# CLOUDUKO INVOICE SYSTEM - OFFLINE LAUNCHER
# ============================================
#
# This script starts the CloudUko Invoice Management System
# for offline use without requiring XAMPP Control Panel.

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "CLOUDUKO INVOICE SYSTEM - OFFLINE LAUNCHER" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ============================================
# STEP 0: DEPLOY APPLICATION FILES
# ============================================
Write-Host "[0/5] Deploying Application Files..." -ForegroundColor Yellow

$sourceDir = "c:\Users\User\Documents\cloud uko\apps\Invoice-System-In-PHP-main\Invoice-System-In-PHP-main"
$webRoot = "C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice"

if (Test-Path $sourceDir)
{
    try
    {
        Copy-Item -Path "$sourceDir\*" -Destination $webRoot -Recurse -Force -ErrorAction SilentlyContinue | Out-Null
        Write-Host "      ✓ Application files deployed to web root" -ForegroundColor Green
    }
    catch
    {
        Write-Host "      ⚠ Warning: Could not copy some files, but continuing..." -ForegroundColor Yellow
    }
}
else
{
    Write-Host "      ⚠ Warning: Source directory not found, skipping file copy" -ForegroundColor Yellow
}

Write-Host ""

# ============================================
# STEP 1: START MARIADB/MYSQL DATABASE
# ============================================
Write-Host "[1/5] Starting MariaDB/MySQL Database Server..." -ForegroundColor Yellow

$mysqlPath = "C:\Users\User\Documents\Software\xampp\mysql\bin\mysqld.exe"
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($mysqlRunning)
{
    $pid = $mysqlRunning.Id
    Write-Host "      ✓ MariaDB is already running (PID: $pid)" -ForegroundColor Green
}
else
{
    if (Test-Path $mysqlPath)
    {
        Start-Process -FilePath $mysqlPath -ArgumentList "--console" -WindowStyle Minimized
        Write-Host "      ✓ MariaDB started successfully" -ForegroundColor Green
        Write-Host "      ⏳ Waiting for database to initialize..." -ForegroundColor Gray
        Start-Sleep -Seconds 5
    }
    else
    {
        Write-Host "      ✗ ERROR: MariaDB not found at: $mysqlPath" -ForegroundColor Red
        Write-Host "      Please check your XAMPP installation" -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
}

Write-Host ""

# ============================================
# STEP 2: VERIFY DATABASE CONNECTION
# ============================================
Write-Host "[2/5] Verifying Database Connection..." -ForegroundColor Yellow

$mysqlCmd = "C:\Users\User\Documents\Software\xampp\mysql\bin\mysql.exe"
$retries = 5
$connected = $false

for ($i = 1; $i -le $retries; $i++)
{
    try
    {
        $result = & $mysqlCmd -u root -h localhost -e "SELECT 1" 2>&1 | Where-Object { $_ -match "1" }
        if ($result)
        {
            Write-Host "      ✓ Database connection successful" -ForegroundColor Green
            $connected = $true
            break
        }
    }
    catch
    {
        # Continue trying
    }
    
    if ($i -lt $retries)
    {
        Write-Host "      ⏳ Waiting for database... (attempt $i/$retries)" -ForegroundColor Gray
        Start-Sleep -Seconds 2
    }
}

if (-not $connected)
{
    Write-Host "      ⚠ Warning: Database connection could not be verified" -ForegroundColor Yellow
    Write-Host "      ⚠ But continuing anyway..." -ForegroundColor Yellow
}

Write-Host ""

# ============================================
# STEP 3: CONFIGURE PHP ENVIRONMENT
# ============================================
Write-Host "[3/5] Configuring PHP Environment..." -ForegroundColor Yellow

$phpPath = "C:\Users\User\Documents\Software\xampp\php\php.exe"
if (Test-Path $phpPath)
{
    $phpVersion = & $phpPath -v 2>&1 | Select-Object -First 1
    Write-Host "      ✓ PHP is ready: $phpVersion" -ForegroundColor Green
}
else
{
    Write-Host "      ✗ ERROR: PHP not found at: $phpPath" -ForegroundColor Red
    Write-Host "      Please ensure XAMPP is installed correctly" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# ============================================
# STEP 4: START PHP WEB SERVER
# ============================================
Write-Host "[4/5] Starting PHP Web Server..." -ForegroundColor Yellow

if (Test-Path $webRoot)
{
    Write-Host "      Web Root: $webRoot" -ForegroundColor Gray
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$webRoot'; & '$phpPath' -S localhost:8000" -WindowStyle Normal
    Write-Host "      ✓ PHP Server started on http://localhost:8000" -ForegroundColor Green
    Write-Host "      ⏳ Waiting for web server to start..." -ForegroundColor Gray
    Start-Sleep -Seconds 3
}
else
{
    Write-Host "      ✗ ERROR: Web root not found at: $webRoot" -ForegroundColor Red
    Write-Host "      Please verify installation path" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# ============================================
# STEP 5: OPEN WEB BROWSER
# ============================================
Write-Host "[5/5] Launching CloudUko Invoice System..." -ForegroundColor Yellow
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

Read-Host "Press Enter to close this launcher (system will keep running)"
