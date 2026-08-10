<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_id'     => $this->invoice_id,
            'invoice_number' => $this->invoice?->invoice_number,
            'amount'         => (float) $this->amount,
            'payment_date'   => $this->payment_date?->format('Y-m-d'),
            'payment_mode'   => $this->payment_mode,
            'reference_no'   => $this->reference_no,
            'remarks'        => $this->remarks,
            'created_at'     => $this->created_at,
        ];
    }
}
