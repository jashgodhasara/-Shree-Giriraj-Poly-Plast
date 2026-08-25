const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const desktopPath = path.join(process.env.USERPROFILE || 'C:\\Users\\jashg', 'Desktop');
const targetExe = 'C:\\xampp\\htdocs\\shreegiriraj\\giriraj-desktop\\dist\\Shree Giriraj ERP-win32-x64\\Shree Giriraj ERP.exe';
const shortcutPath = path.join(desktopPath, 'Shree Giriraj ERP.lnk');

const psScript = `
$wsh = New-Object -ComObject WScript.Shell
$sc = $wsh.CreateShortcut('${shortcutPath.replace(/\\/g, '\\\\')}')
$sc.TargetPath = '${targetExe.replace(/\\/g, '\\\\')}'
$sc.WorkingDirectory = 'C:\\\\xampp\\\\htdocs\\\\shreegiriraj\\\\giriraj-desktop\\\\dist\\\\Shree Giriraj ERP-win32-x64'
$sc.Description = 'Shree Giriraj Poly Plast Desktop ERP'
$sc.Save()
`;

const tempPs1 = path.join(__dirname, 'temp_shortcut.ps1');
fs.writeFileSync(tempPs1, psScript, 'utf8');

try {
    execSync(`powershell -ExecutionPolicy Bypass -File "${tempPs1}"`, { stdio: 'inherit' });
    console.log('Successfully created Desktop shortcut at:', shortcutPath);
} finally {
    if (fs.existsSync(tempPs1)) fs.unlinkSync(tempPs1);
}
