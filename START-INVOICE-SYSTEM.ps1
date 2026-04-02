# ============================================
# CLOUDUKO INVOICE SYSTEM - OFFLINE LAUNCHER
# ============================================

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "CLOUDUKO INVOICE SYSTEM - OFFLINE LAUNCHER" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$sourceDir = "c:\Users\User\Documents\cloud uko\apps\Invoice-System-In-PHP-main\Invoice-System-In-PHP-main"
$webRoot = "C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice"
$mysqlPath = "C:\Users\User\Documents\Software\xampp\mysql\bin\mysqld.exe"
$mysqlCmd = "C:\Users\User\Documents\Software\xampp\mysql\bin\mysql.exe"
$phpPath = "C:\Users\User\Documents\Software\xampp\php\php.exe"
$siteUrl = "http://localhost:8000"

# STEP 0: DEPLOY APPLICATION FILES
Write-Host "[0/5] Deploying Application Files..." -ForegroundColor Yellow
if (Test-Path $sourceDir)
{
    try
    {
        New-Item -Path $webRoot -ItemType Directory -Force | Out-Null
        Copy-Item -Path "$sourceDir\*" -Destination $webRoot -Recurse -Force
        Write-Host "      [OK] Application files deployed to web root" -ForegroundColor Green
    }
    catch
    {
        Write-Host "      [WARN] Some files could not be copied, continuing..." -ForegroundColor Yellow
    }
}
else
{
    Write-Host "      [WARN] Source directory not found, skipping file copy" -ForegroundColor Yellow
}

Write-Host ""

# STEP 1: START MARIADB/MYSQL DATABASE
Write-Host "[1/5] Starting MariaDB/MySQL Database Server..." -ForegroundColor Yellow
$mysqlRunning = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysqlRunning)
{
    $mysqlPid = ($mysqlRunning | Select-Object -First 1).Id
    Write-Host "      [OK] MariaDB is already running (PID: $mysqlPid)" -ForegroundColor Green
}
else
{
    if (Test-Path $mysqlPath)
    {
        Start-Process -FilePath $mysqlPath -ArgumentList "--console" -WindowStyle Minimized
        Write-Host "      [OK] MariaDB started successfully" -ForegroundColor Green
        Write-Host "      [WAIT] Waiting for database to initialize..." -ForegroundColor Gray
        Start-Sleep -Seconds 5
    }
    else
    {
        Write-Host "      [ERROR] MariaDB not found at: $mysqlPath" -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
}

Write-Host ""

# STEP 2: VERIFY DATABASE CONNECTION
Write-Host "[2/5] Verifying Database Connection..." -ForegroundColor Yellow
$connected = $false
if (Test-Path $mysqlCmd)
{
    for ($i = 1; $i -le 5; $i++)
    {
        try
        {
            $result = & $mysqlCmd -u root -h localhost -e "SELECT 1" 2>&1
            if ($LASTEXITCODE -eq 0)
            {
                $connected = $true
                Write-Host "      [OK] Database connection successful" -ForegroundColor Green
                break
            }
        }
        catch { }

        if ($i -lt 5)
        {
            Write-Host "      [WAIT] Waiting for database... (attempt $i/5)" -ForegroundColor Gray
            Start-Sleep -Seconds 2
        }
    }
}
if (-not $connected)
{
    Write-Host "      [WARN] Database connection could not be verified, continuing..." -ForegroundColor Yellow
}

Write-Host ""

# STEP 3: CONFIGURE PHP ENVIRONMENT
Write-Host "[3/5] Configuring PHP Environment..." -ForegroundColor Yellow
if (Test-Path $phpPath)
{
    $phpVersion = & $phpPath -v 2>&1 | Select-Object -First 1
    Write-Host "      [OK] PHP is ready: $phpVersion" -ForegroundColor Green
}
else
{
    Write-Host "      [ERROR] PHP not found at: $phpPath" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# STEP 4: START PHP WEB SERVER
Write-Host "[4/5] Starting PHP Web Server..." -ForegroundColor Yellow
if (-not (Test-Path $webRoot))
{
    Write-Host "      [ERROR] Web root not found at: $webRoot" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

$portOpen = Test-NetConnection -ComputerName localhost -Port 8000 -WarningAction SilentlyContinue
if ($portOpen.TcpTestSucceeded)
{
    Write-Host "      [OK] Port 8000 already in use, reusing existing server" -ForegroundColor Green
}
else
{
    Write-Host "      Web Root: $webRoot" -ForegroundColor Gray
    $serverCommand = "Set-Location '$webRoot'; & '$phpPath' -S localhost:8000"
    Start-Process powershell -ArgumentList "-NoExit", "-Command", $serverCommand -WindowStyle Normal
    Start-Sleep -Seconds 3
    Write-Host "      [OK] PHP server started on $siteUrl" -ForegroundColor Green
}

Write-Host ""

# STEP 5: OPEN WEB BROWSER
Write-Host "[5/5] Launching CloudUko Invoice System..." -ForegroundColor Yellow
Start-Process $siteUrl
Write-Host "      [OK] Browser launched" -ForegroundColor Green
Write-Host ""

Write-Host "============================================" -ForegroundColor Green
Write-Host "SYSTEM STARTED SUCCESSFULLY" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host "Access your invoice system at: $siteUrl" -ForegroundColor Cyan
Write-Host ""
Write-Host "Default Login Credentials:" -ForegroundColor White
Write-Host "  Username: admin" -ForegroundColor Cyan
Write-Host "  Password: Password@123" -ForegroundColor Cyan
Write-Host ""
Write-Host "Do not close the PHP server window if it was opened." -ForegroundColor Yellow
Write-Host "To stop DB manually: Get-Process mysqld | Stop-Process" -ForegroundColor Gray
Write-Host ""

Read-Host "Press Enter to close this launcher (system keeps running)"
