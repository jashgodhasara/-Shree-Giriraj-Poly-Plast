<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'type', 'name', 'unit', 'grade_variation', 'temp', 'size', 'stock_quantity',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
    ];

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
