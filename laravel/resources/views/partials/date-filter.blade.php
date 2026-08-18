{{-- 
    Reusable Date Filter Partial
    Usage: @include('partials.date-filter', ['action' => route('invoices.index')])
    Requires: $preset, $dateFrom, $dateTo from controller
--}}
<style>
.df-bar {
    background: #fff;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.df-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.df-presets-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted, #94a3b8);
    margin-right: 4px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 5px;
}
.df-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 13px;
    border-radius: 20px;
    border: 1.5px solid var(--border, #e2e8f0);
    background: #f8fafc;
    color: #475569;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    white-space: nowrap;
    line-height: 1;
}
.df-chip:hover {
    border-color: var(--primary, #6366f1);
    color: var(--primary, #6366f1);
    background: #eff6ff;
}
.df-chip.active {
    background: var(--primary, #6366f1) !important;
    color: #fff !important;
    border-color: var(--primary, #6366f1) !important;
    box-shadow: 0 2px 8px rgba(99,102,241,0.25);
}
.df-chip.clear-chip {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fecaca;
}
.df-chip.clear-chip:hover {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
}
.df-custom-row {
    display: none;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 12px;
    border-top: 1px solid var(--border, #e2e8f0);
    margin-top: 10px;
}
.df-custom-row.visible { display: flex; }
.df-custom-row label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    letter-spacing: 0.4px;
    white-space: nowrap;
}
.df-custom-row input[type="date"] {
    padding: 6px 12px;
    border: 1.5px solid var(--border, #e2e8f0);
    border-radius: 8px;
    font-size: 13px;
    font-family: inherit;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.15s;
    cursor: pointer;
}
.df-custom-row input[type="date"]:focus {
    border-color: var(--primary, #6366f1);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.df-date-range-info {
    font-size: 12px;
    color: var(--primary, #6366f1);
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 20px;
    padding: 5px 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
@media(max-width: 640px) {
    .df-presets { gap: 6px; }
    .df-chip { padding: 5px 9px; font-size: 12px; }
    .df-custom-row { gap: 8px; }
}
</style>

<div class="df-bar">
    {{-- Quick-select preset chips --}}
    <div class="df-presets">
        <span class="df-presets-label"><i class="fa fa-calendar-alt"></i> Period:</span>

        <a class="df-chip {{ $preset === 'today' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'today', 'date_from' => null, 'date_to' => null]) }}">
           Today
        </a>
        <a class="df-chip {{ $preset === 'yesterday' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'yesterday', 'date_from' => null, 'date_to' => null]) }}">
           Yesterday
        </a>
        <a class="df-chip {{ $preset === 'this_month' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'this_month', 'date_from' => null, 'date_to' => null]) }}">
           This Month
        </a>
        <a class="df-chip {{ $preset === 'last_month' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'last_month', 'date_from' => null, 'date_to' => null]) }}">
           Last Month
        </a>
        <a class="df-chip {{ $preset === 'last_3months' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'last_3months', 'date_from' => null, 'date_to' => null]) }}">
           Last 3 Months
        </a>
        <a class="df-chip {{ $preset === 'this_year' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'this_year', 'date_from' => null, 'date_to' => null]) }}">
           This Year
        </a>
        <a class="df-chip {{ $preset === 'last_year' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['preset' => 'last_year', 'date_from' => null, 'date_to' => null]) }}">
           Last Year
        </a>
        <button type="button"
                id="df-custom-toggle-btn"
                class="df-chip {{ ($preset === 'custom' || (!$preset && ($dateFrom || $dateTo))) ? 'active' : '' }}"
                onclick="toggleCustomRange()">
            <i class="fa fa-sliders-h"></i> Custom Range
        </button>

        @if($preset || $dateFrom || $dateTo)
            <a class="df-chip clear-chip" href="{{ request()->fullUrlWithQuery(['preset' => null, 'date_from' => null, 'date_to' => null]) }}" title="Reset Date Filter">
                <i class="fa fa-times"></i> Clear Filter
            </a>
            @if($dateFrom && $dateTo)
                <span class="df-date-range-info">
                    <i class="fa fa-calendar-check"></i>
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                    &nbsp;→&nbsp;
                    {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                </span>
            @endif
        @endif
    </div>

    {{-- Custom date range form --}}
    <form id="df-custom-form" action="{{ $action }}" method="GET">
        {{-- Preserve any other active query params like status, search, customer_id, etc. --}}
        @foreach(request()->query() as $key => $val)
            @if(!in_array($key, ['preset', 'date_from', 'date_to', 'page']) && is_string($val))
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach
        <input type="hidden" name="preset" value="custom">

        <div class="df-custom-row {{ ($preset === 'custom' || (!$preset && ($dateFrom || $dateTo))) ? 'visible' : '' }}" id="df-custom-row">
            <label><i class="fa fa-calendar"></i> From:</label>
            <input type="date" name="date_from" id="df_date_from" value="{{ $dateFrom }}" required>
            <label>To:</label>
            <input type="date" name="date_to" id="df_date_to" value="{{ $dateTo }}" required>
            <button type="submit" class="btn btn-primary btn-sm" style="padding:7px 18px; border-radius:8px;">
                <i class="fa fa-filter"></i> Apply Filter
            </button>
            <button type="button" class="btn btn-outline btn-sm" onclick="toggleCustomRange()" style="padding:7px 14px; border-radius:8px;">
                Cancel
            </button>
        </div>
    </form>
</div>

<script>
function toggleCustomRange() {
    const row = document.getElementById('df-custom-row');
    const btn = document.getElementById('df-custom-toggle-btn');
    const isVisible = row.classList.contains('visible');
    if (isVisible) {
        row.classList.remove('visible');
        btn.classList.remove('active');
    } else {
        row.classList.add('visible');
        btn.classList.add('active');
        document.getElementById('df_date_from').focus();
    }
}
</script>
