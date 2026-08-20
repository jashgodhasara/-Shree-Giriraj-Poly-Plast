<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLog extends Model
{
    protected $fillable = [
        'date',
        'raw_material_id',   'raw_material_used_kg',
        'additive_id',       'additive_used_kg',
        'final_product_id',  'final_product_qty_pcs',
        'salvage_qty_kg',    'salvage_pct',
        'effective_yield_kg','notes',
    ];

    protected $casts = [
        'date'                  => 'date',
        'raw_material_used_kg'  => 'decimal:3',
        'additive_used_kg'      => 'decimal:3',
        'final_product_qty_pcs' => 'integer',
        'salvage_qty_kg'        => 'decimal:3',
        'salvage_pct'           => 'decimal:2',
        'effective_yield_kg'    => 'decimal:3',
    ];

    // ── Computed: salvage Kg from raw used × pct ──────────────────────────────
    public static function calcSalvageKg(float $rawKg, float $pct): float
    {
        return round($rawKg * ($pct / 100), 3);
    }

    // ── Computed: effective yield (net usable Kg) ─────────────────────────────
    public static function calcYieldKg(float $rawKg, float $salvageKg): float
    {
        return round($rawKg - $salvageKg, 3);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'raw_material_id');
    }

    public function additive(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'additive_id');
    }

    public function finalProduct(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'final_product_id');
    }
}
