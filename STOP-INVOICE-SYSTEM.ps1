# ============================================
# CLOUDUKO INVOICE SYSTEM - SHUTDOWN SCRIPT
# ============================================
#
# This script safely stops all CloudUko system services
#
# What it does:
# 1. Stops PHP web server
# 2. Stops MariaDB/MySQL database server
# 3. Cleans up processes
#
# ============================================

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "CLOUDUKO INVOICE SYSTEM - SHUTDOWN" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ============================================
# STOP PHP WEB SERVER
# ============================================
Write-Host "[1/2] Stopping PHP Web Server..." -ForegroundColor Yellow

$phpProcesses = Get-Process -Name "php" -ErrorAction SilentlyContinue

if ($phpProcesses) {
    $phpProcesses | Stop-Process -Force
    Write-Host "      ✓ PHP Server stopped" -ForegroundColor Green
} else {
    Write-Host "      ℹ PHP Server was not running" -ForegroundColor Gray
}

Write-Host ""

# ============================================
# STOP MARIADB/MYSQL DATABASE
# ============================================
Write-Host "[2/2] Stopping MariaDB/MySQL Database..." -ForegroundColor Yellow

$mysqlProcesses = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($mysqlProcesses) {
    # Give database time to close connections gracefully
    Write-Host "      ⏳ Shutting down database safely..." -ForegroundColor Gray
    $mysqlProcesses | Stop-Process -Force
    Start-Sleep -Seconds 2
    Write-Host "      ✓ MariaDB stopped" -ForegroundColor Green
} else {
    Write-Host "      ℹ MariaDB was not running" -ForegroundColor Gray
}

Write-Host ""

# ============================================
# SUCCESS MESSAGE
# ============================================
Write-Host "============================================" -ForegroundColor Green
Write-Host "✓ SYSTEM SHUTDOWN COMPLETE" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "All CloudUko Invoice System services have been stopped." -ForegroundColor White
Write-Host "You can now safely close all windows." -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to exit"
