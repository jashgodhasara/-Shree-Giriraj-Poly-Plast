<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryAsset extends Model
{
    protected $table = 'factory_assets';

    protected $fillable = [
        'asset_code',
        'name',
        'category',
        'make_brand',
        'model_number',
        'serial_number',
        'tonnage_or_capacity',
        'power_rating_kw',
        'plant_location',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'supplier_id',
        'status',
        'assigned_operator',
        'last_service_date',
        'next_service_date',
        'service_interval_days',
        'image',
        'notes',
    ];

    protected $casts = [
        'purchase_date'         => 'date',
        'warranty_expiry'       => 'date',
        'last_service_date'     => 'date',
        'next_service_date'     => 'date',
        'purchase_cost'         => 'decimal:2',
        'power_rating_kw'       => 'decimal:2',
        'service_interval_days' => 'integer',
    ];

    protected $appends = ['image_url', 'is_maintenance_due', 'is_under_warranty'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getIsMaintenanceDueAttribute(): bool
    {
        if ($this->next_service_date && Carbon::parse($this->next_service_date)->isPast()) {
            return true;
        }
        return false;
    }

    public function getIsUnderWarrantyAttribute(): bool
    {
        if ($this->warranty_expiry && Carbon::parse($this->warranty_expiry)->isFuture()) {
            return true;
        }
        return false;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(AssetMaintenanceLog::class, 'asset_id')->latest('service_date');
    }

    public static function generateCode(string $prefix = 'AST'): string
    {
        $count = self::count() + 1;
        $code = sprintf('%s-%03d', strtoupper($prefix), $count);
        while (self::where('asset_code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%03d', strtoupper($prefix), $count);
        }
        return $code;
    }
}
