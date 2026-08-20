@extends('layouts.app')
@section('title', 'Ledger / Book Keeping')
@section('page-title', 'Ledger / Book Keeping')

@section('content')

{{-- Date Filter Bar --}}
@include('partials.date-filter', ['action' => route('ledger.index')])

{{-- Summary Cards — always visible --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;">

    {{-- Total Entries --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px 20px;
                box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:14px;">
        <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                    display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;
                    box-shadow:0 4px 12px rgba(99,102,241,.3);">
            <i class="fa fa-list"></i>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);">Entries</div>
            <div style="font-size:24px;font-weight:800;color:var(--primary);">{{ number_format($totalCount) }}</div>
        </div>
    </div>

    {{-- Total Credit --}}
    <div style="background:#fff;border:2px solid #bbf7d0;border-radius:12px;padding:16px 20px;
                box-shadow:0 2px 8px rgba(16,185,129,.1);display:flex;align-items:center;gap:14px;
                position:relative;overflow:hidden;">
        <div style="position:absolute;top:-12px;right:-12px;width:70px;height:70px;border-radius:50%;
                    background:rgba(16,185,129,.08);"></div>
        <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);
                    display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;
                    box-shadow:0 4px 12px rgba(16,185,129,.3);">
            <i class="fa fa-arrow-down"></i>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#065f46;">Total Credit</div>
            <div style="font-size:22px;font-weight:800;color:#10b981;">₹{{ number_format($totalCredit, 2) }}</div>
            <div style="font-size:10px;color:#6ee7b7;margin-top:1px;">Money received / income</div>
        </div>
    </div>

    {{-- Total Debit --}}
    <div style="background:#fff;border:2px solid #fecaca;border-radius:12px;padding:16px 20px;
                box-shadow:0 2px 8px rgba(239,68,68,.1);display:flex;align-items:center;gap:14px;
                position:relative;overflow:hidden;">
        <div style="position:absolute;top:-12px;right:-12px;width:70px;height:70px;border-radius:50%;
                    background:rgba(239,68,68,.08);"></div>
        <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#ef4444,#dc2626);
                    display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;
                    box-shadow:0 4px 12px rgba(239,68,68,.3);">
            <i class="fa fa-arrow-up"></i>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#991b1b;">Total Debit</div>
            <div style="font-size:22px;font-weight:800;color:#ef4444;">₹{{ number_format($totalDebit, 2) }}</div>
            <div style="font-size:10px;color:#fca5a5;margin-top:1px;">Money paid / expense</div>
        </div>
    </div>

    {{-- Net Balance --}}
    @php $net = $totalCredit - $totalDebit; $isPositive = $net >= 0; @endphp
    <div style="background:#fff;border:2px solid {{ $isPositive ? '#bbf7d0' : '#fecaca' }};border-radius:12px;
                padding:16px 20px;box-shadow:0 2px 8px rgba(0,0,0,.08);
                display:flex;align-items:center;gap:14px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-12px;right:-12px;width:70px;height:70px;border-radius:50%;
                    background:{{ $isPositive ? 'rgba(16,185,129,.08)' : 'rgba(239,68,68,.08)' }};"></div>
        <div style="width:46px;height:46px;border-radius:12px;
                    background:{{ $isPositive ? 'linear-gradient(135deg,#059669,#047857)' : 'linear-gradient(135deg,#dc2626,#b91c1c)' }};
                    display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;
                    box-shadow:0 4px 12px {{ $isPositive ? 'rgba(16,185,129,.3)' : 'rgba(239,68,68,.3)' }};">
            <i class="fa fa-{{ $isPositive ? 'scale-balanced' : 'triangle-exclamation' }}"></i>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
                        color:{{ $isPositive ? '#065f46' : '#991b1b' }};">Net Balance</div>
            <div style="font-size:22px;font-weight:800;color:{{ $isPositive ? '#10b981' : '#ef4444' }};">
                {{ $isPositive ? '+' : '-' }}₹{{ number_format(abs($net), 2) }}
            </div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">
                {{ $isPositive ? 'Net receivable' : 'Net payable' }}
                @if($dateFrom || $preset)
                <span style="color:var(--primary);font-weight:600"> · Filtered</span>
                @else
                <span style="font-weight:600"> · All time</span>
                @endif
            </div>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-book"></i> Ledger Entries</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addLedgerModal')">
            <i class="fa fa-plus"></i> Add Entry
        </button>
    </div>
    <div class="card-body" style="padding:0">
        @if($entries->isEmpty())
        <div class="empty-state">
            <i class="fa fa-calendar-times"></i>
            <p>No ledger entries found{{ $preset ? ' for this period' : '' }}.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Entity Type</th><th>Party</th><th>Type</th><th>Amount</th><th>HSN Code</th><th>CSM Code</th><th>Description</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($entries as $e)
                <tr>
                    <td>{{ $e->transaction_date->format('d M Y') }}</td>
                    <td><span class="badge badge-blue">{{ $e->entity_type }}</span></td>
                    <td>{{ $e->entityName() }}</td>
                    <td>
                        <span class="badge {{ $e->type === 'Credit' ? 'badge-green' : 'badge-red' }}">
                            {{ $e->type }}
                        </span>
                    </td>
                    <td class="fw-bold">₹{{ number_format($e->amount, 2) }}</td>
                    <td>{{ $e->hsn_code ?? '—' }}</td>
                    <td>{{ $e->csm_code ?? '—' }}</td>
                    <td>{{ Str::limit($e->description, 40) ?? '—' }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-icon"
                            onclick="deleteRecord('{{ route('ledger.destroy', $e) }}', 'ledger entry')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:linear-gradient(to right,#f8faff,#f0fdf4);font-weight:700;font-size:13px;">
                        <td colspan="4" style="padding:12px 16px;text-align:right;color:var(--text-muted);font-size:12px;letter-spacing:.5px;text-transform:uppercase;">
                            {{ $dateFrom || $preset ? 'Period Total' : 'All-Time Total' }}
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-size:11px;color:#10b981;font-weight:700">↓ Credit: ₹{{ number_format($totalCredit,2) }}</div>
                            <div style="font-size:11px;color:#ef4444;font-weight:700;margin-top:2px">↑ Debit: ₹{{ number_format($totalDebit,2) }}</div>
                            @php $net = $totalCredit - $totalDebit; @endphp
                            <div style="font-size:12px;font-weight:800;color:{{ $net>=0?'#059669':'#dc2626' }};margin-top:4px;border-top:1px solid var(--border);padding-top:4px;">
                                Net: {{ $net>=0?'+':'-' }}₹{{ number_format(abs($net),2) }}
                            </div>
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div style="padding:12px 20px;">
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addLedgerModal">
    <div class="modal" style="max-width:560px">
        <div class="modal-header">
            <h3>Add Ledger Entry</h3>
            <button class="modal-close" onclick="closeModal('addLedgerModal')">✕</button>
        </div>
        <form id="addLedgerForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Entity Type *</label>
                        <select name="entity_type" id="entityType" onchange="onEntityTypeChange()" required>
                            <option value="">Select type</option>
                            <option value="Customer">Customer</option>
                            <option value="Supplier">Supplier</option>
                            <option value="Investor">Investor</option>
                            <option value="Job Work">Job Work</option>
                        </select>
                    </div>
                    <div class="form-group" id="entityGroup">
                        <label>Party *</label>
                        <select name="entity_id" id="entitySelect" required>
                            <option value="">Select entity type first</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="Debit">Debit (Expense / Payment Out)</option>
                            <option value="Credit">Credit (Receipt / Payment In)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label>Amount (₹) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>HSN Code</label>
                        <input type="text" name="hsn_code">
                    </div>
                    <div class="form-group">
                        <label>CSM Code</label>
                        <input type="text" name="csm_code">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description / Narration</label>
                    <textarea name="description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addLedgerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const customers = @json($customers);
const suppliers  = @json($suppliers);
const investors  = @json($investors);
const jobWorks   = @json($jobWorks);

function onEntityTypeChange() {
    const type = document.getElementById('entityType').value;
    const sel  = document.getElementById('entitySelect');
    sel.innerHTML = '<option value="">Select...</option>';

    if (type === 'Customer') {
        customers.forEach(c => sel.innerHTML += `<option value="${c.id}">${c.name}</option>`);
    } else if (type === 'Supplier') {
        suppliers.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.name}</option>`);
    } else if (type === 'Investor') {
        investors.forEach(i => sel.innerHTML += `<option value="${i.id}">${i.name}</option>`);
    } else if (type === 'Job Work') {
        jobWorks.forEach(j => sel.innerHTML += `<option value="${j.id}">${j.party_name}</option>`);
    }
}

document.getElementById('addLedgerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('ledger.store') }}', 'POST');
});
</script>
@endsection
