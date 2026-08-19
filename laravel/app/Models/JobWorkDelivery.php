<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorkDelivery extends Model
{
    protected $fillable = [
        'job_work_order_id',
        'delivery_number',
        'delivery_date',
        'challan_number',
        'vehicle_number',
        'transporter_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(JobWorkOrder::class, 'job_work_order_id');
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 'transporter_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobWorkDeliveryItem::class, 'job_work_delivery_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
