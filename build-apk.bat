@echo off
title Building Shree Giriraj ERP Android APK
cd /d "%~dp0"

echo =======================================================
echo    BUILDING SHREE GIRIRAJ ERP STANDALONE ANDROID APK
echo =======================================================
echo.

set JAVA_HOME=C:\Program Files\Android\Android Studio\jbr
set ANDROID_HOME=C:\Users\jashg\AppData\Local\Android\Sdk
set PATH=%JAVA_HOME%\bin;%ANDROID_HOME%\platform-tools;%PATH%

cd /d "%~dp0giriraj-mobile\android"

echo Running Gradle assembleDebug...
call gradlew.bat assembleDebug --no-daemon

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Build failed! Check the error log above.
    pause
    exit /b 1
)

echo.
echo Copying APK to main workspace...
if exist "app\build\outputs\apk\debug\app-debug.apk" (
    copy /y "app\build\outputs\apk\debug\app-debug.apk" "%~dp0ShreeGirirajERP.apk"
    echo.
    echo =======================================================
    echo  SUCCESS! Standalone APK Created:
    echo  %~dp0ShreeGirirajERP.apk
    echo =======================================================
) else (
    echo [Warning] APK file not found in default output directory.
)

pause
