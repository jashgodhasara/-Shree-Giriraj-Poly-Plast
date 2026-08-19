<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorkClient extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'gstin',
        'opening_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(JobWorkOrder::class, 'client_id');
    }
}
