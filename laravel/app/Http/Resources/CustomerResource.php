<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'image'      => $this->image,
            'image_url'  => $this->image_url,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'address'    => $this->address,
            'gstin'      => $this->gstin,
            'state'      => $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
