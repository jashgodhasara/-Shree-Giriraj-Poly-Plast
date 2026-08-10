<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Smart ERP Configurator - Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .wizard-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; width: 100%; max-width: 680px; padding: 40px; box-shadow: 0 25px 80px rgba(0,0,0,0.5); position: relative; overflow: hidden; }
        .wizard-card::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, transparent 70%); border-radius: 50%; }
        .brand-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
        .logo-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
        .step-progress { display: flex; gap: 8px; margin-bottom: 30px; }
        .progress-dot { flex: 1; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.1); transition: all 0.3s; }
        .progress-dot.active { background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 0 10px rgba(99,102,241,0.6); }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        h2 { font-size: 24px; font-weight: 800; margin-bottom: 8px; color: #fff; }
        p.desc { font-size: 14px; color: #94a3b8; margin-bottom: 24px; }
        .grid-options { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
        .option-card { background: rgba(255,255,255,0.04); border: 2px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 18px; cursor: pointer; transition: all 0.2s; display: flex; align-items: flex-start; gap: 14px; }
        .option-card:hover { border-color: #818cf8; background: rgba(99,102,241,0.08); transform: translateY(-2px); }
        .option-card.selected { border-color: #6366f1; background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.1)); box-shadow: 0 4px 16px rgba(99,102,241,0.25); }
        .opt-icon { width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; font-size: 18px; color: #818cf8; flex-shrink: 0; }
        .option-card.selected .opt-icon { background: #6366f1; color: #fff; }
        .opt-title { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 2px; }
        .opt-sub { font-size: 11px; color: #94a3b8; }
        .btn-group { display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 12px 24px; border-radius: 10px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; font-family: inherit; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; box-shadow: 0 4px 16px rgba(99,102,241,0.4); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.6); }
        .btn-ghost { background: transparent; color: #94a3b8; }
        .btn-ghost:hover { color: #fff; }

        /* Assembly Screen */
        .assembly-box { text-align: center; padding: 30px 10px; }
        .assembly-spinner { width: 64px; height: 64px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.1); border-top-color: #6366f1; animation: spin 1s infinite linear; margin: 0 auto 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .assembly-tasks { margin-top: 20px; text-align: left; max-width: 320px; margin-left: auto; margin-right: auto; }
        .task-item { font-size: 13px; color: #94a3b8; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
        .task-item i { color: #10b981; opacity: 0; transition: opacity 0.3s; }
        .task-item.done i { opacity: 1; }
        .task-item.done { color: #fff; }
    </style>
</head>
<body>

<div class="wizard-card">
    <div class="brand-logo">
        <div class="logo-icon"><i class="fa fa-wand-magic-sparkles"></i></div>
        <div>
            <h3 style="font-size: 16px; font-weight: 800;">Smart ERP Configurator</h3>
            <small style="color:#94a3b8; font-size: 11px;">Powered by AI Engine · No Technical Skills Needed</small>
        </div>
    </div>

    <div class="step-progress">
        <div class="progress-dot active" id="dot-1"></div>
        <div class="progress-dot" id="dot-2"></div>
        <div class="progress-dot" id="dot-3"></div>
        <div class="progress-dot" id="dot-4"></div>
    </div>

    <form id="wizardForm">
        <!-- STEP 1: Business Type -->
        <div class="step-content active" id="step-1">
            <h2>What kind of business do you run?</h2>
            <p class="desc">Tap your business type and the AI will pick the perfect tools for you.</p>

            <div class="grid-options">
                <div class="option-card selected" onclick="selectRadio(this, 'business_type', 'manufacturing')">
                    <div class="opt-icon"><i class="fa fa-industry"></i></div>
                    <div>
                        <div class="opt-title">Manufacturing &amp; Factory</div>
                        <div class="opt-sub">Raw materials, production processing, BOM, scrap &amp; job work</div>
                    </div>
                    <input type="radio" name="business_type" value="manufacturing" checked style="display:none;">
                </div>

                <div class="option-card" onclick="selectRadio(this, 'business_type', 'kirana_retail')">
                    <div class="opt-icon"><i class="fa fa-store"></i></div>
                    <div>
                        <div class="opt-title">Kirana / Retail Shop</div>
                        <div class="opt-sub">Super-fast barcode billing, cash register, quick inventory</div>
                    </div>
                    <input type="radio" name="business_type" value="kirana_retail" style="display:none;">
                </div>

                <div class="option-card" onclick="selectRadio(this, 'business_type', 'distributor')">
                    <div class="opt-icon"><i class="fa fa-truck-field"></i></div>
                    <div>
                        <div class="opt-title">Wholesale &amp; Distributor</div>
                        <div class="opt-sub">Bulk purchase orders, customer credit limits, delivery notes</div>
                    </div>
                    <input type="radio" name="business_type" value="distributor" style="display:none;">
                </div>

                <div class="option-card" onclick="selectRadio(this, 'business_type', 'salon_restaurant')">
                    <div class="opt-icon"><i class="fa fa-utensils"></i></div>
                    <div>
                        <div class="opt-title">Restaurant / Salon / Services</div>
                        <div class="opt-sub">Service billing, daily cash reports, customer appointments</div>
                    </div>
                    <input type="radio" name="business_type" value="salon_restaurant" style="display:none;">
                </div>
            </div>

            <div class="btn-group">
                <div></div>
                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next: Core Operations <i class="fa fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 2: Core Operations -->
        <div class="step-content" id="step-2">
            <h2>Do you track Inventory &amp; Stock?</h2>
            <p class="desc">Choose your day-to-day operations so we show only relevant buttons.</p>

            <div class="grid-options">
                <div class="option-card selected" onclick="selectRadio(this, 'has_inventory', 'yes')">
                    <div class="opt-icon"><i class="fa fa-boxes-stacked"></i></div>
                    <div>
                        <div class="opt-title">Yes, Track Stock</div>
                        <div class="opt-sub">Auto-deduct stock on sales and warn on low stock levels</div>
                    </div>
                    <input type="radio" name="has_inventory" value="yes" checked style="display:none;">
                </div>

                <div class="option-card" onclick="selectRadio(this, 'has_inventory', 'no')">
                    <div class="opt-icon"><i class="fa fa-file-invoice"></i></div>
                    <div>
                        <div class="opt-title">No, Billing Only</div>
                        <div class="opt-sub">Simple fast invoices without stock tracking</div>
                    </div>
                    <input type="radio" name="has_inventory" value="no" style="display:none;">
                </div>

                <div class="option-card selected" onclick="selectRadio(this, 'has_credit', 'yes')">
                    <div class="opt-icon"><i class="fa fa-hand-holding-dollar"></i></div>
                    <div>
                        <div class="opt-title">Give Customer Credit</div>
                        <div class="opt-sub">Track who owes what, set credit limits, 30-day reminders</div>
                    </div>
                    <input type="radio" name="has_credit" value="yes" checked style="display:none;">
                </div>

                <div class="option-card" onclick="selectRadio(this, 'has_staff', 'yes')">
                    <div class="opt-icon"><i class="fa fa-users"></i></div>
                    <div>
                        <div class="opt-title">Manage Staff / Operators</div>
                        <div class="opt-sub">Track sales staff, operators, and permissions</div>
                    </div>
                    <input type="radio" name="has_staff" value="yes" checked style="display:none;">
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-ghost" onclick="prevStep(1)"><i class="fa fa-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next: Payments &amp; Setup <i class="fa fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 3: Payment Modes -->
        <div class="step-content" id="step-3">
            <h2>Payment &amp; Multi-Branch Setup</h2>
            <p class="desc">How do your customers pay and do you manage multiple shop locations?</p>

            <div class="grid-options">
                <div class="option-card selected" onclick="selectRadio(this, 'branch_count', 'single')">
                    <div class="opt-icon"><i class="fa fa-location-dot"></i></div>
                    <div>
                        <div class="opt-title">Single Shop / Office</div>
                        <div class="opt-sub">One main store location in Ahmedabad</div>
                    </div>
                    <input type="radio" name="branch_count" value="single" checked style="display:none;">
                </div>

                <div class="option-card" onclick="selectRadio(this, 'branch_count', 'multi')">
                    <div class="opt-icon"><i class="fa fa-diagram-project"></i></div>
                    <div>
                        <div class="opt-title">Multiple Branches</div>
                        <div class="opt-sub">Shared product master with inter-branch stock transfers</div>
                    </div>
                    <input type="radio" name="branch_count" value="multi" style="display:none;">
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 700; margin-bottom: 10px; display: block;">Supported Payment Modes:</label>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="payment_modes[]" value="UPI" checked style="accent-color: #6366f1;"> Instant UPI Auto-Detect (GPay / Paytm / PhonePe)
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="payment_modes[]" value="Cheque" checked style="accent-color: #6366f1;"> Cheque Tracking &amp; Clearing
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="payment_modes[]" value="Cash" checked style="accent-color: #6366f1;"> Cash &amp; Credit Ledger
                    </label>
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-ghost" onclick="prevStep(2)"><i class="fa fa-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-primary" onclick="assembleErp()"><i class="fa fa-bolt"></i> Generate My Custom ERP</button>
            </div>
        </div>

        <!-- STEP 4: AI Assembly Screen -->
        <div class="step-content" id="step-4">
            <div class="assembly-box">
                <div class="assembly-spinner"></div>
                <h2>AI Engine Assembling Your ERP...</h2>
                <p class="desc">Configuring pre-tested billing, inventory, and payment modules based on your business answers.</p>

                <div class="assembly-tasks">
                    <div class="task-item" id="t-1"><i class="fa fa-circle-check"></i> Selecting modules for your business type</div>
                    <div class="task-item" id="t-2"><i class="fa fa-circle-check"></i> Setting up UPI payment auto-verification</div>
                    <div class="task-item" id="t-3"><i class="fa fa-circle-check"></i> Configuring stock deduction &amp; credit limit rules</div>
                    <div class="task-item" id="t-4"><i class="fa fa-circle-check"></i> Building clean, simplified dashboard &amp; sidebar</div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function selectRadio(card, name, value) {
    const parent = card.parentElement;
    parent.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    const input = card.querySelector(`input[name="${name}"]`);
    if (input) input.checked = true;
}

function nextStep(step) {
    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.progress-dot').forEach(d => d.classList.remove('active'));
    document.getElementById(`step-${step}`).classList.add('active');
    for (let i = 1; i <= step; i++) {
        document.getElementById(`dot-${i}`).classList.add('active');
    }
}

function prevStep(step) {
    nextStep(step);
}

async function assembleErp() {
    nextStep(4);

    // Animate AI tasks
    setTimeout(() => document.getElementById('t-1').classList.add('done'), 500);
    setTimeout(() => document.getElementById('t-2').classList.add('done'), 1100);
    setTimeout(() => document.getElementById('t-3').classList.add('done'), 1700);
    setTimeout(() => document.getElementById('t-4').classList.add('done'), 2300);

    const formData = new FormData(document.getElementById('wizardForm'));

    try {
        const res = await fetch('{{ route("onboard.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();
        setTimeout(() => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert(data.message || 'Error configuring ERP');
            }
        }, 2800);
    } catch(err) {
        setTimeout(() => {
            window.location.href = '{{ route("dashboard") }}?tour=1';
        }, 2800);
    }
}
</script>
</body>
</html>
