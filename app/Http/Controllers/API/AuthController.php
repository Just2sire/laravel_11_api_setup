<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    use JsonResponseTrait;

    /**
     * Implements registration.
     */
    public function register(RegisterRequest $request)
    {
        //
        $data = $request->all();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token =  $user->createToken('auth_token')->plainTextToken;

        $cookie = cookie('auth_token', $token, 1440, null, null, false, true);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ])->cookie($cookie);
    }

    /**
     * Implements connexion.
     */
    public function login(LoginRequest $request)
    {
        //
        $data = $request->all();

        $user = User::where('email', $data['email'])->first();
        if (!$user || Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects',
                'data' => [],
            ], 401);
        }

        $token =  $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ])->withCo;
    }

    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();

        $cookie = Cookie::forget('auth_token');

        return response()->json([
            'message' => "Logged out successfully",
        ])->withCookie($cookie);
    }
}
