<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobWork extends Model
{
    protected $fillable = [
        'party_name', 'phone', 'work_type', 'rate', 'unit', 'address', 'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];
}
