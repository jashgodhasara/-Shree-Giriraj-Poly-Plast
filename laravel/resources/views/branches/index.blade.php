@extends('layouts.app')

@section('title', 'Multi-Branch & Inter-Branch Stock Transfers - Shree Giriraj Poly Plast')
@section('page-title', 'Multi-Location Management')

@section('content')
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;">Multi-Branch &amp; Stock Redistribution Engine</h2>
        <p class="text-muted" style="font-size: 13px;">Manage multiple shop/factory locations with shared product catalogs &amp; AI inter-branch transfers</p>
    </div>
    <button class="btn btn-primary" onclick="alert('Demo: Branch creation is active under your multi-location owner account.')">
        <i class="fa fa-plus"></i> Add New Branch / Depot
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fa fa-diagram-project"></i> Active Locations &amp; Depots</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Branch ID</th>
                    <th>Branch Name</th>
                    <th>Location / City</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $b)
                <tr>
                    <td>#{{ $b['id'] }}</td>
                    <td><strong>{{ $b['name'] }}</strong></td>
                    <td><i class="fa fa-location-dot text-primary"></i> {{ $b['city'] }}</td>
                    <td>{{ $b['type'] }}</td>
                    <td>
                        @if($b['is_main'])
                            <span class="badge badge-green">Main HQ / Primary</span>
                        @else
                            <span class="badge badge-blue">Branch Active</span>
                        @endif
                    </td>
                    <td>
                        <button onclick="switchBranch('{{ $b['name'] }}')" class="btn btn-outline btn-sm">
                            <i class="fa fa-right-to-bracket"></i> Switch Location
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-robot"></i> AI Inter-Branch Stock Redistribution Suggestions</h3>
        <span class="badge badge-purple"><i class="fa fa-wand-magic-sparkles"></i> AI Auto-Optimization</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Source Location</th>
                        <th>Destination Location</th>
                        <th>Material / SKU</th>
                        <th>Suggested Qty</th>
                        <th>AI Recommendation Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockTransfers as $st)
                    <tr>
                        <td><i class="fa fa-building text-muted"></i> {{ $st['from_branch'] }}</td>
                        <td><i class="fa fa-arrow-right text-primary"></i> <strong>{{ $st['to_branch'] }}</strong></td>
                        <td><strong>{{ $st['material'] }}</strong></td>
                        <td><span class="badge badge-orange">{{ $st['quantity'] }}</span></td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $st['reason'] }}</td>
                        <td>
                            <button onclick="showToast('Inter-branch stock transfer initiated!', 'success')" class="btn btn-success btn-sm">
                                <i class="fa fa-truck-arrow-right"></i> Approve &amp; Transfer
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function switchBranch(name) {
    try {
        const res = await fetch('{{ route("branches.switch") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ branch_name: name })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        }
    } catch(e) {
        showToast('Error switching branch', 'error');
    }
}
</script>
@endsection
