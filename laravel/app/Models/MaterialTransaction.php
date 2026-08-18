<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialTransaction extends Model
{
    protected $fillable = [
        'material_id', 'type', 'quantity', 'unit_type',
        'quantity_kg', 'quantity_pcs',
        'rate', 'total_amount',
        'supplier_id', 'transaction_date',
        'reference_no', 'vehicle_no', 'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'decimal:3',
        'quantity_kg'      => 'decimal:3',
        'quantity_pcs'     => 'decimal:2',
        'rate'             => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
