<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailResetPasswordRequest;

class AuthController extends Controller
{
    // user registration

    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|string|max:191|unique:users',
            'password' => 'required|confirmed',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role_id = $request->role_id ?? 3;


        if ($user->save()) {
            return response()->json([
                'message' => 'Registration successful, please try to login',
                'user' => $user
            ], 201);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again',
            ], 500);
        }
    }

    /**
     * Public restaurant partner signup. Creates a vendor account and pending restaurant
     * for admin review. Manager can log in only after admin approves the restaurant.
     */
    public function registerRestaurant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'restaurant_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'restaurant_email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $restaurant = DB::transaction(function () use ($request) {
            $manager = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 2,
            ]);

            $slug = $this->uniqueRestaurantSlug($request->restaurant_name);

            $restaurant = Restaurant::create([
                'user_id' => $manager->id,
                'name' => $request->restaurant_name,
                'slug' => $slug,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->restaurant_email,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            $manager->update(['restaurant_id' => $restaurant->id]);

            $admins = User::where('role_id', 1)->get();
            foreach ($admins as $admin) {
                Notification::createForUser(
                    $admin->id,
                    'restaurant_registration',
                    'New restaurant signup',
                    "{$restaurant->name} has registered and is awaiting approval.",
                    ['restaurant_id' => $restaurant->id, 'restaurant_name' => $restaurant->name]
                );
            }

            return $restaurant;
        });

        return response()->json([
            'message' => 'Your restaurant application has been submitted. You will receive an email once an admin approves your account.',
            'restaurant' => $restaurant->only(['id', 'name', 'slug', 'status']),
        ], 201);
    }

    private function uniqueRestaurantSlug(string $name): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'restaurant';
        }
        $original = $slug;
        $i = 1;
        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    // user login

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User Login",
     *     description="Authenticate a user and return an access token.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error_code", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Invalid username/password',
                'error_code' => 401
            ], 401);
        }

        $user = $request->user();

        if ($user->role_id == 2) {
            $restaurant = $user->restaurant_id
                ? Restaurant::find($user->restaurant_id)
                : Restaurant::where('user_id', $user->id)->first();

            if (!$restaurant || $restaurant->status !== 'active') {
                Auth::logout();
                $message = !$restaurant
                    ? 'No restaurant is linked to your account. Please contact support.'
                    : ($restaurant->status === 'pending'
                        ? 'Your restaurant application is pending admin approval. You will receive an email when approved.'
                        : 'Your restaurant account is not active. Please contact support.');

                return response()->json([
                    'message' => $message,
                    'error_code' => 403,
                ], 403);
            }
        }

        $user->tokens()->delete();

        if ($user->role_id == 1) {
            $token = $user->createToken('Personal Access Token', ['admin']);
        } elseif ($user->role_id == 2) {
            $token = $user->createToken('Personal Access Token', ['vendor']);
        } elseif ($user->role_id == 4) {
            $token = $user->createToken('Personal Access Token', ['rider']);
        } else {
            $token = $user->createToken('Personal Access Token', ['customer']);
        }


        return response()->json([
            'message' => 'Login successfully',
            'user' => $user,
            'token' => $token->plainTextToken
        ]);
    }

    // user profile

    /**
     * @OA\Get(
     *     path="/api/auth/user-profile",
     *     summary="Get User Profile",
     *     description="Get User Profile.",
     *     tags={"Authentication"},
     *     security={{ "sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Baber Azam"),
     *             @OA\Property(property="email", type="string", format="email", example="baber@azam.com"),
     *             @OA\Property(property="role_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token missing or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */

    public function userProfile(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $profile = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'restaurant_id' => $user->getAttribute('restaurant_id'),
            ];
            return response()->json($profile, 200);
        } catch (\Throwable $e) {
            \Log::error('userProfile error: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Error loading profile',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // update profile

    /**
     * @OA\Put(
     *     path="/api/auth/edit-Profile",
     *     summary="Update User Profile",
     *     description="Update the authenticated user's profile information.",
     *     tags={"Authentication"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=150, example="Mohammad Rizwan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Mohammad Rizwan"),
     *             @OA\Property(property="email", type="string", format="email", example="rizwan@gmail.com"),
     *             @OA\Property(property="role_id", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The name field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token missing or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some error occurred, please try again.")
     *         )
     *     )
     * )
     */

     public function updateProfile(Request $request)
     {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
        ]);
        $user->name = $request->name;
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($user->save()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'restaurant_id' => $user->getAttribute('restaurant_id'),
            ], 200);
        }

        return response()->json([
            'message' => 'Some error occurred, please try again',
        ], 500);
     }

     // reset password request

        /**
     * @OA\Post(
     *     path="/api/auth/reset-password-request",
     *     summary="Request Password Reset",
     *     description="Send a password reset verification code to the user's email.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="rizwan@gmail.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification code sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="We sent a verification code to the email address you provided!"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some error occurred, please try again.")
     *         )
     *     )
     * )
     */
     public function resetPasswordRequest(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $code = rand(111111, 999999);
        $user->verification_code = $code;
        if ($user->save()) {
            $data = array(
                'heading' => $user->heading,
                'name' => $user->name,
                'email' => $user->email,
                'code' => $user->verification_code,
            );

            Mail::to($user->email)->send(new MailResetPasswordRequest($data));

            return response()->json([
                'message' => 'We sent a verification code to the email address you provided!',
                'status_code' => 200
            ], 200);

        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    // reset password


    /**
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     summary="Reset Password",
     *     description="Reset a user's password using their email and a verification code.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "verification_code", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="rizwan@gmail.com"),
     *             @OA\Property(property="verification_code", type="integer", example=123456),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The password confirmation does not match."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or invalid verification code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found/Invalid code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some error occurred, please try again.")
     *         )
     *     )
     * )
     */

    public function resetPassword(Request $request)
    {
        // dd('s;dlfk');
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|integer',
            'password' => 'required|confirmed',
        ]);


        $user = User::where('email', $request->email)->where('verification_code', $request->verification_code)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found/Invalid code'
            ], 404);
        }
        $user->password = bcrypt($request->password);
        $user->verification_code = NULL;
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    // change password

     /**
     * @OA\Post(
     *     path="/api/auth/change-password",
     *     summary="Change Password",
     *     description="Change the authenticated user's password.",
     *     tags={"Authentication"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password changed successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Incorrect current password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your current password is incorrect")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token missing or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The password confirmation does not match."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some error occurred, please try again.")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = request()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Your current password is incorrect'], 400);
        }

        $user->password = bcrypt($request->password);

        if ($user->save()) {
            return response()->json([
                'message' => 'Password changed successfully!'
            ], 200);
        }

        return response()->json([
            'message' => 'Some error occurred, please try again'
        ], 401);
    }

    public function getUserRole(Request $request)
    {
        $user = $request->user();
        return response()->json(['role_id' => $user ? $user->role_id : null], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="User Logout",
     *     description="Logs out the authenticated user by revoking all tokens.",
     *     tags={"Authentication"},
     *     security={{ "sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token missing or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some error occurred, please try again.")
     *         )
     *     )
     * )
     */


    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
            'message' => 'Logout successfully'
        ], 200);
    }
}
