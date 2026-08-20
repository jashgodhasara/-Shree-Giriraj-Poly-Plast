@echo off
setlocal
title Shree Giriraj ERP Launcher

set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

:: 1. Detect PHP binary
set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"

:: 2. Check if Laravel backend is responding on port 8000
curl -s -m 2 http://127.0.0.1:8000/login >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [Shree Giriraj ERP] Starting backend service on port 8000...
    cd /d "%SCRIPT_DIR%laravel"
    start "Shree Giriraj ERP Backend" /min "%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000
    timeout /t 2 /nobreak >nul
)

:: 3. Launch Desktop ERP Application
cd /d "%SCRIPT_DIR%giriraj-desktop"
if exist "dist\Shree Giriraj ERP-win32-x64\Shree Giriraj ERP.exe" (
    start "" "dist\Shree Giriraj ERP-win32-x64\Shree Giriraj ERP.exe"
) else (
    start "" npm start
)
endlocal
exit
