<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ManagerCredentialsMail;
use App\Mail\RestaurantApprovedMail;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::with('menuCategories.menuItems')->where('status', 'active');

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        $restaurants = $query->orderBy('name')->paginate(12);
        return response()->json($restaurants);
    }

    /** Admin: list all restaurants (any status) with optional search & status filter */
    public function adminIndex(Request $request)
    {
        if ($request->user()->role_id != 1) {
            abort(403, 'Admin only');
        }
        $query = Restaurant::with('user:id,name,email')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $restaurants = $query->paginate($request->get('per_page', 15));
        return response()->json($restaurants);
    }

    /**
     * Admin: create a restaurant and assign a manager. Creates a new Vendor user,
     * sends login credentials by email. Manager can then log in and manage menu & orders.
     */
    public function adminStore(Request $request)
    {
        if ($request->user()->role_id != 1) {
            abort(403, 'Admin only');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'estimated_delivery_minutes' => 'nullable|integer|min:0',
            'manager_name' => 'required|string|max:255',
            'manager_email' => 'required|email|unique:users,email',
        ]);

        $plainPassword = Str::random(12);
        $manager = User::create([
            'name' => $request->manager_name,
            'email' => $request->manager_email,
            'password' => Hash::make($plainPassword),
            'role_id' => 2, // Vendor
            'restaurant_id' => null,
        ]);

        $data = $request->only(['name', 'slug', 'address', 'phone', 'email', 'description', 'delivery_fee', 'estimated_delivery_minutes']);
        $data['user_id'] = $manager->id;
        $data['status'] = 'active';

        $restaurant = Restaurant::create($data);
        $manager->update(['restaurant_id' => $restaurant->id]);

        try {
            Mail::to($manager->email)->send(new ManagerCredentialsMail([
                'name' => $manager->name,
                'email' => $manager->email,
                'password' => $plainPassword,
                'restaurant_name' => $restaurant->name,
                'login_url' => rtrim(config('app.url'), '/') . '/login',
            ]));
        } catch (\Throwable $e) {
            \Log::warning('Manager credentials email failed: ' . $e->getMessage());
        }

        $restaurant->load('user:id,name,email');
        return response()->json($restaurant, 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'estimated_delivery_minutes' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $data = $request->only(['name', 'slug', 'address', 'phone', 'email', 'description', 'delivery_fee', 'estimated_delivery_minutes']);
        $data['user_id'] = $user->id;
        $data['status'] = 'pending';

        $restaurant = Restaurant::create($data);
        if ($user->role_id == 2) {
            $user->update(['restaurant_id' => $restaurant->id]);
        }
        return response()->json($restaurant, 201);
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['menuCategories.menuItems' => fn($q) => $q->where('is_available', true)]);
        return response()->json($restaurant);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $this->authorizeRestaurant($request->user(), $restaurant);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:restaurants,slug,' . $restaurant->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,pending',
            'delivery_fee' => 'nullable|numeric|min:0',
            'estimated_delivery_minutes' => 'nullable|integer|min:0',
        ]);

        $oldStatus = $restaurant->status;
        $restaurant->update($request->only(['name', 'slug', 'address', 'phone', 'email', 'description', 'status', 'delivery_fee', 'estimated_delivery_minutes']));

        if (
            $request->user()->role_id == 1
            && $request->has('status')
            && $request->status === 'active'
            && $oldStatus === 'pending'
            && $restaurant->user
        ) {
            try {
                Mail::to($restaurant->user->email)->send(new RestaurantApprovedMail([
                    'name' => $restaurant->user->name,
                    'restaurant_name' => $restaurant->name,
                    'login_url' => rtrim(config('app.url'), '/') . '/login',
                ]));
            } catch (\Throwable $e) {
                \Log::warning('Restaurant approval email failed: ' . $e->getMessage());
            }
        }

        return response()->json($restaurant);
    }

    public function destroy(Request $request, Restaurant $restaurant)
    {
        $this->authorizeRestaurant($request->user(), $restaurant);
        $restaurant->delete();
        return response()->json(['message' => 'Restaurant deleted'], 200);
    }

    private function authorizeRestaurant($user, Restaurant $restaurant)
    {
        if ($user->role_id == 1) return;
        if ($user->role_id == 2 && $restaurant->user_id == $user->id) return;
        abort(403, 'Unauthorized');
    }
}
