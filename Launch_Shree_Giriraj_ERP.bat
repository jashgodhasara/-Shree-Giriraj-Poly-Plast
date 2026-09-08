@echo off
setlocal enabledelayedexpansion
title Shree Giriraj Poly Plast ERP
echo ===================================================================
echo   SHREE GIRIRAJ POLY PLAST ERP - HYBRID DESKTOP APPLICATION
echo ===================================================================
echo.

set ONLINE_URL=https://shreegiriraj-erp.onrender.com
set OFFLINE_URL=http://127.0.0.1:8000
set TARGET_URL=%ONLINE_URL%

:: 1. Detect PHP binary
set PHP_BIN=php
if exist "C:\xampp\php\php.exe" set PHP_BIN=C:\xampp\php\php.exe

:: 2. Find Laravel project directory
set LARAVEL_DIR=
if exist "%~dp0laravel\artisan" set LARAVEL_DIR=%~dp0laravel
if not defined LARAVEL_DIR if exist "%~dp0..\laravel\artisan" set LARAVEL_DIR=%~dp0..\laravel
if not defined LARAVEL_DIR if exist "C:\xampp\htdocs\shreegiriraj\laravel\artisan" set LARAVEL_DIR=C:\xampp\htdocs\shreegiriraj\laravel

:: 3. Check if local backend is running, start if needed
curl -s -m 2 %OFFLINE_URL%/login >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    if defined LARAVEL_DIR (
        echo Starting Local Offline ERP Engine on 127.0.0.1:8000...
        pushd "%LARAVEL_DIR%"
        start "Shree Giriraj ERP Local Backend" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000
        popd
        timeout /t 2 /nobreak >nul
    )
)

:: 4. Check if online Cloud ERP is reachable
curl -s -m 3 %ONLINE_URL%/api >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [Mode] No Internet. Launching Offline Mode on 127.0.0.1:8000...
    set TARGET_URL=%OFFLINE_URL%
) else (
    echo [Mode] Internet Connected! Launching Cloud Live ERP...
)

echo Opening Dedicated Desktop App Window...

:: Launch in native window mode
if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
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

start %TARGET_URL%
exit /b
