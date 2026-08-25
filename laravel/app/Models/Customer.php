<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'image', 'phone', 'email', 'address', 'city',
        'state', 'country', 'pincode', 'gstin', 'tax_type'
    ];

    protected $appends = ['image_url', 'tax_regime'];

    public function getTaxRegimeAttribute(): array
    {
        return \App\Services\GstTaxCalculationService::determineTaxRegime(
            $this->country,
            $this->state,
            $this->gstin,
            $this->tax_type
        );
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset($this->image);
        }
        return null;
    }

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
