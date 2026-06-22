<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['success' => true]);
    }

    // Login
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => [
                        'email' => ['Incorrect email or password']
                    ]
                ]
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }

    // Get Authenticated User
    public function user(Request $request)
    {
        $map_object =  $request->user()->map_object;
        Log::alert("map_object1 " . json_encode($map_object, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $map_object = is_null($map_object) ?
            ['business_id' => 0, 'business_title' => '', 'reviews_url' => ''] :
            $map_object->only('business_id', 'business_title', 'reviews_url', 'rating', 'ratings_count', 'reviews_count');
        Log::alert("map_object2 " . json_encode($map_object, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'user' => $request->user()->only('id', 'name', 'email'),
            'map_object' => $map_object
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
