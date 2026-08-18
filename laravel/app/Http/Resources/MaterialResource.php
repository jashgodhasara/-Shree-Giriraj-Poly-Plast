<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'name'            => $this->name,
            'image'           => $this->image,
            'image_url'       => $this->image_url,
            'unit'            => $this->unit,
            'grade_variation' => $this->grade_variation,
            'temp'            => $this->temp,
            'size'            => $this->size,
            'stock_quantity'  => round((float) $this->stock_quantity, 2),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
