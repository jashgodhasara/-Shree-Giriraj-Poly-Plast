<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobWorkDeliveryItem extends Model
{
    protected $fillable = [
        'job_work_delivery_id',
        'job_work_order_item_id',
        'delivered_quantity',
        'remarks',
    ];

    protected $casts = [
        'delivered_quantity' => 'decimal:4',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(JobWorkDelivery::class, 'job_work_delivery_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(JobWorkOrderItem::class, 'job_work_order_item_id');
    }
}
