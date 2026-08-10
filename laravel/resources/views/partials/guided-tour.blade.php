@if(request()->has('tour'))
<div class="card mb-4" id="guidedTourBanner" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border: 1.5px solid rgba(99,102,241,0.4); color: #fff; box-shadow: 0 8px 24px rgba(99,102,241,0.25);">
    <div class="card-body" style="padding: 20px 24px;">
        <div class="d-flex justify-between align-center mb-3">
            <div class="d-flex align-center gap-2">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff;">
                    <i class="fa fa-compass"></i>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #fff;">Welcome to Your Custom AI ERP!</h3>
                    <p style="font-size: 12px; color: #c7d2fe; margin-top: 2px;">Your ERP workspace is ready. Complete your first 3 tasks below to get started:</p>
                </div>
            </div>
            <button class="btn btn-ghost btn-icon" style="color: #c7d2fe;" onclick="document.getElementById('guidedTourBanner').remove()">✕</button>
        </div>

        <div class="form-row cols-3">
            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 14px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">1</div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: #fff;">Add First Product</div>
                    <a href="{{ route('products.index') }}" style="font-size: 11px; color: #818cf8; text-decoration: underline;">Open Product Master &rarr;</a>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 14px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #6366f1; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">2</div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: #fff;">Create First Bill</div>
                    <a href="{{ route('invoices.create') }}" style="font-size: 11px; color: #818cf8; text-decoration: underline;">Open POS Billing &rarr;</a>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 14px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">3</div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: #fff;">Test UPI Auto-Detect</div>
                    <a href="{{ route('invoices.create') }}?upi=1" style="font-size: 11px; color: #818cf8; text-decoration: underline;">Test GPay/Paytm QR &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
