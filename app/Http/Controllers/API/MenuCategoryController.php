<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        $categories = $restaurant->menuCategories()->with('menuItems')->orderBy('sort_order')->get();
        return response()->json($categories);
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $user = $request->user();
        if ($user->role_id != 1 && ($user->role_id != 2 || $restaurant->user_id != $user->id)) {
            abort(403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data = $request->only(['name', 'description', 'sort_order']);
        $data['restaurant_id'] = $restaurant->id;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $category = MenuCategory::create($data);
        return response()->json($category, 201);
    }

    public function update(Request $request, Restaurant $restaurant, MenuCategory $menuCategory)
    {
        if ($menuCategory->restaurant_id != $restaurant->id) abort(404);
        $user = $request->user();
        if ($user->role_id != 1 && ($user->role_id != 2 || $restaurant->user_id != $user->id)) abort(403);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $menuCategory->update($request->only(['name', 'description', 'sort_order']));
        return response()->json($menuCategory);
    }

    public function destroy(Request $request, Restaurant $restaurant, MenuCategory $menuCategory)
    {
        if ($menuCategory->restaurant_id != $restaurant->id) abort(404);
        $user = $request->user();
        if ($user->role_id != 1 && ($user->role_id != 2 || $restaurant->user_id != $user->id)) abort(403);
        $menuCategory->delete();
        return response()->json(['message' => 'Category deleted'], 200);
    }
}
