<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobWorkAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'job_work_order_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(JobWorkOrder::class, 'job_work_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
