@echo off
title AeroSense V2 Server
echo ============================================================
echo   AeroSense V2 - Local Server Runner
echo ============================================================
echo.

REM 1. Check if MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL is already running.
) else (
    echo [..] Starting MySQL via XAMPP...
    start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    timeout /t 3 /nobreak >NUL
)

echo.
echo ============================================================
echo  Server Ready!
echo.
echo  Website:
echo    - Viewer Dashboard:  http://localhost:8000
echo    - Admin Panel:       http://localhost:8000/admin
echo                         (User: admin / Pass: admin12345)
echo.
echo  Hardware / ESP32 Endpoint (Local Network):
echo    - Ingestion URL:     http://localhost:8000/api/send-data
echo    - Method:            POST (application/json)
echo ============================================================
echo.

REM Open browser
start "" "http://localhost:8000"

REM Run Laravel server listening on all network interfaces
php artisan serve --host=0.0.0.0 --port=8000
