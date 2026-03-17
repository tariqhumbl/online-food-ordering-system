<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'restaurant_id',
        'user_id',
        'delivery_rider_id',
        'status',
        'delivery_address',
        'customer_phone',
        'notes',
        'subtotal',
        'delivery_fee',
        'tax',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid()) . '-' . now()->format('Ymd');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryRider()
    {
        return $this->belongsTo(User::class, 'delivery_rider_id');
    }

    public function items()
    {
        return $this->hasMany(FoodOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
