@extends('layouts.app')
@section('title', 'Job Work ' . $jobWorkOrder->job_work_number)
@section('page-title', 'Job Work Details & Lifecycle')

@section('content')
<div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
    <div>
        <div class="d-flex align-center gap-2">
            <h2 style="font-size: 20px; font-weight: 800; color: var(--text); margin: 0;">
                {{ $jobWorkOrder->job_work_number }}
            </h2>
            <span class="badge {{ $jobWorkOrder->status === 'Delivered' ? 'badge-green' : ($jobWorkOrder->status === 'In Production' ? 'badge-purple' : ($jobWorkOrder->status === 'Material Received' ? 'badge-blue' : ($jobWorkOrder->status === 'Partially Completed' ? 'badge-orange' : 'badge-gray'))) }}" style="font-size: 12px; padding: 4px 10px;">
                {{ $jobWorkOrder->status }}
            </span>
        </div>
        <div class="text-muted" style="font-size: 12.5px; margin-top: 2px;">
            Order Date: <strong>{{ $jobWorkOrder->order_date->format('d M Y') }}</strong>
            @if($jobWorkOrder->due_date) • Due Date: <strong>{{ $jobWorkOrder->due_date->format('d M Y') }}</strong> @endif
            @if($jobWorkOrder->reference_number) • Ref / Challan: <strong>{{ $jobWorkOrder->reference_number }}</strong> @endif
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('statusModal')">
            <i class="fa fa-arrows-rotate"></i> Change Status
        </button>
        <button type="button" class="btn btn-success btn-sm" onclick="openModal('deliveryModal')">
            <i class="fa fa-truck-fast"></i> Record Delivery / Dispatch
        </button>
        <a href="{{ route('jobworks.print', $jobWorkOrder) }}" class="btn btn-outline btn-sm" target="_blank">
            <i class="fa fa-print"></i> Print Document
        </a>
        <a href="{{ route('jobworks.edit', $jobWorkOrder) }}" class="btn btn-outline btn-sm">
            <i class="fa fa-pen"></i> Edit
        </a>
        <a href="{{ route('jobworks.duplicate', $jobWorkOrder) }}" class="btn btn-outline btn-sm" title="Duplicate this entry">
            <i class="fa fa-copy"></i>
        </a>
    </div>
</div>

{{-- Top Row: Client Info & Summary Bar --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
    {{-- Client Details Card --}}
    <div class="card" style="margin: 0;">
        <div class="card-header d-flex justify-between align-center">
            <h3><i class="fa fa-building-user text-primary"></i> Client / Party Information</h3>
        </div>
        <div class="card-body" style="padding: 14px 16px;">
            <div style="font-size: 15px; font-weight: 700; color: var(--text);">{{ $jobWorkOrder->client->name }}</div>
            @if($jobWorkOrder->client->company_name)
                <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 600;">{{ $jobWorkOrder->client->company_name }}</div>
            @endif
            <div style="margin-top: 8px; font-size: 12.5px; color: #475569;">
                @if($jobWorkOrder->client->phone)
                    <div><i class="fa fa-phone text-muted" style="width:16px;"></i> <a href="tel:{{ $jobWorkOrder->client->phone }}" style="color:inherit; text-decoration:none;">{{ $jobWorkOrder->client->phone }}</a></div>
                @endif
                @if($jobWorkOrder->client->gstin)
                    <div><i class="fa fa-receipt text-muted" style="width:16px;"></i> GSTIN: <code>{{ $jobWorkOrder->client->gstin }}</code></div>
                @endif
                @if($jobWorkOrder->client->address)
                    <div style="margin-top:4px;"><i class="fa fa-location-dot text-muted" style="width:16px;"></i> {{ $jobWorkOrder->client->address }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Material & Production Progress Card --}}
    <div class="card" style="margin: 0; background: #f8fafc; border: 1.5px solid #e2e8f0;">
        <div class="card-header" style="background: transparent;">
            <h3><i class="fa fa-chart-simple text-primary"></i> Production &amp; Delivery Progress</h3>
        </div>
        <div class="card-body" style="padding: 14px 16px;">
            @php
                $deliveryPct = $jobWorkOrder->total_net_pieces > 0 ? min(100, round(($jobWorkOrder->total_delivered_pieces / $jobWorkOrder->total_net_pieces) * 100)) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 12px; font-weight: 700; color: #475569;">Fulfillment Progress</span>
                <span style="font-size: 13px; font-weight: 800; color: #2563eb;">{{ $deliveryPct }}% Dispatched</span>
            </div>
            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 6px; overflow: hidden; margin-bottom: 12px;">
                <div style="width: {{ $deliveryPct }}%; height: 100%; background: linear-gradient(90deg, #3b82f6, #10b981); border-radius: 6px; transition: width .4s;"></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; text-align: center;">
                <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 10.5px; color: #64748b; font-weight: 600;">RECEIVED</div>
                    <div style="font-weight: 800; font-size: 13.5px; color: var(--primary);">{{ number_format($jobWorkOrder->total_received_weight_kg, 2) }} KG</div>
                </div>
                <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 10.5px; color: #64748b; font-weight: 600;">NET FINISHED</div>
                    <div style="font-weight: 800; font-size: 13.5px; color: #059669;">{{ number_format($jobWorkOrder->total_net_pieces) }} PCS</div>
                </div>
                <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 10.5px; color: #64748b; font-weight: 600;">REMAINING</div>
                    <div style="font-weight: 800; font-size: 13.5px; color: #ef4444;">{{ number_format($jobWorkOrder->total_balance_pieces) }} PCS</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Product Items Table --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-boxes-stacked"></i> Job Work Product Items &amp; Weight Calculation</h3>
        <span class="badge badge-gray" style="font-size: 11px;">Rounding: {{ ucfirst($jobWorkOrder->rounding_method) }}</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Piece Weight (Snapshot)</th>
                        <th>Received Material</th>
                        <th>Gross Pieces</th>
                        <th>Wastage</th>
                        <th>Net Finished Pieces</th>
                        <th>Delivered</th>
                        <th>Balance</th>
                        <th>Rate &amp; Pricing</th>
                        <th>Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobWorkOrder->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if($item->product->sku)
                                <div style="font-size: 11px; color: var(--text-muted);">SKU: {{ $item->product->sku }}</div>
                            @endif
                            @if($item->remarks)
                                <div style="font-size: 11px; color: #64748b; font-style: italic;"><i class="fa fa-note-sticky"></i> {{ $item->remarks }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-purple">{{ $item->product_weight }} {{ $item->product_weight_unit }}</span>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">= {{ number_format($item->product_weight_grams, 2) }} g</div>
                        </td>
                        <td>
                            <strong>{{ number_format($item->received_weight, 2) }} {{ $item->received_weight_unit }}</strong>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">= {{ number_format($item->received_weight_grams) }} g</div>
                        </td>
                        <td><strong>{{ number_format($item->gross_quantity) }}</strong></td>
                        <td>
                            <span class="badge badge-orange">{{ number_format($item->wastage_quantity) }} PCS</span>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">({{ $item->wastage_percentage }}%)</div>
                        </td>
                        <td><strong style="color: #059669; font-size: 14px;">{{ number_format($item->net_quantity) }} PCS</strong></td>
                        <td style="color: #2563eb; font-weight: 700;">{{ number_format($item->delivered_quantity) }}</td>
                        <td>
                            @if($item->balance_quantity > 0)
                                <span class="badge badge-red">{{ number_format($item->balance_quantity) }}</span>
                            @else
                                <span class="badge badge-green"><i class="fa fa-check"></i> Complete</span>
                            @endif
                        </td>
                        <td>
                            <div>₹{{ number_format($item->rate, 2) }}</div>
                            <div style="font-size: 10px; color: #64748b; text-transform: uppercase;">{{ str_replace('_', ' ', $item->rate_type) }}</div>
                        </td>
                        <td class="fw-bold" style="color: var(--primary);">₹{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Financial Breakdown Card --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px;">
    {{-- Remarks / Notes --}}
    <div class="card" style="margin: 0;">
        <div class="card-header">
            <h3><i class="fa fa-comment-dots text-primary"></i> Remarks &amp; Instructions</h3>
        </div>
        <div class="card-body" style="padding: 14px 16px;">
            <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.6;">
                {{ $jobWorkOrder->remarks ?: 'No special remarks recorded for this Job Work order.' }}
            </p>
            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 11.5px; color: #64748b;">
                Created by: <strong>{{ $jobWorkOrder->creator->name ?? 'System' }}</strong> on {{ $jobWorkOrder->created_at->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    {{-- Financial Ledger Card --}}
    <div class="card" style="margin: 0; background: #fafafa;">
        <div class="card-header">
            <h3><i class="fa fa-receipt text-primary"></i> Financial Summary</h3>
        </div>
        <div class="card-body" style="padding: 14px 16px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                <span class="text-muted">Job Work Subtotal:</span>
                <strong>₹{{ number_format($jobWorkOrder->subtotal, 2) }}</strong>
            </div>
            @if($jobWorkOrder->additional_charges > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                <span class="text-muted">Additional / Freight:</span>
                <span>+ ₹{{ number_format($jobWorkOrder->additional_charges, 2) }}</span>
            </div>
            @endif
            @if($jobWorkOrder->discount > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; color: #16a34a;">
                <span>Discount:</span>
                <span>- ₹{{ number_format($jobWorkOrder->discount, 2) }}</span>
            </div>
            @endif
            @if($jobWorkOrder->tax > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                <span class="text-muted">Tax / GST:</span>
                <span>+ ₹{{ number_format($jobWorkOrder->tax, 2) }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid #e2e8f0; font-size: 15px;">
                <strong style="color: var(--text);">Grand Total:</strong>
                <strong style="color: var(--primary); font-size: 17px;">₹{{ number_format($jobWorkOrder->grand_total, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 13px;">
                <span class="text-muted">Paid / Settled:</span>
                <span style="color: #10b981; font-weight: 700;">₹{{ number_format($jobWorkOrder->paid_amount, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 13.5px;">
                <span style="font-weight: 700; color: #ef4444;">Balance Due:</span>
                <span style="color: #ef4444; font-weight: 800; font-size: 15px;">₹{{ number_format($jobWorkOrder->balance_amount, 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Delivery Dispatches Table --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-between align-center">
        <h3><i class="fa fa-truck-ramp-box text-primary"></i> Delivery &amp; Dispatch History</h3>
        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('deliveryModal')">
            <i class="fa fa-plus"></i> Record New Delivery
        </button>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($jobWorkOrder->deliveries->isEmpty())
        <div class="empty-state" style="padding: 24px;">
            <i class="fa fa-truck text-muted" style="font-size: 28px; opacity: 0.4;"></i>
            <p style="margin: 6px 0;">No dispatch delivery batches recorded yet.</p>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('deliveryModal')">
                <i class="fa fa-truck-fast"></i> Dispatch Finished Pieces
            </button>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Delivery #</th>
                        <th>Date</th>
                        <th>Challan / Vehicle</th>
                        <th>Transporter</th>
                        <th>Dispatched Items</th>
                        <th>Dispatched By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobWorkOrder->deliveries as $del)
                    <tr>
                        <td><strong>{{ $del->delivery_number }}</strong></td>
                        <td>{{ $del->delivery_date->format('d M Y') }}</td>
                        <td>
                            <div>{{ $del->challan_number ?: '—' }}</div>
                            @if($del->vehicle_number)<div style="font-size:11px; color:#64748b;">Vehicle: {{ $del->vehicle_number }}</div>@endif
                        </td>
                        <td>{{ $del->transporter->name ?? '—' }}</td>
                        <td>
                            @foreach($del->items as $dItem)
                                <div><strong style="color: #2563eb;">{{ number_format($dItem->delivered_quantity) }} PCS</strong> - {{ $dItem->orderItem->product->name }}</div>
                            @endforeach
                        </td>
                        <td>{{ $del->creator->name ?? 'System' }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $del->notes ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Audit Trail & Activity History --}}
<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fa fa-timeline text-primary"></i> Order Audit Trail &amp; Activity Log</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Activity Details</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobWorkOrder->auditLogs as $log)
                    <tr>
                        <td style="color: var(--text-muted); font-size: 12px;">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td><strong>{{ $log->user->name ?? 'System' }}</strong></td>
                        <td><span class="badge badge-gray">{{ $log->action }}</span></td>
                        <td>
                            <div><strong>{{ $log->field_name }}</strong></div>
                            @if($log->old_value)<div style="font-size:11.5px; color:#64748b;">{{ $log->old_value }}</div>@endif
                            @if($log->new_value)<div style="font-size:11.5px; color:#059669;">{{ $log->new_value }}</div>@endif
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $log->notes ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 16px;">No activity logs recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Change Status --}}
<div class="modal-overlay" id="statusModal">
    <div class="modal" style="max-width: 440px;">
        <div class="modal-header">
            <h3><i class="fa fa-arrows-rotate"></i> Update Job Work Status</h3>
            <button class="modal-close" onclick="closeModal('statusModal')">✕</button>
        </div>
        <form id="updateStatusForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Status *</label>
                    <select name="status" id="modal_status" required>
                        @foreach(['Draft', 'Material Received', 'In Production', 'Partially Completed', 'Completed', 'Delivered', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ $jobWorkOrder->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes / Status Reason</label>
                    <textarea name="notes" id="modal_notes" rows="2" placeholder="e.g. Completed initial production batch..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Record Delivery Batch --}}
<div class="modal-overlay" id="deliveryModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3><i class="fa fa-truck-fast"></i> Record Delivery / Dispatch Batch</h3>
            <button class="modal-close" onclick="closeModal('deliveryModal')">✕</button>
        </div>
        <form id="recordDeliveryForm">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Delivery Date *</label>
                        <input type="date" name="delivery_date" id="del_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Delivery Challan No.</label>
                        <input type="text" name="challan_number" id="del_challan" placeholder="e.g. DC-2026-089">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Transporter / Courier</label>
                        <select name="transporter_id" id="del_transporter">
                            <option value="">-- Select Transporter --</option>
                            @foreach($transporters as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" name="vehicle_number" id="del_vehicle" placeholder="e.g. GJ-01-AB-1234">
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: var(--primary);">
                        <i class="fa fa-boxes-packing"></i> Delivery Item Quantities
                    </div>
                    @foreach($jobWorkOrder->items as $idx => $item)
                        @php
                            $available = max(0, (float) $item->net_quantity - (float) $item->delivered_quantity);
                        @endphp
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #e2e8f0;">
                            <div style="flex: 1;">
                                <strong>{{ $item->product->name }}</strong>
                                <div style="font-size: 11px; color: #64748b;">
                                    Net: {{ number_format($item->net_quantity) }} | Delivered: {{ number_format($item->delivered_quantity) }} | Available: <strong style="color: #ef4444;">{{ number_format($available) }} PCS</strong>
                                </div>
                            </div>
                            <div style="width: 140px;">
                                <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                <input type="number" name="items[{{ $idx }}][quantity]" step="1" min="0" max="{{ $available }}" value="{{ $available }}" class="jw-input" placeholder="Qty PCS" style="text-align: right; font-weight: 700;">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Dispatch Notes</label>
                    <textarea name="notes" id="del_notes" rows="2" placeholder="Driver details, gate pass remarks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('deliveryModal')">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Dispatch &amp; Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Update Status AJAX
document.getElementById('updateStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const status = document.getElementById('modal_status').value;
    const notes  = document.getElementById('modal_notes').value;

    try {
        const res = await fetch('{{ route("jobworks.status.update", $jobWorkOrder) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status, notes })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Error updating status', 'error');
        }
    } catch(err) {
        showToast('Network error while updating status', 'error');
    }
});

// Record Delivery AJAX
document.getElementById('recordDeliveryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const payload = {
        delivery_date: formData.get('delivery_date'),
        challan_number: formData.get('challan_number'),
        vehicle_number: formData.get('vehicle_number'),
        transporter_id: formData.get('transporter_id'),
        notes: formData.get('notes'),
        items: []
    };

    @foreach($jobWorkOrder->items as $idx => $item)
        payload.items.push({
            item_id: formData.get('items[{{ $idx }}][item_id]'),
            quantity: parseFloat(formData.get('items[{{ $idx }}][quantity]')) || 0
        });
    @endforeach

    try {
        const res = await fetch('{{ route("jobworks.delivery.record", $jobWorkOrder) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Error recording delivery', 'error');
        }
    } catch(err) {
        showToast('Failed to record delivery', 'error');
    }
});
</script>
@endsection
