const { app, BrowserWindow, Menu, dialog, ipcMain } = require('electron');
const path = require('path');
const fs = require('fs');
const http = require('http');
const https = require('https');
const { spawn } = require('child_process');

const configPath = path.join(app.getPath('userData'), 'server_config.json');
let phpProcess = null;
let mainWindow = null;

const ONLINE_URL = 'https://shreegiriraj-erp.onrender.com';
const OFFLINE_URL = 'http://127.0.0.1:8000';

function getServerUrl() {
  try {
    if (fs.existsSync(configPath)) {
      const data = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      if (data.url && data.url.trim().length > 0) return data.url.trim();
    }
  } catch (e) {
    // fallback
  }
  return ONLINE_URL;
}

function saveServerUrl(url) {
  try {
    fs.writeFileSync(configPath, JSON.stringify({ url: url.trim() }));
  } catch (e) {
    console.error('Failed to save config:', e);
  }
}

// Find Laravel directory
function findLaravelPath() {
  const candidatePaths = [
    path.resolve(__dirname, '..', 'laravel'),
    path.resolve(__dirname, '..', '..', '..', '..', 'laravel'),
    path.resolve(__dirname, '..', '..', '..', 'laravel'),
    path.resolve(process.resourcesPath || '', '..', '..', '..', 'laravel'),
    path.resolve(process.cwd(), 'laravel'),
    path.resolve(process.cwd(), '..', 'laravel'),
    'C:\\xampp\\htdocs\\shreegiriraj\\laravel',
  ];

  for (const p of candidatePaths) {
    if (p && fs.existsSync(path.join(p, 'artisan'))) {
      return p;
    }
  }
  return null;
}

// Find PHP executable
function findPhpPath() {
  const candidatePaths = [
    'C:\\xampp\\php\\php.exe',
    'php',
  ];
  for (const p of candidatePaths) {
    if (p === 'php' || fs.existsSync(p)) {
      return p;
    }
  }
  return 'php';
}

// Check if server is reachable and responding (supports both HTTP & HTTPS)
function checkServerRunning(url, callback) {
  try {
    const parsed = new URL(url);
    const client = parsed.protocol === 'https:' ? https : http;
    const req = client.get(
      {
        host: parsed.hostname,
        port: parsed.port || (parsed.protocol === 'https:' ? 443 : 80),
        path: '/login',
        timeout: 6000,
      },
      (res) => {
        callback(res.statusCode >= 200 && res.statusCode < 500);
      }
    );
    req.on('error', () => callback(false));
    req.on('timeout', () => {
      req.destroy();
      callback(false);
    });
  } catch {
    callback(false);
  }
}

// Auto-spawn Laravel artisan serve if on local machine and not running
function startLocalBackend(callback) {
  const laravelPath = findLaravelPath();
  const phpBin = findPhpPath();

  if (laravelPath) {
    console.log('Starting local Laravel backend from:', laravelPath, 'using PHP:', phpBin);
    if (mainWindow && mainWindow.webContents) {
      mainWindow.webContents.send('connection-status', {
        status: 'starting',
        message: 'Starting local Laravel offline engine on port 8000...'
      });
    }

    try {
      if (!phpProcess) {
        phpProcess = spawn(phpBin, ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {
          cwd: laravelPath,
          shell: true,
          detached: false,
        });

        phpProcess.stdout?.on('data', (data) => console.log(`[Laravel]: ${data}`));
        phpProcess.stderr?.on('data', (data) => console.error(`[Laravel Err]: ${data}`));
      }
    } catch (err) {
      console.error('Failed to spawn PHP artisan serve:', err);
    }
  } else {
    console.warn('Could not locate Laravel directory with artisan file.');
  }

  // Poll until server responds (max 15 seconds)
  let attempts = 0;
  const poll = setInterval(() => {
    attempts++;
    checkServerRunning(OFFLINE_URL, (isRunning) => {
      if (isRunning || attempts >= 20) {
        clearInterval(poll);
        callback(isRunning);
      }
    });
  }, 750);
}

function loadApp() {
  if (!mainWindow) return;
  const url = getServerUrl();
  const isOnlineMode = url.startsWith('https://') || url.includes('onrender.com');

  // Load sleek connecting / mode selector screen
  mainWindow.loadFile(path.join(__dirname, 'offline.html'));

  if (mainWindow && mainWindow.webContents) {
    mainWindow.webContents.send('current-mode', {
      mode: isOnlineMode ? 'online' : 'offline',
      url: url
    });
  }

  checkServerRunning(url, (isRunning) => {
    if (isRunning) {
      if (mainWindow && mainWindow.webContents) {
        mainWindow.webContents.send('connection-status', {
          status: 'connected',
          message: isOnlineMode ? 'Connected to Cloud Live ERP!' : 'Connected to Local Offline ERP!'
        });
      }
      setTimeout(() => {
        if (mainWindow) mainWindow.loadURL(url);
      }, 500);
    } else {
      if (isOnlineMode) {
        // Online cloud server reachable? If not, offer fallback or wait
        if (mainWindow && mainWindow.webContents) {
          mainWindow.webContents.send('connection-status', {
            status: 'error',
            message: 'Unable to reach Online Cloud Server. You can retry or switch to Offline Mode.'
          });
        }
      } else {
        // Local mode: Try auto-starting PHP server
        startLocalBackend((started) => {
          if (started) {
            if (mainWindow && mainWindow.webContents) {
              mainWindow.webContents.send('connection-status', {
                status: 'connected',
                message: 'Local Offline ERP Ready! Launching...'
              });
            }
            setTimeout(() => {
              if (mainWindow) mainWindow.loadURL(url);
            }, 500);
          } else {
            if (mainWindow && mainWindow.webContents) {
              mainWindow.webContents.send('connection-status', {
                status: 'error',
                message: 'Could not start Local ERP service on 127.0.0.1:8000. Please ensure PHP is installed.'
              });
            }
          }
        });
      }
    }
  });
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1320,
    height: 860,
    minWidth: 1024,
    minHeight: 700,
    title: 'Shree Giriraj Poly Plast — ERP',
    backgroundColor: '#0f172a',
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  loadApp();

  // Custom Application Menu
  const menuTemplate = [
    {
      label: 'ERP Mode',
      submenu: [
        {
          label: '🌐 Switch to Online Mode (Cloud Live)',
          accelerator: 'CmdOrCtrl+1',
          click: () => {
            saveServerUrl(ONLINE_URL);
            loadApp();
          },
        },
        {
          label: '💻 Switch to Offline Mode (Local PC)',
          accelerator: 'CmdOrCtrl+2',
          click: () => {
            saveServerUrl(OFFLINE_URL);
            loadApp();
          },
        },
        { type: 'separator' },
        {
          label: '🔄 Reload / Reconnect',
          accelerator: 'CmdOrCtrl+R',
          click: () => loadApp(),
        },
        {
          label: '⚙️ Custom Server URL...',
          click: async () => {
            const current = getServerUrl();
            const { response } = await dialog.showMessageBox(mainWindow, {
              type: 'question',
              buttons: ['Online Live (Cloud)', 'Offline Localhost', 'Cancel'],
              defaultId: 0,
              title: 'Select ERP Environment',
              message: `Current Server:\n${current}\n\nChoose connection mode:`,
            });
            if (response === 0) {
              saveServerUrl(ONLINE_URL);
              loadApp();
            } else if (response === 1) {
              saveServerUrl(OFFLINE_URL);
              loadApp();
            }
          },
        },
        { type: 'separator' },
        { label: 'Exit ERP', role: 'quit' },
      ],
    },
    {
      label: 'Edit',
      submenu: [
        { role: 'undo' },
        { role: 'redo' },
        { type: 'separator' },
        { role: 'cut' },
        { role: 'copy' },
        { role: 'paste' },
        { role: 'selectAll' },
      ],
    },
    {
      label: 'View',
      submenu: [
        { role: 'resetZoom' },
        { role: 'zoomIn' },
        { role: 'zoomOut' },
        { type: 'separator' },
        { role: 'togglefullscreen' },
        { role: 'toggleDevTools' },
      ],
    },
  ];

  const menu = Menu.buildFromTemplate(menuTemplate);
  Menu.setApplicationMenu(menu);

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

ipcMain.on('switch-mode', (event, targetUrl) => {
  if (targetUrl) {
    saveServerUrl(targetUrl);
  }
  loadApp();
});

ipcMain.on('retry-connection', (event, customUrl) => {
  if (customUrl && customUrl.trim().length > 0) {
    saveServerUrl(customUrl.trim());
  }
  loadApp();
});

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (phpProcess) {
    try { phpProcess.kill(); } catch (e) {}
  }
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});
