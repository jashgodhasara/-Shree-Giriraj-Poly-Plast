<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DyeAndMould extends Model
{
    protected $table = 'dyes_and_moulds';

    protected $fillable = [
        'code',
        'name',
        'mould_type',
        'cavities',
        'ownership_type',
        'customer_id',
        'product_id',
        'compatible_machines',
        'rack_location',
        'status',
        'total_shots_count',
        'service_interval_shots',
        'last_serviced_date',
        'next_service_due_date',
        'purchase_cost',
        'fabrication_date',
        'image',
        'specifications',
        'notes',
    ];

    protected $casts = [
        'cavities'               => 'integer',
        'total_shots_count'      => 'integer',
        'service_interval_shots' => 'integer',
        'last_serviced_date'     => 'date',
        'next_service_due_date'  => 'date',
        'fabrication_date'       => 'date',
        'purchase_cost'          => 'decimal:2',
        'specifications'         => 'array',
    ];

    protected $appends = ['image_url', 'is_maintenance_due', 'shots_remaining_for_service'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getIsMaintenanceDueAttribute(): bool
    {
        if ($this->next_service_due_date && Carbon::parse($this->next_service_due_date)->isPast()) {
            return true;
        }
        if ($this->service_interval_shots > 0 && ($this->total_shots_count % $this->service_interval_shots) >= ($this->service_interval_shots * 0.95)) {
            return true;
        }
        return false;
    }

    public function getShotsRemainingForServiceAttribute(): int
    {
        if ($this->service_interval_shots <= 0) return 0;
        $shotsSinceLast = $this->total_shots_count % $this->service_interval_shots;
        return max(0, $this->service_interval_shots - $shotsSinceLast);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(DyeMaintenanceLog::class, 'dye_id')->latest('maintenance_date');
    }

    public static function generateCode(string $prefix = 'DIE'): string
    {
        $count = self::count() + 1;
        $code = sprintf('%s-%03d', strtoupper($prefix), $count);
        while (self::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%03d', strtoupper($prefix), $count);
        }
        return $code;
    }
}
