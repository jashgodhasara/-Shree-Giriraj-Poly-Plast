<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'unit_type',
        'weight_per_piece',
        'weight_unit',
        'weight_in_grams',
        'job_work_applicable',
        'wastage_percentage',
        'fixed_wastage',
        'is_active',
        'description',
        'image',
        'price',
        'hsn_code',
        'gst_rate',
        'stock_quantity'
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'gst_rate'            => 'decimal:2',
        'stock_quantity'      => 'decimal:2',
        'weight_per_piece'    => 'decimal:4',
        'weight_in_grams'     => 'decimal:4',
        'wastage_percentage'  => 'decimal:2',
        'fixed_wastage'       => 'decimal:4',
        'job_work_applicable' => 'boolean',
        'is_active'           => 'boolean',
    ];

    protected $appends = ['image_url', 'calculated_weight_grams'];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset($this->image);
        }
        return null;
    }

    public function getCalculatedWeightGramsAttribute(): float
    {
        if ($this->weight_in_grams && $this->weight_in_grams > 0) {
            return (float) $this->weight_in_grams;
        }

        $weight = (float) ($this->weight_per_piece ?? 0);
        $unit   = strtoupper(trim($this->weight_unit ?? 'Gram'));

        return match ($unit) {
            'KG', 'KILOGRAM' => $weight * 1000,
            'TON', 'METRIC TON' => $weight * 1000000,
            'MILLIGRAM', 'MG' => $weight / 1000,
            default => $weight, // Grams
        };
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function jobWorkOrderItems(): HasMany
    {
        return $this->hasMany(JobWorkOrderItem::class);
    }
}
