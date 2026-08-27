<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order #{{ $purchaseOrder->po_number }} - Shree Giriraj Poly Plast</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; padding: 30px; font-size: 13px; line-height: 1.5; }
        .po-container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,.06); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 2px solid #6366f1; margin-bottom: 24px; }
        .company-title { font-size: 22px; font-weight: 800; color: #4f46e5; letter-spacing: -.5px; }
        .company-subtitle { font-size: 11px; color: #64748b; margin-top: 2px; }
        .po-badge { font-size: 20px; font-weight: 800; color: #0f172a; text-transform: uppercase; text-align: right; }
        .po-number { font-size: 14px; color: #6366f1; font-weight: 700; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
        .box-title { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .5px; margin-bottom: 8px; }
        .box h4 { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; background: #f1f5f9; padding: 10px 14px; text-align: left; border-bottom: 2px solid #cbd5e1; }
        td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .totals-table { width: 300px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        .totals-table tr.grand-total td { font-size: 16px; font-weight: 800; color: #4f46e5; border-top: 2px solid #4f46e5; }
        .footer-signatures { display: flex; justify-content: space-between; margin-top: 60px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .sig-box { text-align: center; width: 200px; }
        .sig-line { border-top: 1px dashed #94a3b8; margin-top: 50px; padding-top: 6px; font-size: 11px; font-weight: 600; color: #475569; }
        .no-print { text-align: center; margin-bottom: 20px; }
        @media print {
            body { background: #fff; padding: 0; }
            .po-container { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="display:flex; justify-content:center; gap:12px; margin-bottom:20px;">
    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" style="padding: 10px 20px; background: #334155; color: #fff; text-decoration:none; border-radius: 8px; font-size: 14px; font-weight: 600; display:inline-flex; align-items:center; gap:6px;">
        ← Back
    </a>
    <button onclick="exitPoPrint()" style="padding: 10px 20px; background: #475569; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
        ✕ Exit
    </button>
    <button onclick="window.print()" style="padding: 10px 24px; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit;">
        🖨 Print / Save as PDF
    </button>
</div>

<script>
function exitPoPrint() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.close();
    }
    setTimeout(function() {
        window.location.href = "{{ route('purchase-orders.index') }}";
    }, 200);
}
</script>

<div class="po-container">
    <div class="header">
        <div>
            <div class="company-title">SHREE GIRIRAJ POLY PLAST</div>
            <div class="company-subtitle">Plastic Processing &amp; Poly Manufacturing</div>
            <div style="margin-top: 6px; font-size: 11px; color: #475569;">
                Ahmedabad, Gujarat, India<br>
                Email: info@shreegiriraj.com | Phone: +91 98765 43210
            </div>
        </div>
        <div>
            <div class="po-badge">Purchase Order</div>
            <div class="po-number">{{ $purchaseOrder->po_number }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Date: {{ $purchaseOrder->po_date->format('d-M-Y') }}</div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="box">
            <div class="box-title">Vendor / Supplier Details</div>
            <h4>{{ $purchaseOrder->supplier->name ?? 'N/A' }}</h4>
            <div>GSTIN: {{ $purchaseOrder->supplier->gstin ?? 'N/A' }}</div>
            <div>Phone: {{ $purchaseOrder->supplier->phone ?? 'N/A' }}</div>
            <div>Address: {{ $purchaseOrder->supplier->address ?? 'N/A' }}</div>
        </div>
        <div class="box">
            <div class="box-title">Delivery &amp; Order Terms</div>
            <div><strong>Payment Terms:</strong> {{ $purchaseOrder->payment_terms ?? 'Standard Terms' }}</div>
            <div><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d-M-Y') : 'As Agreed' }}</div>
            <div><strong>Delivery Address:</strong> {{ $purchaseOrder->delivery_address ?? 'Factory Premises, Ahmedabad' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Description of Material</th>
                <th>HSN Code</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Rate (₹)</th>
                <th style="text-align: center;">GST</th>
                <th style="text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->material->name ?? 'Material Item' }}</strong>
                    <div style="font-size: 11px; color: #64748b;">Type: {{ $item->material->type ?? 'Raw Material' }}</div>
                </td>
                <td>{{ $item->hsn_code ?? '-' }}</td>
                <td style="text-align: center;">{{ number_format($item->quantity, 2) }} {{ $item->material->unit ?? 'Kg' }}</td>
                <td style="text-align: right;">₹{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: center;">{{ number_format($item->gst_rate, 0) }}%</td>
                <td style="text-align: right; font-weight: 600;">₹{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="text-muted">Subtotal:</td>
            <td style="text-align: right;">₹{{ number_format($purchaseOrder->subtotal, 2) }}</td>
        </tr>
        @if($purchaseOrder->igst > 0)
            <tr>
                <td class="text-muted">IGST:</td>
                <td style="text-align: right;">₹{{ number_format($purchaseOrder->igst, 2) }}</td>
            </tr>
        @else
            <tr>
                <td class="text-muted">CGST:</td>
                <td style="text-align: right;">₹{{ number_format($purchaseOrder->cgst, 2) }}</td>
            </tr>
            <tr>
                <td class="text-muted">SGST:</td>
                <td style="text-align: right;">₹{{ number_format($purchaseOrder->sgst, 2) }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td>Grand Total:</td>
            <td style="text-align: right;">₹{{ number_format($purchaseOrder->grand_total, 2) }}</td>
        </tr>
    </table>

    @if($purchaseOrder->notes)
        <div style="margin-top: 24px; padding: 12px; background: #fffbe0; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 12px;">
            <strong>Special Notes:</strong> {{ $purchaseOrder->notes }}
        </div>
    @endif

    <div class="footer-signatures">
        <div class="sig-box">
            <div class="sig-line">Vendor Acceptance</div>
        </div>
        <div class="sig-box">
            <div class="sig-line">Authorized Signatory<br>Shree Giriraj Poly Plast</div>
        </div>
    </div>
</div>

</body>
</html>
