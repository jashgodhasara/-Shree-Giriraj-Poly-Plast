<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address', 'investment_amount', 'notes',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
    ];
}
