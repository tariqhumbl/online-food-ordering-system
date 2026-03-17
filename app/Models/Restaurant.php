<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'address', 'phone', 'email',
        'logo', 'cover_image', 'description', 'status',
        'delivery_fee', 'estimated_delivery_minutes',
    ];

    protected $casts = ['delivery_fee' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class)->orderBy('sort_order');
    }

    public function foodOrders()
    {
        return $this->hasMany(FoodOrder::class, 'restaurant_id');
    }
}
