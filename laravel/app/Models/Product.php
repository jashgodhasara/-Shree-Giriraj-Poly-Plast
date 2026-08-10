<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'image', 'price', 'hsn_code', 'gst_rate', 'stock_quantity'];

    protected $casts = [
        'price'          => 'decimal:2',
        'gst_rate'       => 'decimal:2',
        'stock_quantity' => 'decimal:2',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset($this->image);
        }
        return null;
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
