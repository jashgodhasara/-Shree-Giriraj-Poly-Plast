@echo off
title Shree Giriraj ERP - Direct Live Online URL Generator
color 0A
cls
echo ================================================================
echo         SHREE GIRIRAJ POLY PLAST ERP - ONLINE DIRECT SERVER
echo ================================================================
echo.
echo Starting direct online live URL (No IP password needed)...
echo.
ssh -o StrictHostKeyChecking=no -R 80:localhost:4000 serveo.net
pause
