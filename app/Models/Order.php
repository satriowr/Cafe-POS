<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'table_number',
        'queue_number',
        'status',
        'is_paid',
        'receipt_id',
        'payment_token',
        'payment_method',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}
