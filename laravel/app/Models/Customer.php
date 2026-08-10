<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'gstin', 'state'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'entity_id')
            ->where('entity_type', 'Customer');
    }
}
