<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAuditLog extends Model
{
    const UPDATED_AT = null; // Only created_at is tracked

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'reference_number',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
