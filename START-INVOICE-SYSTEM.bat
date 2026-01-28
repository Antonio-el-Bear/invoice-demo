@echo off
REM ============================================
REM CLOUDUKO INVOICE SYSTEM - STARTUP
REM ============================================
REM This batch file starts the entire system:
REM - Database server (MariaDB)
REM - Web server (PHP)
REM - Browser window
REM ============================================

setlocal enabledelayedexpansion

cls
echo.
echo ============================================
echo CLOUDUKO INVOICE SYSTEM - STARTUP
echo ============================================
echo.

REM ============================================
REM STEP 1: Check if MariaDB is already running
REM ============================================
echo [1/4] Checking database server...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo       MariaDB is already running
) else (
    echo       Starting MariaDB database server...
    start "" /MIN "C:\Users\User\Documents\Software\xampp\mysql\bin\mysqld.exe" --console
    timeout /t 5 /nobreak
)
echo.

REM ============================================
REM STEP 2: Change to web root directory
REM ============================================
echo [2/4] Preparing web server...
cd /d "C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice"

if not exist "index.php" (
    echo ERROR: Could not find invoice system files!
    echo Expected location: C:\Users\User\Documents\Software\xampp\htdocs\clouduko-invoice\
    pause
    exit /b 1
)
echo       Web files found and ready
echo.

REM ============================================
REM STEP 3: Start PHP web server
REM ============================================
echo [3/4] Starting PHP web server on localhost:8000...
start "CloudUko Invoice System - Web Server" cmd /k php -S localhost:8000
timeout /t 3 /nobreak
echo       Web server started!
echo.

REM ============================================
REM STEP 4: Open browser
REM ============================================
echo [4/4] Opening your browser...
start http://localhost:8000
echo.

REM ============================================
REM SUCCESS MESSAGE
REM ============================================
echo ============================================
echo SYSTEM STARTED SUCCESSFULLY!
echo ============================================
echo.
echo URL: http://localhost:8000
echo.
echo Login with:
echo   Username: admin
echo   Password: Password@123
echo.
echo IMPORTANT: Do NOT close the web server window!
echo The system will stop if you close it.
echo.
echo To STOP the system later, run:
echo   STOP-INVOICE-SYSTEM.bat
echo.
pause
