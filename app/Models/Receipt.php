<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
        'invoice_number',
        'table_number',
        'total_price',
        'tax_amount',
        'service_charge',
        'grand_total',
        'cashier_name',
        'paid_at',
        'payment_type', 
        'cash_amount', 
        'change',       
    ];

    protected $dates = ['paid_at'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
