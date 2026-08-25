<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenanceLog extends Model
{
    protected $table = 'asset_maintenance_logs';

    protected $fillable = [
        'asset_id',
        'service_date',
        'service_type',
        'cost',
        'technician_name',
        'vendor_name',
        'parts_replaced',
        'problem_reported',
        'action_taken',
        'status_after_service',
        'next_service_due',
    ];

    protected $casts = [
        'service_date'     => 'date',
        'next_service_due' => 'date',
        'cost'             => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FactoryAsset::class, 'asset_id');
    }
}
