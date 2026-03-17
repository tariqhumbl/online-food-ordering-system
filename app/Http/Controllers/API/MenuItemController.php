<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function store(Request $request, Restaurant $restaurant, MenuCategory $menuCategory)
    {
        $this->authorizeRestaurant($request->user(), $restaurant);
        if ($menuCategory->restaurant_id != $restaurant->id) abort(404);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable',
            'is_available' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048']);
        }

        $data = $request->only(['name', 'description', 'price', 'sort_order']);
        $data['menu_category_id'] = $menuCategory->id;
        $data['is_available'] = $request->boolean('is_available', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        } else {
            $data['image'] = $request->input('image');
        }

        $item = MenuItem::create($data);
        return response()->json($item, 201);
    }

    public function update(Request $request, Restaurant $restaurant, MenuItem $menuItem)
    {
        $this->authorizeRestaurant($request->user(), $restaurant);
        if ($menuItem->menuCategory->restaurant_id != $restaurant->id) abort(404);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable',
            'is_available' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048']);
        }

        $update = $request->only(['name', 'description', 'price', 'is_available', 'sort_order']);
        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $update['image'] = $request->file('image')->store('menu-items', 'public');
        } elseif ($request->has('image') && $request->input('image') === '') {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $update['image'] = null;
        }
        $menuItem->update($update);
        return response()->json($menuItem);
    }

    public function destroy(Request $request, Restaurant $restaurant, MenuItem $menuItem)
    {
        $this->authorizeRestaurant($request->user(), $restaurant);
        if ($menuItem->menuCategory->restaurant_id != $restaurant->id) abort(404);
        $menuItem->delete();
        return response()->json(['message' => 'Menu item deleted'], 200);
    }

    private function authorizeRestaurant($user, Restaurant $restaurant)
    {
        if ($user->role_id == 1) return;
        if ($user->role_id == 2 && $restaurant->user_id == $user->id) return;
        abort(403, 'Unauthorized');
    }
}
