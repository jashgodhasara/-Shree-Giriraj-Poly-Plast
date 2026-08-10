<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $grandTotal = round((float) $this->grand_total, 2);
        $paidAmount = round((float) $this->paid_amount, 2);

        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_id'    => $this->customer_id,
            'customer_name'  => $this->customer?->name,
            'invoice_date'   => $this->invoice_date?->format('Y-m-d'),
            'subtotal'       => round((float) $this->subtotal, 2),
            'cgst'           => round((float) $this->cgst, 2),
            'sgst'           => round((float) $this->sgst, 2),
            'igst'           => round((float) $this->igst, 2),
            'grand_total'    => $grandTotal,
            'paid_amount'    => $paidAmount,
            'pending_amount' => round(max(0, $grandTotal - $paidAmount), 2),
            'status'         => $this->status,
            'payment_mode'   => $this->payment_mode,
            'lr_number'      => $this->lr_number,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at,
        ];
    }
}
