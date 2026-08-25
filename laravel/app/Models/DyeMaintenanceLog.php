<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DyeMaintenanceLog extends Model
{
    protected $table = 'dye_maintenance_logs';

    protected $fillable = [
        'dye_id',
        'maintenance_date',
        'maintenance_type',
        'shots_at_service',
        'cost',
        'performed_by',
        'vendor_name',
        'work_description',
        'next_due_date',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_due_date'    => 'date',
        'shots_at_service' => 'integer',
        'cost'             => 'decimal:2',
    ];

    public function dye(): BelongsTo
    {
        return $this->belongsTo(DyeAndMould::class, 'dye_id');
    }
}
