<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_order_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function foodOrder()
    {
        return $this->belongsTo(FoodOrder::class);
    }
}
