@extends('layouts.app')
@section('title', 'Product Stock Ledger')
@section('page-title', 'Stock Ledger')

@section('content')
<style>
    .ledger-filter-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .ledger-summary-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .summary-box {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px 18px;
        box-shadow: var(--shadow-sm);
    }
    .summary-box small {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .summary-box .val {
        font-size: 20px;
        font-weight: 800;
        margin-top: 4px;
    }
</style>

<!-- Summary Strip -->
<div class="ledger-summary-strip">
    <div class="summary-box">
        <small>Total Inward (IN)</small>
        <div class="val text-success">+{{ number_format($totalIn, 2) }}</div>
    </div>
    <div class="summary-box">
        <small>Total Outward (OUT)</small>
        <div class="val text-danger">-{{ number_format($totalOut, 2) }}</div>
    </div>
    <div class="summary-box">
        <small>Net Stock Flow</small>
        <div class="val text-primary">{{ number_format($totalIn - $totalOut, 2) }}</div>
    </div>
    <div class="summary-box">
        <small>Transaction Amount</small>
        <div class="val">₹{{ number_format($totalAmount, 2) }}</div>
    </div>
</div>

<!-- Filter Box -->
<div class="ledger-filter-card">
    <form method="GET" action="{{ route('inventory.ledger') }}" class="d-flex flex-wrap gap-3 align-center">
        <div style="flex:1; min-width:200px;">
            <select name="product_id" onchange="this.form.submit()" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:6px;">
                <option value="">-- All Products --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                @endforeach
            </select>
        </div>

        <div style="min-width:160px;">
            <select name="transaction_type" onchange="this.form.submit()" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:6px;">
                <option value="">-- All Movements --</option>
                <option value="Opening Stock" {{ request('transaction_type') == 'Opening Stock' ? 'selected' : '' }}>Opening Stock</option>
                <option value="Purchase" {{ request('transaction_type') == 'Purchase' ? 'selected' : '' }}>Purchase</option>
                <option value="Sales" {{ request('transaction_type') == 'Sales' ? 'selected' : '' }}>Sales</option>
                <option value="Sales Return" {{ request('transaction_type') == 'Sales Return' ? 'selected' : '' }}>Sales Return</option>
                <option value="Stock Adjustment" {{ request('transaction_type') == 'Stock Adjustment' ? 'selected' : '' }}>Stock Adjustment</option>
                <option value="Stock Transfer In" {{ request('transaction_type') == 'Stock Transfer In' ? 'selected' : '' }}>Transfer In</option>
                <option value="Stock Transfer Out" {{ request('transaction_type') == 'Stock Transfer Out' ? 'selected' : '' }}>Transfer Out</option>
                <option value="Production" {{ request('transaction_type') == 'Production' ? 'selected' : '' }}>Production</option>
                <option value="Job Work Issue" {{ request('transaction_type') == 'Job Work Issue' ? 'selected' : '' }}>Job Work Issue</option>
                <option value="Job Work Receive" {{ request('transaction_type') == 'Job Work Receive' ? 'selected' : '' }}>Job Work Receive</option>
            </select>
        </div>

        <div>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding:7px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px;" title="From Date">
        </div>
        <div>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="padding:7px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px;" title="To Date">
        </div>

        <div>
            <input type="text" name="reference_number" value="{{ request('reference_number') }}" placeholder="Ref #" style="padding:7px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; max-width:130px;">
        </div>

        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
        @if(request()->hasAny(['product_id', 'transaction_type', 'date_from', 'date_to', 'reference_number']))
            <a href="{{ route('inventory.ledger') }}" class="btn btn-outline btn-sm"><i class="fa fa-rotate-left"></i> Reset</a>
        @endif

        <button type="button" class="btn btn-outline btn-sm" onclick="window.print()" style="margin-left:auto;"><i class="fa fa-print"></i> Print</button>
    </form>
</div>

<!-- Stock Ledger Table -->
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-book-open text-primary"></i> Immutable Stock Ledger Movements ({{ $ledgers->total() }})</h3>
        <span style="font-size:12px; color:var(--text-muted);">Page {{ $ledgers->currentPage() }} of {{ $ledgers->lastPage() }}</span>
    </div>
    <div class="card-body" style="padding:0">
        @if($ledgers->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-book"></i></div>
            <p>No stock ledger entries found for the selected criteria.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference #</th>
                        <th>Product Name</th>
                        <th>Movement Type</th>
                        <th style="text-align:right;">Quantity IN</th>
                        <th style="text-align:right;">Quantity OUT</th>
                        <th style="text-align:right;">Unit Rate</th>
                        <th style="text-align:right;">Amount</th>
                        <th style="text-align:right;">Running Balance</th>
                        <th>User</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($ledgers as $l)
                <tr>
                    <td>{{ $l->transaction_date ? $l->transaction_date->format('d-m-Y') : '—' }}</td>
                    <td><code>{{ $l->reference_number ?: 'Ref #' . $l->id }}</code></td>
                    <td>
                        @if($l->product)
                            <a href="{{ route('products.show', $l->product) }}" class="fw-bold text-primary" style="text-decoration:none;">
                                {{ $l->product->name }}
                            </a>
                            <div style="font-size:10.5px; color:var(--text-muted);">{{ $l->product->sku }}</div>
                        @else
                            <span class="text-muted">Deleted Product</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $l->quantity_in > 0 ? 'badge-success' : 'badge-danger' }}" style="font-size:10px;">
                            {{ $l->transaction_type }}
                        </span>
                    </td>
                    <td style="text-align:right;" class="fw-bold {{ $l->quantity_in > 0 ? 'text-success' : 'text-muted' }}">
                        {{ $l->quantity_in > 0 ? '+' . number_format($l->quantity_in, 2) : '—' }}
                    </td>
                    <td style="text-align:right;" class="fw-bold {{ $l->quantity_out > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $l->quantity_out > 0 ? '-' . number_format($l->quantity_out, 2) : '—' }}
                    </td>
                    <td style="text-align:right;">₹{{ number_format($l->rate, 2) }}</td>
                    <td style="text-align:right;" class="fw-bold">₹{{ number_format($l->amount, 2) }}</td>
                    <td style="text-align:right;" class="fw-bold" style="color:var(--primary);">
                        {{ number_format($l->new_stock, 2) }} {{ $l->unit }}
                    </td>
                    <td style="font-size:12px;">{{ $l->user->name ?? 'System' }}</td>
                    <td style="font-size:11.5px; color:var(--text-muted); max-width:180px;">{{ $l->remarks ?: '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 20px;">
            {{ $ledgers->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
