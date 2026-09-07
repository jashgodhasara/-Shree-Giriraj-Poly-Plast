<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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
        $desktopSize = $desktopExists ? round(File::size($desktopZip) / (1024 * 1024), 1) : 110.8;

        $apkExists = File::exists($mobileApk);
        $apkSize = $apkExists ? round(File::size($mobileApk) / (1024 * 1024), 1) : 135.3;

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

        // If not found locally on disk (e.g. Render cloud without binary), redirect to downloads hub with notice
        return redirect()->route('downloads.index')->with('notice', 'Desktop App setup package is being configured. You can also install the application with 1-click PWA right now below!');
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
