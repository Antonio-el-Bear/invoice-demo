@echo off
REM ============================================
REM CLOUDUKO INVOICE SYSTEM - SHUTDOWN
REM ============================================
REM This batch file stops all system services
REM ============================================

cls
echo.
echo ============================================
echo CLOUDUKO INVOICE SYSTEM - SHUTDOWN
echo ============================================
echo.

REM ============================================
REM STEP 1: Stop PHP processes
REM ============================================
echo [1/2] Stopping PHP web server...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="0" (
    taskkill /IM php.exe /F 2>NUL
    echo       PHP server stopped
) else (
    echo       PHP was not running
)
echo.

REM ============================================
REM STEP 2: Stop MySQL/MariaDB processes
REM ============================================
echo [2/2] Stopping MariaDB database server...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    taskkill /IM mysqld.exe /F 2>NUL
    echo       MariaDB stopped
) else (
    echo       MariaDB was not running
)
echo.

REM ============================================
REM SUCCESS MESSAGE
REM ============================================
echo ============================================
echo SYSTEM SHUTDOWN COMPLETE
echo ============================================
echo.
echo All services have been stopped.
echo You can now close this window.
echo.
pause
