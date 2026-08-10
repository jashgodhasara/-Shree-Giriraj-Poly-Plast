<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'transporter_id', 'lr_number',
        'invoice_date', 'subtotal', 'cgst', 'sgst', 'igst', 'grand_total',
        'paid_amount', 'payment_mode', 'status', 'notes',
        'payment_terms', 'po_number', 'po_date', 'delivery_at', 'eway_bill_no', 'challan_number',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal'     => 'decimal:2',
        'cgst'         => 'decimal:2',
        'sgst'         => 'decimal:2',
        'igst'         => 'decimal:2',
        'grand_total'  => 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPendingAmountAttribute(): float
    {
        return max(0, $this->grand_total - $this->payments()->sum('amount'));
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        if ($totalPaid <= 0) {
            $this->status = 'Unpaid';
        } elseif ($totalPaid >= $this->grand_total) {
            $this->status = 'Paid';
        } else {
            $this->status = 'Partial';
        }
        $this->paid_amount = $totalPaid;
        $this->saveQuietly();
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';
        
        $lastInvoice = self::where('invoice_number', 'LIKE', $prefix . '%')
                           ->orderByRaw('CAST(SUBSTRING(invoice_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                           ->first();

        if ($lastInvoice) {
            $lastNum = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        while (self::where('invoice_number', $candidate)->exists()) {
            $nextNum++;
            $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}
