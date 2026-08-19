<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorkOrder extends Model
{
    protected $fillable = [
        'job_work_number',
        'client_id',
        'order_date',
        'due_date',
        'reference_number',
        'status',
        'rounding_method',
        'total_received_weight_kg',
        'total_gross_pieces',
        'total_wastage_pieces',
        'total_net_pieces',
        'total_delivered_pieces',
        'total_balance_pieces',
        'subtotal',
        'additional_charges',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'order_date'               => 'date',
        'due_date'                 => 'date',
        'total_received_weight_kg' => 'decimal:4',
        'total_gross_pieces'       => 'decimal:2',
        'total_wastage_pieces'     => 'decimal:2',
        'total_net_pieces'         => 'decimal:2',
        'total_delivered_pieces'   => 'decimal:2',
        'total_balance_pieces'     => 'decimal:2',
        'subtotal'                 => 'decimal:2',
        'additional_charges'       => 'decimal:2',
        'discount'                 => 'decimal:2',
        'tax'                      => 'decimal:2',
        'grand_total'              => 'decimal:2',
        'paid_amount'              => 'decimal:2',
        'balance_amount'           => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(JobWorkClient::class, 'client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobWorkOrderItem::class, 'job_work_order_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(JobWorkDelivery::class, 'job_work_order_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(JobWorkAuditLog::class, 'job_work_order_id')->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate summary totals from related line items.
     */
    public function refreshTotals(): self
    {
        $this->load('items');

        $totalReceivedGrams = 0;
        $totalGrossPieces   = 0;
        $totalWastagePieces = 0;
        $totalNetPieces     = 0;
        $totalDelivered     = 0;
        $subtotal           = 0;

        foreach ($this->items as $item) {
            $totalReceivedGrams += (float) $item->received_weight_grams;
            $totalGrossPieces   += (float) $item->gross_quantity;
            $totalWastagePieces += (float) $item->wastage_quantity;
            $totalNetPieces     += (float) $item->net_quantity;
            $totalDelivered     += (float) $item->delivered_quantity;
            $subtotal           += (float) $item->amount;
        }

        $this->total_received_weight_kg = round($totalReceivedGrams / 1000, 4);
        $this->total_gross_pieces       = $totalGrossPieces;
        $this->total_wastage_pieces     = $totalWastagePieces;
        $this->total_net_pieces         = $totalNetPieces;
        $this->total_delivered_pieces   = $totalDelivered;
        $this->total_balance_pieces     = max(0, $totalNetPieces - $totalDelivered);

        $this->subtotal    = $subtotal;
        $grandTotal        = $subtotal + (float) $this->additional_charges - (float) $this->discount + (float) $this->tax;
        $this->grand_total = max(0, $grandTotal);
        $this->balance_amount = max(0, $this->grand_total - (float) $this->paid_amount);

        // Update overall status based on delivery progress
        if ($this->status !== 'Cancelled' && $this->status !== 'Draft') {
            if ($totalNetPieces > 0 && $totalDelivered >= $totalNetPieces) {
                $this->status = 'Delivered';
            } elseif ($totalDelivered > 0 && $totalDelivered < $totalNetPieces) {
                $this->status = 'Partially Completed';
            }
        }

        $this->save();

        return $this;
    }
}
