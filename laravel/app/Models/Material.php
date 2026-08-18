<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'type', 'name', 'image', 'unit', 'secondary_unit',
        'grade_variation', 'temp', 'size',
        'stock_quantity',        // legacy total stock (kept for compatibility)
        'stock_kg',              // stock in Kg
        'stock_pcs',             // stock in Pcs
        'kg_per_pcs',            // conversion: Kg per 1 Pcs
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'stock_kg'       => 'decimal:3',
        'stock_pcs'      => 'decimal:2',
        'kg_per_pcs'     => 'decimal:4',
    ];

    protected $appends = ['image_url', 'has_dual_unit', 'stock_kg_equivalent'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    /** True when this material tracks both Kg AND Pcs */
    public function getHasDualUnitAttribute(): bool
    {
        return !empty($this->kg_per_pcs) && (float)$this->kg_per_pcs > 0;
    }

    /** Total stock expressed in Kg (pcs × kg_per_pcs + stock_kg) */
    public function getStockKgEquivalentAttribute(): float
    {
        $fromPcs = $this->has_dual_unit
            ? (float)$this->stock_pcs * (float)$this->kg_per_pcs
            : 0;
        return round((float)$this->stock_kg + $fromPcs, 3);
    }

    /** Convert Kg → Pcs using kg_per_pcs */
    public function kgToPcs(float $kg): float
    {
        if (!$this->has_dual_unit) return 0;
        return round($kg / (float)$this->kg_per_pcs, 2);
    }

    /** Convert Pcs → Kg using kg_per_pcs */
    public function pcsToKg(float $pcs): float
    {
        if (!$this->has_dual_unit) return 0;
        return round($pcs * (float)$this->kg_per_pcs, 3);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MaterialTransaction::class);
    }

    public function productionLogsAsRaw(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'raw_material_id');
    }

    public function productionLogsAsAdditive(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'additive_id');
    }

    public function productionLogsAsProduct(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'final_product_id');
    }
}
