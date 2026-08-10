<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'material_id'      => $this->material_id,
            'material_name'    => $this->material?->name,
            'unit'             => $this->material?->unit,
            'type'             => $this->type,
            'quantity'         => (float) $this->quantity,
            'rate'             => $this->rate !== null ? (float) $this->rate : null,
            'total_amount'     => $this->total_amount !== null ? (float) $this->total_amount : null,
            'supplier_id'      => $this->supplier_id,
            'supplier_name'    => $this->supplier?->name,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'reference_no'     => $this->reference_no,
            'vehicle_no'       => $this->vehicle_no,
            'remarks'          => $this->remarks,
            'created_at'       => $this->created_at,
        ];
    }
}
