@echo off
setlocal
title Shree Giriraj Poly Plast ERP
echo ===================================================================
echo   SHREE GIRIRAJ POLY PLAST ERP - HYBRID DESKTOP APPLICATION
echo ===================================================================
echo.
echo [1/2] Checking Network and Local Backend Status...

set ONLINE_URL=https://shreegiriraj-erp.onrender.com
set OFFLINE_URL=http://127.0.0.1:8000
set TARGET_URL=%ONLINE_URL%

:: Find PHP executable
set PHP_BIN=php
if exist "C:\xampp\php\php.exe" set PHP_BIN=C:\xampp\php\php.exe

:: Ensure local backend is running in background for offline use & auto-sync
curl -s -m 2 %OFFLINE_URL%/login >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Starting Local ERP Database Engine...
    if exist "%~dp0laravel" (
        cd /d "%~dp0laravel"
        start "Shree Giriraj ERP Local Backend" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000
        timeout /t 2 /nobreak >nul
    )
)

:: Test if online cloud is reachable
curl -s -m 3 %ONLINE_URL%/api >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [Notice] No Internet detected. Opening Offline Mode...
    set TARGET_URL=%OFFLINE_URL%
) else (
    echo [Status] Internet Connected!
)

echo [2/2] Opening Native Window...

:: 1. Microsoft Edge App Mode (Built-in on Windows 10/11)
if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)

:: 2. Google Chrome App Mode
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" (
    start "" "%LocalAppData%\Google\Chrome\Application\chrome.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)

:: 3. Default browser fallback
start %TARGET_URL%
exit /b
