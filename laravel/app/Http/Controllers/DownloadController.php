<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class DownloadController extends Controller
{
    /**
     * Display Download Hub & App Installation Page
     */
    public function index()
    {
        $desktopZip = public_path('downloads/Shree-Giriraj-ERP-Desktop-Setup.zip');
        $mobileApk = public_path('ShreeGirirajERP.apk');

        $desktopExists = File::exists($desktopZip);
        if ($desktopExists) {
            $bytes = File::size($desktopZip);
            $desktopSize = $bytes > 1024 * 1024 ? round($bytes / (1024 * 1024), 1) . ' MB' : round($bytes / 1024, 1) . ' KB';
        } else {
            $desktopSize = 'Portable ZIP (~10 KB)';
        }

        $apkExists = File::exists($mobileApk);
        $apkSize = $apkExists ? round(File::size($mobileApk) / (1024 * 1024), 1) . ' MB' : 'Android APK';

        return view('downloads.index', compact('desktopExists', 'desktopSize', 'apkExists', 'apkSize'));
    }

    /**
     * Download Windows Desktop Application ZIP
     */
    public function desktop()
    {
        $possiblePaths = [
            public_path('downloads/Shree-Giriraj-ERP-Desktop-Setup.zip'),
            public_path('downloads/Shree-Giriraj-ERP-Desktop-v1.0.zip'),
            public_path('ShreeGirirajERP_Windows_Desktop.zip'),
            base_path('../ShreeGirirajERP_Windows_Desktop.zip'),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return response()->download($path, 'Shree-Giriraj-ERP-Desktop-Setup.zip', [
                    'Content-Type' => 'application/zip',
                ]);
            }
        }

        // On-the-fly zip generator fallback if missing on server
        return $this->generateAndDownloadDesktopZip();
    }

    /**
     * Generate Desktop Setup ZIP on the fly and trigger browser download
     */
    private function generateAndDownloadDesktopZip()
    {
        $zipDir = public_path('downloads');
        if (!File::isDirectory($zipDir)) {
            File::makeDirectory($zipDir, 0777, true, true);
        }

        $zipPath = $zipDir . '/Shree-Giriraj-ERP-Desktop-Setup.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $liveUrl = url('/');

            $launcherBat = "@echo off\r\ntitle Shree Giriraj Poly Plast ERP - Desktop App\r\necho ===================================================================\r\necho   SHREE GIRIRAJ POLY PLAST ERP - WINDOWS DESKTOP APPLICATION\r\necho ===================================================================\r\necho.\r\necho Launching ERP Native Window...\r\n\r\nset TARGET_URL=" . $liveUrl . "\r\n\r\n:: 1. Microsoft Edge App Mode (Built-in on Windows 10/11)\r\nif exist \"%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe\" (\r\n    start \"\" \"%ProgramFiles(x86)%\\Microsoft\\Edge\\Application\\msedge.exe\" --app=\"%TARGET_URL%\" --window-size=1366,820\r\n    exit /b\r\n)\r\nif exist \"%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe\" (\r\n    start \"\" \"%ProgramFiles%\\Microsoft\\Edge\\Application\\msedge.exe\" --app=\"%TARGET_URL%\" --window-size=1366,820\r\n    exit /b\r\n)\r\n\r\n:: 2. Google Chrome App Mode\r\nif exist \"%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe\" (\r\n    start \"\" \"%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe\" --app=\"%TARGET_URL%\" --window-size=1366,820\r\n    exit /b\r\n)\r\nif exist \"%ProgramFiles(x86)%\\Google\\Chrome\\Application\\chrome.exe\" (\r\n    start \"\" \"%ProgramFiles(x86)%\\Google\\Chrome\\Application\\chrome.exe\" --app=\"%TARGET_URL%\" --window-size=1366,820\r\n    exit /b\r\n)\r\nif exist \"%LocalAppData%\\Google\\Chrome\\Application\\chrome.exe\" (\r\n    start \"\" \"%LocalAppData%\\Google\\Chrome\\Application\\chrome.exe\" --app=\"%TARGET_URL%\" --window-size=1366,820\r\n    exit /b\r\n)\r\n\r\n:: 3. Default browser fallback\r\nstart %TARGET_URL%\r\nexit /b\r\n";

            $shortcutBat = "@echo off\r\ntitle Install Shree Giriraj ERP Desktop Shortcut\r\necho Creating Desktop Shortcut...\r\npowershell -NoProfile -Command \"\$ws = New-Object -ComObject WScript.Shell; \$desktopPath = [Environment]::GetFolderPath('Desktop'); \$shortcut = \$ws.CreateShortcut([System.IO.Path]::Combine(\$desktopPath, 'Shree Giriraj Poly Plast ERP.lnk')); \$shortcut.TargetPath = [System.IO.Path]::Combine('%~dp0', 'Launch_Shree_Giriraj_ERP.bat'); \$shortcut.WorkingDirectory = '%~dp0'; \$shortcut.Description = 'Shree Giriraj Poly Plast ERP Desktop Application'; \$shortcut.Save(); Write-Host 'Desktop Shortcut Created Successfully!' -ForegroundColor Green\"\r\necho.\r\necho Shortcut created on your Windows Desktop!\r\ntimeout /t 3\r\n";

            $readme = "===================================================================\r\n  SHREE GIRIRAJ POLY PLAST ERP - WINDOWS DESKTOP SETUP\r\n===================================================================\r\n\r\nINSTALLATION & USAGE:\r\n1. Double-click \"Launch_Shree_Giriraj_ERP.bat\" to start the Desktop Application.\r\n2. Double-click \"Install_Desktop_Shortcut.bat\" to place a 1-click icon on your Windows Desktop.\r\n\r\nFEATURES:\r\n- Opens in dedicated native desktop window without browser bars.\r\n- Instant access to Live ERP.\r\n- Fast thermal invoice printing and barcode scanning.\r\n- Compatible with Windows 10 and Windows 11 (64-bit / 32-bit).\r\n";

            $zip->addFromString('Launch_Shree_Giriraj_ERP.bat', $launcherBat);
            $zip->addFromString('Install_Desktop_Shortcut.bat', $shortcutBat);
            $zip->addFromString('README.txt', $readme);
            $zip->close();

            return response()->download($zipPath, 'Shree-Giriraj-ERP-Desktop-Setup.zip', [
                'Content-Type' => 'application/zip',
            ]);
        }

        return redirect()->route('downloads.index')->with('notice', 'Unable to create zip archive. Please use the 1-click PWA installer.');
    }

    /**
     * Download Android Mobile APK
     */
    public function mobile()
    {
        $possiblePaths = [
            public_path('ShreeGirirajERP.apk'),
            public_path('downloads/ShreeGirirajERP.apk'),
            base_path('../ShreeGirirajERP.apk'),
            base_path('../ShreeGiriraj-ERP.apk'),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return response()->download($path, 'ShreeGirirajERP.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                ]);
            }
        }

        return redirect()->route('downloads.index')->with('notice', 'Android APK package is being prepared. You can also add the Web App directly to your Android home screen!');
    }
}
