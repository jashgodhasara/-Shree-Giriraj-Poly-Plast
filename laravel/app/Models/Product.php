<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'subcategory',
        'sku',
        'product_code',
        'product_type',
        'material_id',
        'brand',
        'unit',
        'unit_type',
        'purchase_unit',
        'sales_unit',
        'conversion_factor',
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
        'purchase_rate',
        'average_cost',
        'sales_rate',
        'wholesale_rate',
        'mrp',
        'hsn_code',
        'gst_rate',
        'barcode',
        'opening_stock',
        'minimum_stock',
        'maximum_stock',
        'reorder_level',
        'stock_quantity',
        'warehouse_id',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'purchase_rate'       => 'decimal:2',
        'average_cost'        => 'decimal:4',
        'sales_rate'          => 'decimal:2',
        'wholesale_rate'      => 'decimal:2',
        'mrp'                 => 'decimal:2',
        'gst_rate'            => 'decimal:2',
        'conversion_factor'   => 'decimal:4',
        'opening_stock'       => 'decimal:4',
        'minimum_stock'       => 'decimal:4',
        'maximum_stock'       => 'decimal:4',
        'reorder_level'       => 'decimal:4',
        'stock_quantity'      => 'decimal:4',
        'weight_per_piece'    => 'decimal:4',
        'weight_in_grams'     => 'decimal:4',
        'wastage_percentage'  => 'decimal:2',
        'fixed_wastage'       => 'decimal:4',
        'job_work_applicable' => 'boolean',
        'is_active'           => 'boolean',
    ];

    protected $appends = ['image_url', 'calculated_weight_grams', 'stock_status', 'inventory_value'];

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

    public function getStockStatusAttribute(): string
    {
        $stock = (float) $this->stock_quantity;
        $reorder = (float) $this->reorder_level;
        $min = (float) $this->minimum_stock;

        if ($stock <= 0) {
            return 'Out of Stock';
        }
        if ($min > 0 && $stock <= $min) {
            return 'Critical';
        }
        if ($reorder > 0 && $stock <= $reorder) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    public function getInventoryValueAttribute(): float
    {
        $cost = (float) $this->average_cost > 0 ? (float) $this->average_cost : (float) $this->purchase_rate;
        if ($cost <= 0) {
            $cost = (float) $this->price;
        }
        return round((float) $this->stock_quantity * $cost, 2);
    }

    // ── Relationships ────────────────────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class)->latest('transaction_date')->latest('id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function jobWorkOrderItems(): HasMany
    {
        return $this->hasMany(JobWorkOrderItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('stock_quantity', '>', 0)
            ->where(function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'reorder_level')
                  ->orWhereColumn('stock_quantity', '<=', 'minimum_stock');
            });
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('sku', 'LIKE', "%{$term}%")
              ->orWhere('product_code', 'LIKE', "%{$term}%")
              ->orWhere('barcode', 'LIKE', "%{$term}%")
              ->orWhere('hsn_code', 'LIKE', "%{$term}%")
              ->orWhere('brand', 'LIKE', "%{$term}%");
        });
    }

    public static function generateSku(?string $prefix = 'SGP'): string
    {
        $prefix = strtoupper(trim($prefix ?: 'SGP')) . '-';
        $last = self::where('sku', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
        $num = 1;
        if ($last) {
            $existing = substr($last->sku, strlen($prefix));
            $num = (int) $existing + 1;
        }
        return $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
