<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number',
        'adjustment_date',
        'product_id',
        'warehouse_id',
        'system_stock',
        'physical_stock',
        'difference_quantity',
        'adjustment_type',
        'reason',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date'     => 'date',
        'system_stock'        => 'decimal:4',
        'physical_stock'      => 'decimal:4',
        'difference_quantity' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ-' . date('Ym') . '-';
        $last = self::where('adjustment_number', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
        $num = $last ? (int) substr($last->adjustment_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
