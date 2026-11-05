<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            Log::warning('Login gagal', [
                'event' => 'auth.login_failed',
                'user_id' => $user?->id,
                'action' => 'login',
                'reason' => $user ? 'password_salah' : 'akun_tidak_ditemukan',
                'email_hash' => substr(hash('sha256', strtolower($data['email'])), 0, 12),
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        Log::info('Login berhasil', [
            'event' => 'auth.login_succeeded',
            'user_id' => $user->id,
            'action' => 'login',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $user->createToken('api')->plainTextToken,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        Log::info('Logout berhasil, token dicabut', [
            'event' => 'auth.logout',
            'user_id' => $request->user()->id,
            'action' => 'logout',
        ]);

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
