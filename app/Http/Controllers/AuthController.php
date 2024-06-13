<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User; // Zorg ervoor dat je het User-model importeert

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['error' => 'Unexpected user type'], 500);
        }

        $refreshToken = Str::random(60); // Genereer een random string als refresh token

        // Sla de refresh token op in de database
        $user->refresh_token = $refreshToken;
        $user->refresh_token_expiry = Carbon::now()->addDays(30); // Stel de vervaldatum in
        $user->save();

        return $this->respondWithToken($token, $refreshToken);
    }

    protected function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'refresh_token' => $refreshToken
        ]);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        $user = User::where('refresh_token', $refreshToken)
            ->where('refresh_token_expiry', '>', Carbon::now())
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }

        // Maak een nieuw access token
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $refreshToken);
    }
}
