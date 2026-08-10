<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLog extends Model
{
    protected $fillable = [
        'date', 'raw_material_id', 'raw_material_used_kg',
        'additive_id', 'additive_used_kg',
        'final_product_id', 'final_product_qty_pcs',
        'salvage_qty_kg', 'notes',
    ];

    protected $casts = [
        'date'                   => 'date',
        'raw_material_used_kg'   => 'decimal:2',
        'additive_used_kg'       => 'decimal:2',
        'final_product_qty_pcs'  => 'integer',
        'salvage_qty_kg'         => 'decimal:2',
    ];

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
