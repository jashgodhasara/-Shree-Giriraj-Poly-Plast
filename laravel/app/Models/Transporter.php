<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    protected $fillable = ['name', 'vehicle_no', 'phone'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
