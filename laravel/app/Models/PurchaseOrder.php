<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'expected_delivery_date',
        'payment_terms',
        'delivery_address',
        'subtotal',
        'cgst',
        'sgst',
        'igst',
        'grand_total',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'cgst'        => 'decimal:2',
        'sgst'        => 'decimal:2',
        'igst'        => 'decimal:2',
        'grand_total' => 'decimal:2',
        'po_date'     => 'date',
        'expected_delivery_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function generatePoNumber(): string
    {
        $prefix  = 'PO-' . date('Ym') . '-';
        $lastPo  = self::where('po_number', 'LIKE', $prefix . '%')
                       ->orderBy('id', 'desc')
                       ->first();

        if ($lastPo) {
            $num = (int) substr($lastPo->po_number, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
