const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const rootDir = __dirname;
const pkgDir = path.join(rootDir, 'build_desktop_pkg');
const downloadsDir = path.join(rootDir, 'laravel', 'public', 'downloads');

if (!fs.existsSync(downloadsDir)) {
    fs.mkdirSync(downloadsDir, { recursive: true });
}

if (fs.existsSync(pkgDir)) {
    fs.rmSync(pkgDir, { recursive: true, force: true });
}
fs.mkdirSync(pkgDir, { recursive: true });
fs.mkdirSync(path.join(pkgDir, 'giriraj-desktop'), { recursive: true });

// Copy essential desktop files
fs.copyFileSync(path.join(rootDir, 'giriraj-desktop', 'main.js'), path.join(pkgDir, 'giriraj-desktop', 'main.js'));
fs.copyFileSync(path.join(rootDir, 'giriraj-desktop', 'offline.html'), path.join(pkgDir, 'giriraj-desktop', 'offline.html'));
fs.copyFileSync(path.join(rootDir, 'giriraj-desktop', 'package.json'), path.join(pkgDir, 'giriraj-desktop', 'package.json'));

// 1. Standalone Smart Hybrid Launcher (Online Cloud + Offline Auto Fallback)
const launcherBat = `@echo off
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
if exist "C:\\xampp\\php\\php.exe" set PHP_BIN=C:\\xampp\\php\\php.exe

:: 2. Find Laravel project directory across common locations
set LARAVEL_DIR=
if exist "%~dp0laravel\\artisan" set LARAVEL_DIR=%~dp0laravel
if not defined LARAVEL_DIR if exist "%~dp0..\\laravel\\artisan" set LARAVEL_DIR=%~dp0..\\laravel
if not defined LARAVEL_DIR if exist "C:\\xampp\\htdocs\\shreegiriraj\\laravel\\artisan" set LARAVEL_DIR=C:\\xampp\\htdocs\\shreegiriraj\\laravel

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
if exist "%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe" (
    start "" "%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe" (
    start "" "%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe" (
    start "" "%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles(x86)%\\Google\\Chrome\\Application\\chrome.exe" (
    start "" "%ProgramFiles(x86)%\\Google\\Chrome\\Application\\chrome.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%LocalAppData%\\Google\\Chrome\\Application\\chrome.exe" (
    start "" "%LocalAppData%\\Google\\Chrome\\Application\\chrome.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)

start %TARGET_URL%
exit /b
`;

fs.writeFileSync(path.join(pkgDir, 'Launch_Shree_Giriraj_ERP.bat'), launcherBat);

// 2. Desktop Shortcut Creator
const shortcutBat = `@echo off
title Install Shree Giriraj ERP Desktop Shortcut
echo Creating Desktop Shortcut...
powershell -NoProfile -Command "$ws = New-Object -ComObject WScript.Shell; $desktopPath = [Environment]::GetFolderPath('Desktop'); $shortcut = $ws.CreateShortcut([System.IO.Path]::Combine($desktopPath, 'Shree Giriraj Poly Plast ERP.lnk')); $shortcut.TargetPath = [System.IO.Path]::Combine('%~dp0', 'Launch_Shree_Giriraj_ERP.bat'); $shortcut.WorkingDirectory = '%~dp0'; $shortcut.Description = 'Shree Giriraj Poly Plast ERP Desktop Application'; $shortcut.Save(); Write-Host 'Desktop Shortcut Created Successfully!' -ForegroundColor Green"
echo.
echo Shortcut created on your Windows Desktop!
timeout /t 3
`;

fs.writeFileSync(path.join(pkgDir, 'Install_Desktop_Shortcut.bat'), shortcutBat);

// 3. Offline Mode Direct Launcher
const offlineBat = `@echo off
setlocal enabledelayedexpansion
title Shree Giriraj ERP - Offline Local Mode
echo Starting Offline ERP on Local PC...

set PHP_BIN=php
if exist "C:\\xampp\\php\\php.exe" set PHP_BIN=C:\\xampp\\php\\php.exe

set LARAVEL_DIR=
if exist "%~dp0laravel\\artisan" set LARAVEL_DIR=%~dp0laravel
if not defined LARAVEL_DIR if exist "%~dp0..\\laravel\\artisan" set LARAVEL_DIR=%~dp0..\\laravel
if not defined LARAVEL_DIR if exist "C:\\xampp\\htdocs\\shreegiriraj\\laravel\\artisan" set LARAVEL_DIR=C:\\xampp\\htdocs\\shreegiriraj\\laravel

curl -s -m 2 http://127.0.0.1:8000/login >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    if defined LARAVEL_DIR (
        pushd "%LARAVEL_DIR%"
        start "Shree Giriraj ERP Local" /min "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000
        popd
        timeout /t 2 /nobreak >nul
    )
)

if exist "%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe" (
    start "" "%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe" --app="http://127.0.0.1:8000" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe" (
    start "" "%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe" --app="http://127.0.0.1:8000" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe" (
    start "" "%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe" --app="http://127.0.0.1:8000" --window-size=1366,820
    exit /b
)
start http://127.0.0.1:8000
exit /b
`;

fs.writeFileSync(path.join(pkgDir, 'Offline_Local_ERP.bat'), offlineBat);

// 4. README Instructions
const readmeText = `===================================================================
  SHREE GIRIRAJ POLY PLAST ERP - DUAL MODE DESKTOP APPLICATION
===================================================================

FEATURES:
1. Works 100% OFFLINE without any internet connection.
2. Auto-Syncs all offline bills & payments to Render Cloud when online.
3. Dedicated desktop app window with thermal invoice printing.

HOW TO RUN:
- Double-click "Launch_Shree_Giriraj_ERP.bat" for auto Smart Mode.
- Double-click "Install_Desktop_Shortcut.bat" to add desktop icon.
- Double-click "Offline_Local_ERP.bat" for direct offline access.
`;

fs.writeFileSync(path.join(pkgDir, 'README.txt'), readmeText);

// Zip the package
const targetZip = path.join(downloadsDir, 'Shree-Giriraj-ERP-Desktop-Setup.zip');
if (fs.existsSync(targetZip)) {
    fs.unlinkSync(targetZip);
}

console.log('Compressing build_desktop_pkg into', targetZip);
execSync(`powershell -NoProfile -Command "Compress-Archive -Path '${pkgDir}\\*' -DestinationPath '${targetZip}' -Force"`, { stdio: 'inherit' });

console.log('Done! ZIP Size:', fs.statSync(targetZip).size, 'bytes');
