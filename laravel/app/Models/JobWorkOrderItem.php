<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorkOrderItem extends Model
{
    protected $fillable = [
        'job_work_order_id',
        'product_id',
        'received_weight',
        'received_weight_unit',
        'received_weight_grams',
        'product_weight',
        'product_weight_unit',
        'product_weight_grams',
        'gross_quantity',
        'wastage_type',
        'wastage_percentage',
        'wastage_quantity',
        'net_quantity',
        'delivered_quantity',
        'balance_quantity',
        'rate_type',
        'rate',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'received_weight'       => 'decimal:4',
        'received_weight_grams' => 'decimal:4',
        'product_weight'        => 'decimal:4',
        'product_weight_grams'  => 'decimal:4',
        'gross_quantity'        => 'decimal:4',
        'wastage_percentage'    => 'decimal:2',
        'wastage_quantity'      => 'decimal:4',
        'net_quantity'          => 'decimal:4',
        'delivered_quantity'    => 'decimal:4',
        'balance_quantity'      => 'decimal:4',
        'rate'                  => 'decimal:4',
        'amount'                => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(JobWorkOrder::class, 'job_work_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(JobWorkDeliveryItem::class, 'job_work_order_item_id');
    }
}
