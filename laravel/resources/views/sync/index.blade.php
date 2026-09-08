@extends('layouts.app')

@section('title', 'Cloud & Offline Sync Center')

@section('content')
<div class="content-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-arrows-rotate" style="color: #6366f1;"></i> Cloud &amp; Offline Sync Center
        </h1>
        <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
            Work offline without internet. Automatically upload bills, payments, and stock to Render Cloud when connected.
        </p>
    </div>
    <div>
        <button id="btnTriggerSyncMain" class="btn" style="background: linear-gradient(135deg, #4f46e5, #6366f1); color: white; border-radius: 12px; padding: 12px 20px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(99,102,241,0.3);">
            <i class="fa-solid fa-rotate" id="syncMainSpinner"></i>
            <span id="syncMainText">Sync All Data Now</span>
        </button>
    </div>
</div>

<div id="syncAlertBanner" style="display: none; padding: 16px 20px; border-radius: 14px; margin-bottom: 24px; font-size: 14px; font-weight: 500;"></div>

<!-- Status Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <!-- Connection Status -->
    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 22px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 13px; font-weight: 600; color: #64748b;">CLOUD CONNECTIVITY</span>
            <span id="badgeConnectivity" style="font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: {{ $status['is_online'] ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' }}; color: {{ $status['is_online'] ? '#059669' : '#dc2626' }};">
                {{ $status['is_online'] ? '🟢 Connected (Online)' : '🔴 Offline Mode' }}
            </span>
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">{{ $status['cloud_url'] }}</h3>
        <p style="font-size: 12.5px; color: #64748b; margin: 0;">
            {{ $status['is_online'] ? 'Direct high-speed link with Live Render Cloud ERP active.' : 'Working in local offline cache. Changes will upload when connected.' }}
        </p>
    </div>

    <!-- Last Synced Time -->
    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 22px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 13px; font-weight: 600; color: #64748b;">LAST SYNC TIME</span>
            <i class="fa-regular fa-clock" style="color: #6366f1;"></i>
        </div>
        <h3 id="displayLastSync" style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
            {{ $status['last_sync'] ?? 'Not synced yet' }}
        </h3>
        <p style="font-size: 12.5px; color: #64748b; margin: 0;">
            Result: <strong style="color: #10b981;">{{ $status['last_result'] ?? 'Pending' }}</strong>
        </p>
    </div>

    <!-- Local Records Count -->
    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 22px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 13px; font-weight: 600; color: #64748b;">LOCAL RECORD STATUS</span>
            <i class="fa-solid fa-database" style="color: #6366f1;"></i>
        </div>
        <div style="display: flex; gap: 14px; font-size: 13px;">
            <div><strong style="font-size: 16px; color: #0f172a;">{{ $status['pending_invoices'] }}</strong> Invoices</div>
            <div><strong style="font-size: 16px; color: #0f172a;">{{ $status['pending_customers'] }}</strong> Customers</div>
            <div><strong style="font-size: 16px; color: #0f172a;">{{ $status['pending_products'] }}</strong> Products</div>
        </div>
        <p style="font-size: 12px; color: #64748b; margin-top: 8px;">Auto-replicates across all your devices.</p>
    </div>
</div>

<!-- How Auto-Sync Works Guide -->
<div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: var(--shadow-sm);">
    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-wand-magic-sparkles" style="color: #6366f1;"></i> Intelligent Auto-Sync Rules
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; font-size: 13.5px; color: #475569;">
        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                <i class="fa-solid fa-wifi" style="color: #10b981;"></i> Automatic Network Reconnect
            </div>
            <p style="margin: 0; line-height: 1.4;">When you turn on Wi-Fi or plug in internet on your laptop, the ERP immediately uploads all bills and new clients to Cloud.</p>
        </div>
        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                <i class="fa-solid fa-shield-halved" style="color: #6366f1;"></i> Zero Data Loss Guarantee
            </div>
            <p style="margin: 0; line-height: 1.4;">All invoices receive permanent sequence numbers locally so you can print GST bills immediately without waiting for server connection.</p>
        </div>
        <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Two-Way Reconciliation
            </div>
            <p style="margin: 0; line-height: 1.4;">New items created from the mobile app or cloud dashboard automatically download to this laptop.</p>
        </div>
    </div>
</div>

<script>
    const btnTriggerSyncMain = document.getElementById('btnTriggerSyncMain');
    const syncMainSpinner = document.getElementById('syncMainSpinner');
    const syncMainText = document.getElementById('syncMainText');
    const syncAlertBanner = document.getElementById('syncAlertBanner');
    const displayLastSync = document.getElementById('displayLastSync');

    async function runCloudSync() {
        if (!btnTriggerSyncMain) return;
        btnTriggerSyncMain.disabled = true;
        syncMainSpinner.classList.add('fa-spin');
        syncMainText.textContent = 'Synchronizing with Cloud...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route('sync.trigger') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();

            syncAlertBanner.style.display = 'block';
            if (data.success) {
                syncAlertBanner.style.background = 'rgba(16, 185, 129, 0.12)';
                syncAlertBanner.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                syncAlertBanner.style.color = '#065f46';
                syncAlertBanner.innerHTML = `<strong>✅ Sync Successful!</strong> ${data.message}<br><small>Invoices synced: ${data.report?.invoices_synced || 0}, Customers: ${data.report?.customers_synced || 0}, Products: ${data.report?.products_synced || 0}</small>`;
                displayLastSync.textContent = data.timestamp || 'Just now';
            } else {
                syncAlertBanner.style.background = 'rgba(239, 68, 68, 0.12)';
                syncAlertBanner.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                syncAlertBanner.style.color = '#991b1b';
                syncAlertBanner.innerHTML = `<strong>⚠️ Sync Failed:</strong> ${data.message}`;
            }
        } catch (err) {
            syncAlertBanner.style.display = 'block';
            syncAlertBanner.style.background = 'rgba(239, 68, 68, 0.12)';
            syncAlertBanner.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            syncAlertBanner.style.color = '#991b1b';
            syncAlertBanner.innerHTML = `<strong>⚠️ Network Error:</strong> Unable to connect to sync endpoint.`;
        } finally {
            btnTriggerSyncMain.disabled = false;
            syncMainSpinner.classList.remove('fa-spin');
            syncMainText.textContent = 'Sync All Data Now';
        }
    }

    if (btnTriggerSyncMain) {
        btnTriggerSyncMain.addEventListener('click', runCloudSync);
    }
</script>
@endsection
