<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $fillable = [
        'entity_type', 'entity_id', 'transaction_date',
        'type', 'amount', 'hsn_code', 'csm_code', 'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
        'entity_id'        => 'integer',
    ];

    public function entityName(): string
    {
        return match ($this->entity_type) {
            'Customer' => Customer::find($this->entity_id)?->name ?? 'Unknown Customer',
            'Supplier' => Supplier::find($this->entity_id)?->name ?? 'Unknown Supplier',
            'Investor' => Investor::find($this->entity_id)?->name ?? 'Unknown Investor',
            'Job Work' => JobWork::find($this->entity_id)?->party_name ?? 'Unknown Job Work',
            default    => $this->entity_type . ' #' . $this->entity_id,
        };
    }
}
