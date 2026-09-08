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

// 1. Standalone Native Desktop Window Launcher
const launcherBat = `@echo off
title Shree Giriraj Poly Plast ERP - Desktop App
echo ===================================================================
echo   SHREE GIRIRAJ POLY PLAST ERP - WINDOWS DESKTOP APPLICATION
echo ===================================================================
echo.
echo Launching ERP Native Window...

set TARGET_URL=https://shreegiriraj-erp.onrender.com

:: 1. Microsoft Edge App Mode (Built-in on Windows 10/11)
if exist "%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe" (
    start "" "%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)
if exist "%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe" (
    start "" "%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe" --app="%TARGET_URL%" --window-size=1366,820
    exit /b
)

:: 2. Google Chrome App Mode
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

:: 3. Default browser fallback
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

// 3. README Instructions
const readmeText = `===================================================================
  SHREE GIRIRAJ POLY PLAST ERP - WINDOWS DESKTOP SETUP
===================================================================

INSTALLATION & USAGE:
1. Double-click "Launch_Shree_Giriraj_ERP.bat" to start the Desktop Application.
2. Double-click "Install_Desktop_Shortcut.bat" to place a 1-click icon on your Windows Desktop.

FEATURES:
- Opens in dedicated native desktop window without browser bars.
- Instant access to Live Cloud ERP database.
- Thermal invoice printing and hardware accelerated UI.
- Compatible with Windows 10 and Windows 11 (64-bit / 32-bit).
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
