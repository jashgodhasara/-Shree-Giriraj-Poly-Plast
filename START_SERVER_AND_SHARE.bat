@echo off
title Shree Giriraj ERP - Start Server + Generate Client Link
color 0A
cls
echo.
echo  ================================================================
echo     SHREE GIRIRAJ POLY PLAST ERP - SERVER AND CLIENT SETUP
echo  ================================================================
echo.
echo  [1/2] Starting ERP server on port 4000...
start "SGP-ERP-Backend" cmd /c "cd /d "%~dp0" && node server.js"
timeout /t 3 /nobreak >nul
echo       Server started!
echo.
echo  [2/2] Creating public internet link for clients...
echo        (Takes 5-10 seconds - please wait)
echo.
echo  ================================================================
echo     COPY THE LINK BELOW AND SEND IT TO YOUR CLIENT
echo     Client opens: ShreeGirirajERP_Client.html
echo     Then pastes the link below into the URL box
echo  ================================================================
echo.
ssh -o StrictHostKeyChecking=no -o ServerAliveInterval=60 -R 80:localhost:4000 serveo.net
echo.
echo  Server stopped. Press any key to exit.
pause >nul
