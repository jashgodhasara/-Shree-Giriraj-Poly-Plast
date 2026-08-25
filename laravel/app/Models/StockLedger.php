<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'transaction_date',
        'transaction_type',
        'reference_module',
        'reference_id',
        'reference_number',
        'quantity_in',
        'quantity_out',
        'unit',
        'converted_quantity',
        'rate',
        'amount',
        'previous_stock',
        'stock_change',
        'new_stock',
        'average_cost_after',
        'user_id',
        'remarks',
    ];

    protected $casts = [
        'transaction_date'   => 'date',
        'quantity_in'        => 'decimal:4',
        'quantity_out'       => 'decimal:4',
        'converted_quantity' => 'decimal:4',
        'rate'               => 'decimal:2',
        'amount'             => 'decimal:2',
        'previous_stock'     => 'decimal:4',
        'stock_change'       => 'decimal:4',
        'new_stock'          => 'decimal:4',
        'average_cost_after' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
