const { app, BrowserWindow } = require('electron');
const path = require('path');

// Require and start Express backend
require('./server');

let mainWindow;

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1366,
        height: 850,
        minWidth: 1024,
        minHeight: 700,
        title: 'Shree Giriraj Poly Plast ERP - Desktop Application',
        autoHideMenuBar: false,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true
        }
    });

    // Wait slightly for express server to start
    setTimeout(() => {
        mainWindow.loadURL('http://localhost:4000');
    }, 1200);

    mainWindow.on('closed', function () {
        mainWindow = null;
    });
}

app.on('ready', createWindow);

app.on('window-all-closed', function () {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', function () {
    if (mainWindow === null) {
        createWindow();
    }
});
