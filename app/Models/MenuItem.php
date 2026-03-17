<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_category_id', 'name', 'description', 'price', 'image',
        'is_available', 'sort_order',
    ];

    protected $casts = ['price' => 'decimal:2', 'is_available' => 'boolean'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // Use request base URL when available so image URLs work when app is in a subdirectory (e.g. WAMP)
        $base = request()->root();
        if ($base) {
            return rtrim($base, '/').'/storage/'.$this->image;
        }

        return asset('storage/'.$this->image);
    }

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function foodOrderItems()
    {
        return $this->hasMany(FoodOrderItem::class);
    }
}
