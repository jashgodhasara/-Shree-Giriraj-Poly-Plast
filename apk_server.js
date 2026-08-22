const http = require('http');
const fs = require('fs');
const path = require('path');

const apkPath = path.join(__dirname, 'ShreeGirirajERP.apk');
const desktopZipPath = path.join(__dirname, 'ShreeGirirajERP_Windows_Desktop.zip');

const server = http.createServer((req, res) => {
    const url = req.url.toLowerCase();

    // 1. Android Mobile APK Download
    if (url === '/apk' || url.endsWith('.apk') || url === '/mobile') {
        if (!fs.existsSync(apkPath)) {
            res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
            return res.end('Android APK file not found');
        }
        res.writeHead(200, {
            'Content-Type': 'application/vnd.android.package-archive',
            'Content-Disposition': 'attachment; filename=ShreeGirirajERP.apk',
            'Content-Length': fs.statSync(apkPath).size,
            'Access-Control-Allow-Origin': '*'
        });
        return fs.createReadStream(apkPath).pipe(res);
    }

    // 2. Windows Laptop / Desktop Application ZIP Download
    if (url === '/desktop' || url === '/windows' || url.endsWith('.zip') || url === '/desktop-app') {
        if (!fs.existsSync(desktopZipPath)) {
            res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
            return res.end('Desktop application ZIP file not found');
        }
        res.writeHead(200, {
            'Content-Type': 'application/zip',
            'Content-Disposition': 'attachment; filename=ShreeGirirajERP_Windows_Desktop.zip',
            'Content-Length': fs.statSync(desktopZipPath).size,
            'Access-Control-Allow-Origin': '*'
        });
        return fs.createReadStream(desktopZipPath).pipe(res);
    }

    // 3. Download Portal UI
    const apkSizeMb = fs.existsSync(apkPath) ? (fs.statSync(apkPath).size / (1024 * 1024)).toFixed(1) : '135';
    const zipSizeMb = fs.existsSync(desktopZipPath) ? (fs.statSync(desktopZipPath).size / (1024 * 1024)).toFixed(1) : '110';

    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(`
        <!DOCTYPE html>
        <html lang="gu">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Shree Giriraj ERP — Download Hub</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Inter', system-ui, -apple-system, sans-serif;
                    background: radial-gradient(ellipse at 50% 20%, #1e1b4b 0%, #0f172a 70%, #020617 100%);
                    color: #f8fafc;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    padding: 24px 16px;
                }
                .box {
                    background: rgba(30, 41, 59, 0.8);
                    backdrop-filter: blur(20px);
                    -webkit-backdrop-filter: blur(20px);
                    padding: 36px 28px;
                    border-radius: 28px;
                    max-width: 480px;
                    width: 100%;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
                    text-align: center;
                }
                .brand-icon {
                    width: 68px;
                    height: 68px;
                    background: linear-gradient(135deg, #6366f1, #8b5cf6);
                    border-radius: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 16px;
                    font-size: 32px;
                    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
                }
                h2 {
                    font-family: 'Outfit', sans-serif;
                    font-size: 24px;
                    font-weight: 800;
                    margin-bottom: 4px;
                    background: linear-gradient(135deg, #ffffff, #cbd5e1);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                .sub {
                    color: #94a3b8;
                    font-size: 13px;
                    margin-bottom: 26px;
                }
                .cards {
                    display: flex;
                    flex-direction: column;
                    gap: 16px;
                    margin-bottom: 24px;
                }
                .card {
                    background: rgba(15, 23, 42, 0.7);
                    border: 1.5px solid rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 20px;
                    text-align: left;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 14px;
                    text-decoration: none;
                    color: inherit;
                    transition: all 0.25s;
                }
                .card:hover {
                    border-color: #6366f1;
                    transform: translateY(-2px);
                    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
                }
                .card-icon {
                    font-size: 36px;
                    flex-shrink: 0;
                }
                .card-info {
                    flex: 1;
                }
                .card-title {
                    font-size: 16px;
                    font-weight: 800;
                    color: #ffffff;
                    margin-bottom: 3px;
                }
                .card-desc {
                    font-size: 12px;
                    color: #94a3b8;
                    line-height: 1.3;
                }
                .card-badge {
                    display: inline-block;
                    font-size: 10.5px;
                    font-weight: 700;
                    padding: 3px 8px;
                    border-radius: 6px;
                    margin-top: 6px;
                }
                .badge-mobile { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
                .badge-desktop { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }
                .dl-btn {
                    padding: 10px 16px;
                    border-radius: 12px;
                    background: linear-gradient(135deg, #6366f1, #4f46e5);
                    color: #ffffff;
                    font-weight: 700;
                    font-size: 13px;
                    flex-shrink: 0;
                    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
                }
                .footer {
                    font-size: 11.5px;
                    color: #64748b;
                    border-top: 1px solid rgba(255, 255, 255, 0.08);
                    padding-top: 16px;
                }
            </style>
        </head>
        <body>
            <div class="box">
                <div class="brand-icon">🏭</div>
                <h2>Shree Giriraj Poly Plast</h2>
                <p class="sub">Official ERP Application Download Portal</p>

                <div class="cards">
                    <!-- Desktop App Card -->
                    <a href="/desktop" class="card">
                        <div class="card-icon">💻</div>
                        <div class="card-info">
                            <div class="card-title">Windows Desktop / Laptop</div>
                            <div class="card-desc">Dual Mode (Online Cloud & Offline Local)</div>
                            <span class="card-badge badge-desktop">Portable ZIP &bull; ${zipSizeMb} MB</span>
                        </div>
                        <div class="dl-btn">📥 Download</div>
                    </a>

                    <!-- Mobile App Card -->
                    <a href="/apk" class="card">
                        <div class="card-icon">📱</div>
                        <div class="card-info">
                            <div class="card-title">Android Mobile App</div>
                            <div class="card-desc">Fast, Live Sync & Instant Cache</div>
                            <span class="card-badge badge-mobile">Direct APK &bull; ${apkSizeMb} MB</span>
                        </div>
                        <div class="dl-btn">📲 Download</div>
                    </a>
                </div>

                <div class="footer">
                    ⚡ Live Cloud ERP: <a href="https://shreegiriraj-erp.onrender.com" target="_blank" style="color:#818cf8; text-decoration:none; font-weight:700;">shreegiriraj-erp.onrender.com</a>
                </div>
            </div>
        </body>
        </html>
    `);
});

server.listen(8080, '0.0.0.0', () => {
    console.log('✅ Wi-Fi Multi-Platform Download Server running on port 8080');
});
