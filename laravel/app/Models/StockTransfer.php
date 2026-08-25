<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'transfer_date',
        'from_warehouse_id',
        'to_warehouse_id',
        'product_id',
        'quantity',
        'unit',
        'converted_quantity',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transfer_date'      => 'date',
        'quantity'           => 'decimal:4',
        'converted_quantity' => 'decimal:4',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateTransferNumber(): string
    {
        $prefix = 'TRF-' . date('Ym') . '-';
        $last = self::where('transfer_number', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
        $num = $last ? (int) substr($last->transfer_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
