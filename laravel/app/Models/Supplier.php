<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'gstin', 'address', 'city',
        'state', 'country', 'pincode', 'tax_type'
    ];

    protected $appends = ['tax_regime'];

    public function getTaxRegimeAttribute(): array
    {
        return \App\Services\GstTaxCalculationService::determineTaxRegime(
            $this->country,
            $this->state,
            $this->gstin,
            $this->tax_type
        );
    }

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
