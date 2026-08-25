<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    /**
     * Convert an amount from this unit to another unit.
     */
    public function convertTo(Unit $targetUnit, float $amount): float
    {
        if ($this->id === $targetUnit->id) {
            return $amount;
        }

        // Direct conversion from -> to
        $conversion = UnitConversion::where('from_unit_id', $this->id)
            ->where('to_unit_id', $targetUnit->id)
            ->first();

        if ($conversion) {
            return $conversion->operator === '/'
                ? ($conversion->conversion_factor > 0 ? $amount / (float) $conversion->conversion_factor : $amount)
                : $amount * (float) $conversion->conversion_factor;
        }

        // Reverse conversion to -> from
        $reverse = UnitConversion::where('from_unit_id', $targetUnit->id)
            ->where('to_unit_id', $this->id)
            ->first();

        if ($reverse && (float) $reverse->conversion_factor > 0) {
            return $reverse->operator === '/'
                ? $amount * (float) $reverse->conversion_factor
                : $amount / (float) $reverse->conversion_factor;
        }

        // Fallback default: 1:1 if no conversion defined
        return $amount;
    }
}
