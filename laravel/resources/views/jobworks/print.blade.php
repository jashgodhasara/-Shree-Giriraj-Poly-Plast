<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Work Challan - {{ $jobWorkOrder->job_work_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f1f5f9; color: #1e293b; padding: 20px; font-size: 13px; line-height: 1.5; }
        .page { background: #fff; max-width: 800px; margin: 0 auto; padding: 32px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
        .company-title { font-size: 22px; font-weight: 800; color: #4338ca; text-transform: uppercase; letter-spacing: 0.5px; }
        .company-sub { font-size: 12px; color: #64748b; margin-top: 4px; }
        .doc-badge { text-align: right; }
        .doc-title { font-size: 18px; font-weight: 800; color: #1e293b; text-transform: uppercase; }
        .doc-num { font-size: 14px; font-weight: 700; color: #4338ca; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .info-box { background: #f8fafc; padding: 14px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .info-box-title { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 8px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; font-weight: 700; text-align: left; padding: 10px 8px; font-size: 11.5px; text-transform: uppercase; color: #475569; border: 1px solid #e2e8f0; }
        td { padding: 9px 8px; border: 1px solid #e2e8f0; font-size: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals-section { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 30px; }
        .totals-table { width: 320px; margin-left: auto; }
        .totals-table td { padding: 6px 10px; border: none; }
        .totals-table .grand-total { font-size: 15px; font-weight: 800; color: #4338ca; border-top: 2px solid #e2e8f0; border-bottom: 2px solid #e2e8f0; }
        
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 20px; }
        .sig-box { text-align: center; border-top: 1px dashed #94a3b8; padding-top: 8px; font-size: 12px; font-weight: 600; color: #475569; }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; padding: 15mm; border-radius: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width: 800px; margin: 0 auto 16px auto; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('jobworks.show', $jobWorkOrder) }}" style="color: #4338ca; text-decoration: none; font-weight: 600; font-size: 13px;">← Back to Job Work</a>
    <button onclick="window.print()" style="background: #4338ca; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">
        Print / Save as PDF
    </button>
</div>

<div class="page">
    <div class="header">
        <div>
            <div class="company-title">Shree Giriraj Poly Plast</div>
            <div class="company-sub">
                Manufacturers of Quality Plastic Products &amp; Job Work Processing<br>
                Plot No. 12, Industrial Area, Gujarat, India • Phone: +91 98250 XXXXX<br>
                GSTIN: 24AAAAA0000A1Z5
            </div>
        </div>
        <div class="doc-badge">
            <div class="doc-title">Job Work Challan</div>
            <div class="doc-num">{{ $jobWorkOrder->job_work_number }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Status: <strong>{{ $jobWorkOrder->status }}</strong></div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-box-title">Client / Party Details</div>
            <div style="font-weight: 700; font-size: 14px;">{{ $jobWorkOrder->client->name }}</div>
            @if($jobWorkOrder->client->company_name)<div>{{ $jobWorkOrder->client->company_name }}</div>@endif
            @if($jobWorkOrder->client->phone)<div>Phone: {{ $jobWorkOrder->client->phone }}</div>@endif
            @if($jobWorkOrder->client->gstin)<div>GSTIN: <strong>{{ $jobWorkOrder->client->gstin }}</strong></div>@endif
            @if($jobWorkOrder->client->address)<div style="margin-top: 4px; color: #64748b;">{{ $jobWorkOrder->client->address }}</div>@endif
        </div>

        <div class="info-box">
            <div class="info-box-title">Order Information</div>
            <div>Order Date: <strong>{{ $jobWorkOrder->order_date->format('d M Y') }}</strong></div>
            @if($jobWorkOrder->due_date)<div>Delivery Due: <strong>{{ $jobWorkOrder->due_date->format('d M Y') }}</strong></div>@endif
            @if($jobWorkOrder->reference_number)<div>Client Ref / Challan: <strong>{{ $jobWorkOrder->reference_number }}</strong></div>@endif
            <div>Rounding Method: <strong>{{ ucfirst($jobWorkOrder->rounding_method) }}</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Product Description</th>
                <th class="text-center" style="width: 12%;">Piece Weight</th>
                <th class="text-center" style="width: 14%;">Material Recd</th>
                <th class="text-center" style="width: 11%;">Gross PCS</th>
                <th class="text-center" style="width: 11%;">Wastage</th>
                <th class="text-center" style="width: 12%;">Net Finished</th>
                <th class="text-right" style="width: 10%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobWorkOrder->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->product->name }}</strong>
                    @if($item->product->sku)<div style="font-size: 10.5px; color: #64748b;">SKU: {{ $item->product->sku }}</div>@endif
                </td>
                <td class="text-center">{{ $item->product_weight }} {{ $item->product_weight_unit }}</td>
                <td class="text-center"><strong>{{ number_format($item->received_weight, 2) }} {{ $item->received_weight_unit }}</strong></td>
                <td class="text-center">{{ number_format($item->gross_quantity) }}</td>
                <td class="text-center">{{ number_format($item->wastage_quantity) }} ({{ $item->wastage_percentage }}%)</td>
                <td class="text-center"><strong style="color: #059669;">{{ number_format($item->net_quantity) }} PCS</strong></td>
                <td class="text-right fw-bold">₹{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div style="flex: 1; font-size: 12px; color: #475569;">
            <strong>Material Summary:</strong><br>
            • Total Received Material: <strong>{{ number_format($jobWorkOrder->total_received_weight_kg, 2) }} KG</strong><br>
            • Total Calculated Gross: <strong>{{ number_format($jobWorkOrder->total_gross_pieces) }} PCS</strong><br>
            • Total Wastage Allowance: <strong>{{ number_format($jobWorkOrder->total_wastage_pieces) }} PCS</strong><br>
            • Total Net Production: <strong>{{ number_format($jobWorkOrder->total_net_pieces) }} PCS</strong><br>
            @if($jobWorkOrder->remarks)
                <div style="margin-top: 10px; padding: 8px; background: #f8fafc; border-left: 3px solid #cbd5e1;">
                    <strong>Notes:</strong> {{ $jobWorkOrder->remarks }}
                </div>
            @endif
        </div>

        <table class="totals-table">
            <tr>
                <td class="text-right text-muted">Subtotal:</td>
                <td class="text-right"><strong>₹{{ number_format($jobWorkOrder->subtotal, 2) }}</strong></td>
            </tr>
            @if($jobWorkOrder->additional_charges > 0)
            <tr>
                <td class="text-right text-muted">Additional / Freight:</td>
                <td class="text-right">+ ₹{{ number_format($jobWorkOrder->additional_charges, 2) }}</td>
            </tr>
            @endif
            @if($jobWorkOrder->discount > 0)
            <tr>
                <td class="text-right text-muted">Discount:</td>
                <td class="text-right">- ₹{{ number_format($jobWorkOrder->discount, 2) }}</td>
            </tr>
            @endif
            @if($jobWorkOrder->tax > 0)
            <tr>
                <td class="text-right text-muted">Tax / GST:</td>
                <td class="text-right">+ ₹{{ number_format($jobWorkOrder->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="text-right">Grand Total:</td>
                <td class="text-right">₹{{ number_format($jobWorkOrder->grand_total, 2) }}</td>
            </tr>
            @if($jobWorkOrder->paid_amount > 0)
            <tr>
                <td class="text-right text-muted">Advance Paid:</td>
                <td class="text-right">₹{{ number_format($jobWorkOrder->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right text-muted" style="color: #ef4444; font-weight: 700;">Balance Due:</td>
                <td class="text-right" style="color: #ef4444; font-weight: 700;">₹{{ number_format($jobWorkOrder->balance_amount, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="signatures">
        <div class="sig-box">
            Customer / Receiver Signature
        </div>
        <div class="sig-box">
            For, Shree Giriraj Poly Plast (Authorized Signatory)
        </div>
    </div>
</div>

</body>
</html>
