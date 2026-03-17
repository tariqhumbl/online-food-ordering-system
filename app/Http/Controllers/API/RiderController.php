<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RiderCredentialsMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RiderController extends Controller
{
    private const RIDER_ROLE_ID = 4;

    /**
     * List all delivery riders (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $riders = User::where('role_id', self::RIDER_ROLE_ID)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json($riders);
    }

    /**
     * Create a new delivery rider (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! Role::find(self::RIDER_ROLE_ID)) {
            return response()->json([
                'message' => 'Delivery Rider role is not set up. Please run: php artisan db:seed --class=RoleSeeder',
            ], 500);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => self::RIDER_ROLE_ID,
        ]);

        try {
            Mail::to($user->email)->send(new RiderCredentialsMail([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $request->password,
                'login_url' => url('/login'),
            ]));
        } catch (\Throwable $e) {
            report($e);
            // Rider is created; email failure is logged but we still return success
        }

        return response()->json([
            'message' => 'Rider created successfully.',
            'rider' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }
}
