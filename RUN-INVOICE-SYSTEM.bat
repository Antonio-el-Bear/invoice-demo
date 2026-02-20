@echo off
REM Simple wrapper to run the PowerShell startupscript
REM This avoids PowerShell parsing issues

setlocal
cd /d "%~dp0"

echo.
echo ============================================
echo CLOUDUKO INVOICE SYSTEM - STARTUP
echo ============================================
echo.

REM Check if mysqld is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe">NUL
if errorlevel 1 (
    echo Starting MariaDB...
    start "" /MIN "C:\Users\User\Documents\Software\xampp\mysql\bin\mysqld.exe" --console
    timeout /t 5 /nobreak
) else (
    echo MariaDB is already running
)

echo.
echo Starting PHP Web Server at http://localhost:8000...
start "CloudUko Invoice System - PHP Server" /D "C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice" cmd /k "php -S localhost:8000"

echo.
timeout /t 3 /nobreak

echo Opening browser...
start http://localhost:8000

echo.
echo ============================================
echo SYSTEM STARTED
echo ============================================
echo.
echo Access your system at: http://localhost:8000
echo Default: admin / Password@123
echo.
echo The PHP server window will stay open while the system runs.
echo.
pause
