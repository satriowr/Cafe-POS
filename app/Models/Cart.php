<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'table_number',
        'menu_id',
        'quantity',
        'note',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
