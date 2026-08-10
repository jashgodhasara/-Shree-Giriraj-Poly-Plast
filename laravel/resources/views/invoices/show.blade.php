@extends('layouts.app')
@section('title', 'Invoice '.$invoice->invoice_number)
@section('page-title', 'Invoice Detail')

@section('content')
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">

<!-- Invoice Details -->
<div>
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-file-invoice"></i> {{ $invoice->invoice_number }}</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-outline btn-sm" target="_blank">
                <i class="fa fa-print"></i> Print Invoice
            </a>
            <a href="{{ route('invoices.challan', $invoice) }}" class="btn btn-outline btn-sm" target="_blank">
                <i class="fa fa-truck"></i> Print Challan
            </a>
            <button class="btn btn-danger btn-sm" onclick="deleteRecord('{{ route('invoices.destroy', $invoice) }}', 'invoice')">
                <i class="fa fa-trash"></i> Delete
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row cols-2" style="margin-bottom:16px;">
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">CUSTOMER</div>
                <div style="font-weight:700;font-size:15px;">{{ $invoice->customer->name }}</div>
                @if($invoice->customer->address)<div style="font-size:13px;color:var(--text-muted)">{{ $invoice->customer->address }}</div>@endif
                @if($invoice->customer->gstin)<div style="font-size:12px;">GSTIN: {{ $invoice->customer->gstin }}</div>@endif
                @if($invoice->customer->phone)<div style="font-size:12px;">Ph: {{ $invoice->customer->phone }}</div>@endif
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">INVOICE DATE</div>
                <div style="font-weight:600;">{{ $invoice->invoice_date->format('d M Y') }}</div>
                @if($invoice->transporter)
                <div style="margin-top:8px;font-size:11px;color:var(--text-muted)">TRANSPORTER</div>
                <div style="font-size:13px;font-weight:600;">{{ $invoice->transporter->name }}</div>
                @if($invoice->transporter->vehicle_no)<div style="font-size:12px;color:var(--text-muted)">{{ $invoice->transporter->vehicle_no }}</div>@endif
                @endif
                @if($invoice->lr_number)
                <div style="margin-top:4px;font-size:12px;">LR#: {{ $invoice->lr_number }}</div>
                @endif
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Product</th><th>HSN</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
                </thead>
                <tbody>
                @foreach($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="fw-bold">{{ $item->product->name }}</td>
                    <td>{{ $item->product->hsn_code ?? '—' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td>₹{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;text-align:right;font-size:13px;">
            <div>Subtotal: <strong>₹{{ number_format($invoice->subtotal, 2) }}</strong></div>
            @if($invoice->cgst > 0)
            <div>CGST: ₹{{ number_format($invoice->cgst, 2) }} | SGST: ₹{{ number_format($invoice->sgst, 2) }}</div>
            @endif
            @if($invoice->igst > 0)
            <div>IGST: ₹{{ number_format($invoice->igst, 2) }}</div>
            @endif
            <div style="font-size:18px;font-weight:700;margin-top:6px;color:var(--primary)">Grand Total: ₹{{ number_format($invoice->grand_total, 2) }}</div>
        </div>

        @if($invoice->notes)
        <div style="margin-top:12px;padding:10px;background:#f8fafc;border-radius:6px;font-size:13px;color:var(--text-muted)">
            <strong>Notes:</strong> {{ $invoice->notes }}
        </div>
        @endif
    </div>
</div>

<!-- Payment History -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-money-bill-wave"></i> Payment History</h3>
    </div>
    <div class="card-body" style="padding:0">
        @if($invoice->payments->isEmpty())
        <div class="empty-state" style="padding:20px"><i class="fa fa-money-bill"></i><p>No payments recorded yet.</p></div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Amount</th><th>Mode</th><th>Ref#</th><th>Remarks</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($invoice->payments as $pay)
                <tr>
                    <td>{{ $pay->payment_date->format('d M Y') }}</td>
                    <td class="fw-bold">₹{{ number_format($pay->amount, 2) }}</td>
                    <td><span class="badge badge-blue">{{ $pay->payment_mode }}</span></td>
                    <td>{{ $pay->reference_no ?? '—' }}</td>
                    <td>{{ $pay->remarks ?? '—' }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-icon"
                            onclick="deleteRecord('{{ route('payments.destroy', $pay) }}', 'payment')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
</div>

<!-- Payment Summary sidebar -->
<div>
<div class="card" style="position:sticky;top:20px">
    <div class="card-header"><h3><i class="fa fa-wallet"></i> Payment Status</h3></div>
    <div class="card-body">
        <div style="text-align:center;margin-bottom:16px;">
            @php $paid = $invoice->payments->sum('amount'); $pending = max(0, $invoice->grand_total - $paid); @endphp
            <span class="badge {{ $invoice->status === 'Paid' ? 'badge-green' : ($invoice->status === 'Partial' ? 'badge-orange' : 'badge-red') }}"
                  style="font-size:14px;padding:6px 16px;">
                {{ $invoice->status }}
            </span>
        </div>
        <table style="width:100%;font-size:13px;">
            <tr><td style="padding:5px 0;color:#64748b;">Invoice Total</td><td style="text-align:right;font-weight:700">₹{{ number_format($invoice->grand_total, 2) }}</td></tr>
            <tr><td style="padding:5px 0;color:#16a34a">Total Paid</td><td style="text-align:right;font-weight:700;color:#16a34a">₹{{ number_format($paid, 2) }}</td></tr>
            <tr style="border-top:2px solid var(--border)"><td style="padding:8px 0;font-weight:700;color:#dc2626">Pending</td><td style="text-align:right;font-weight:700;font-size:16px;color:#dc2626">₹{{ number_format($pending, 2) }}</td></tr>
        </table>

        @if($pending > 0)
        <button class="btn btn-primary w-full" style="margin-top:16px" onclick="openModal('addPaymentModal')">
            <i class="fa fa-plus"></i> Record Payment
        </button>
        @else
        <div style="margin-top:16px;text-align:center;color:#16a34a;font-weight:600;font-size:13px;">
            <i class="fa fa-circle-check"></i> Fully Paid
        </div>
        @endif

        <a href="{{ route('invoices.index') }}" class="btn btn-outline w-full" style="margin-top:8px;justify-content:center;">
            <i class="fa fa-arrow-left"></i> Back to Invoices
        </a>
    </div>
</div>
</div>

</div>

<!-- Add Payment Modal -->
<div class="modal-overlay" id="addPaymentModal">
    <div class="modal" style="max-width:480px">
        <div class="modal-header">
            <h3>Record Payment</h3>
            <button class="modal-close" onclick="closeModal('addPaymentModal')">✕</button>
        </div>
        <form id="addPaymentForm">
            <div class="modal-body">
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $pending }}" value="{{ number_format($pending, 2, '.', '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Payment Mode *</label>
                    <select name="payment_mode" required>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="NEFT">NEFT</option>
                        <option value="RTGS">RTGS</option>
                        <option value="UPI">UPI</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reference / Cheque No.</label>
                    <input type="text" name="reference_no" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addPaymentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('addPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('payments.store') }}', 'POST');
});
</script>
@endsection
