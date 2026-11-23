@echo off
echo ============================================================
echo  AeroSense V2 — Starting Development Environment
echo ============================================================
echo.

REM Check if Apache is already running
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Apache is already running.
) else (
    echo [..] Starting Apache via XAMPP...
    start "" "C:\xampp\xampp-control.exe"
    timeout /t 3 /nobreak >NUL
)

echo.
echo [OK] All systems ready.
echo.
echo ============================================================
echo  Open your browser and go to:
echo.
echo     http://localhost:8080        (Viewer Dashboard)
echo     http://localhost:8080/admin  (Admin Panel)
echo.
echo  DO NOT use php artisan serve — use Apache above instead.
echo ============================================================
echo.

REM Open browser automatically
start "" "http://localhost:8080"

pause
