<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'gstin', 'address', 'state'];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'entity_id')
            ->where('entity_type', 'Supplier');
    }
}
