@echo off
title Shree Giriraj ERP - Mobile App Server
cd /d "%~dp0"

echo ===================================================
echo     SHREE GIRIRAJ POLY PLAST - MOBILE ERP APP
echo ===================================================
echo.
echo [1/2] Checking backend server status...
powershell -NoProfile -Command "try { $r = (Invoke-WebRequest -Uri 'http://127.0.0.1:8000/login' -TimeoutSec 1 -UseBasicParsing).StatusCode; if ($r -ge 200 -and $r -lt 500) { exit 0 } else { exit 1 } } catch { exit 1 }"
if %ERRORLEVEL% NEQ 0 (
    echo [Backend] Starting Laravel Backend on http://0.0.0.0:8000 ...
    cd /d "%~dp0laravel"
    set PHP_BIN=php
    if exist "C:\xampp\php\php.exe" set PHP_BIN=C:\xampp\php\php.exe
    start "Shree Giriraj ERP Backend" /min cmd /c ""%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000"
    ping 127.0.0.1 -n 3 >nul
)

echo.
echo [2/2] Starting Mobile App Expo Server...
echo.
echo ---------------------------------------------------
echo  INSTRUCTIONS FOR RUNNING ON YOUR PHONE:
echo  1. Install 'Expo Go' app from Play Store or App Store.
echo  2. Connect your mobile phone to the SAME Wi-Fi network.
echo  3. Open 'Expo Go' on your phone and SCAN the QR code below.
echo  4. The Shree Giriraj ERP Mobile App will open instantly!
echo ---------------------------------------------------
echo.

cd /d "%~dp0giriraj-mobile"
npx expo start --clear

pause
