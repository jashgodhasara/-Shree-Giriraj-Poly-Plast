@extends('layouts.app')

@section('title', 'Download Apps & Desktop Setup')

@section('content')
<div class="content-header" style="margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-cloud-arrow-down" style="color: #6366f1;"></i> Download Apps &amp; Clients
        </h1>
        <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
            Get native desktop and mobile applications for Shree Giriraj Poly Plast ERP.
        </p>
    </div>
</div>

@if(session('notice'))
<div style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; color: #b45309;">
    <i class="fa fa-circle-info" style="font-size: 18px;"></i>
    <span style="font-weight: 500;">{{ session('notice') }}</span>
</div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 30px;">
    
    <!-- 1. Windows Desktop App Card -->
    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
        
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 18px;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(99,102,241,0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                <i class="fa-brands fa-windows"></i>
            </div>
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Windows Desktop App</h2>
                <span style="font-size: 12px; font-weight: 600; color: #6366f1; background: rgba(99,102,241,0.08); padding: 2px 8px; border-radius: 6px;">Windows 10 / 11 (64-bit)</span>
            </div>
        </div>

        <p style="color: #64748b; font-size: 13.5px; line-height: 1.5; margin-bottom: 20px; flex-grow: 1;">
            Full standalone desktop application powered by Electron. Includes dedicated window, offline cache, thermal invoice printing shortcuts, and hardware acceleration.
        </p>

        <div style="background: #f8fafc; border-radius: 10px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #475569;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span>Package Type:</span>
                <strong style="color: #0f172a;">Portable Setup (.ZIP)</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Size:</span>
                <strong style="color: #0f172a;">~{{ $desktopSize }} MB</strong>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('downloads.desktop') }}" class="btn" style="background: #4f46e5; color: white; border-radius: 10px; padding: 12px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 4px rgba(79,70,229,0.25);">
                <i class="fa fa-download"></i> Download Windows Setup (.zip)
            </a>
            
            <button id="btnPwaDesktop" class="btn" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                <i class="fa fa-laptop-code" style="color: #6366f1;"></i> 1-Click Install as App (Instant)
            </button>
        </div>
    </div>

    <!-- 2. Android Mobile App Card -->
    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #10b981, #059669);"></div>
        
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 18px;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16,185,129,0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                <i class="fa-brands fa-android"></i>
            </div>
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Android Mobile App</h2>
                <span style="font-size: 12px; font-weight: 600; color: #059669; background: rgba(16,185,129,0.08); padding: 2px 8px; border-radius: 6px;">Android 8.0+ (APK)</span>
            </div>
        </div>

        <p style="color: #64748b; font-size: 13.5px; line-height: 1.5; margin-bottom: 20px; flex-grow: 1;">
            Mobile app for factory supervisors, dispatch team, and sales agents on the go. Optimized for handheld barcode scanning and quick stock lookups.
        </p>

        <div style="background: #f8fafc; border-radius: 10px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #475569;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span>Package Type:</span>
                <strong style="color: #0f172a;">Android Package (.APK)</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Size:</span>
                <strong style="color: #0f172a;">~{{ $apkSize }} MB</strong>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('downloads.mobile') }}" class="btn" style="background: #059669; color: white; border-radius: 10px; padding: 12px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">
                <i class="fa fa-download"></i> Download Android APK
            </a>
            
            <a href="{{ url('/') }}" class="btn" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fa fa-mobile-screen" style="color: #059669;"></i> Add to Mobile Home Screen
            </a>
        </div>
    </div>

    <!-- 3. Web & Cloud Access -->
    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #0284c7, #38bdf8);"></div>
        
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 18px;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(2,132,199,0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                <i class="fa fa-globe"></i>
            </div>
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Cloud &amp; Web Portal</h2>
                <span style="font-size: 12px; font-weight: 600; color: #0284c7; background: rgba(2,132,199,0.08); padding: 2px 8px; border-radius: 6px;">Universal (Any Device)</span>
            </div>
        </div>

        <p style="color: #64748b; font-size: 13.5px; line-height: 1.5; margin-bottom: 20px; flex-grow: 1;">
            Access full ERP features directly in Google Chrome, Microsoft Edge, Safari, or Firefox without installing any setup files.
        </p>

        <div style="background: #f8fafc; border-radius: 10px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #475569;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span>Cloud Server:</span>
                <strong style="color: #0284c7;">Active &amp; Synced</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Compatibility:</span>
                <strong style="color: #0f172a;">PC, Mac, iPhone, Android</strong>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('dashboard') }}" class="btn" style="background: #0284c7; color: white; border-radius: 10px; padding: 12px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fa fa-arrow-right"></i> Open Web Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Installation Guide Section -->
<div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fa fa-circle-question" style="color: #6366f1;"></i> How to Install as Desktop App in 5 Seconds (Recommended)
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; font-size: 13.5px; color: #475569;">
        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <span style="background: #6366f1; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">1</span>
                In Chrome / Edge Browser
            </div>
            <p style="margin: 0; line-height: 1.4;">Click the <strong>Install App icon (🖥️)</strong> located in the right side of the browser's address bar.</p>
        </div>

        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <span style="background: #6366f1; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">2</span>
                Click 'Install'
            </div>
            <p style="margin: 0; line-height: 1.4;">A prompt will say <em>"Install Shree Giriraj ERP?"</em>. Click <strong>Install</strong> to add it to your Windows Start Menu &amp; Desktop.</p>
        </div>

        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <span style="background: #6366f1; color: white; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">3</span>
                Launch Like Native App
            </div>
            <p style="margin: 0; line-height: 1.4;">Opens in a dedicated full-screen window with taskbar pinning and instant automatic updates.</p>
        </div>
    </div>
</div>

<script>
    let deferredPrompt;
    const btnPwaDesktop = document.getElementById('btnPwaDesktop');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (btnPwaDesktop) {
            btnPwaDesktop.style.background = '#4f46e5';
            btnPwaDesktop.style.color = '#ffffff';
        }
    });

    if (btnPwaDesktop) {
        btnPwaDesktop.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
            } else {
                alert('To install as Desktop App:\n1. Click the (⋮) menu or Install icon in your browser address bar.\n2. Select "Install Shree Giriraj ERP" / "Save and Share -> Install as App".');
            }
        });
    }
</script>
@endsection
