<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'sku'                 => $this->sku,
            'product_code'        => $this->product_code,
            'category_id'         => $this->category_id,
            'category_name'       => $this->category->name ?? null,
            'product_type'        => $this->product_type,
            'brand'               => $this->brand,
            'unit'                => $this->unit ?: 'PCS',
            'purchase_unit'       => $this->purchase_unit,
            'sales_unit'          => $this->sales_unit,
            'conversion_factor'   => (float) $this->conversion_factor,
            'description'         => $this->description,
            'price'               => round((float) $this->price, 2),
            'sales_rate'          => round((float) ($this->sales_rate ?: $this->price), 2),
            'purchase_rate'       => round((float) $this->purchase_rate, 2),
            'average_cost'        => round((float) $this->average_cost, 4),
            'wholesale_rate'      => round((float) $this->wholesale_rate, 2),
            'mrp'                 => round((float) $this->mrp, 2),
            'hsn_code'            => $this->hsn_code,
            'gst_rate'            => round((float) $this->gst_rate, 2),
            'barcode'             => $this->barcode,
            'opening_stock'       => (float) $this->opening_stock,
            'stock_quantity'      => (float) $this->stock_quantity,
            'minimum_stock'       => (float) $this->minimum_stock,
            'maximum_stock'       => (float) $this->maximum_stock,
            'reorder_level'       => (float) $this->reorder_level,
            'stock_status'        => $this->stock_status,
            'inventory_value'     => $this->inventory_value,
            'image'               => $this->image,
            'image_url'           => $this->image_url,
            'is_active'           => (bool) $this->is_active,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
