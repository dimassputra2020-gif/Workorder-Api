<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckExternalToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?: $request->query('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token tidak ditemukan (Header atau Parameter)'
            ], 401);
        }

        $baseUrl = 'https://gateway.pdamkotasmg.co.id/api-gw-dev/portal-pegawai';

        $response = Http::withToken($token)->get($baseUrl . '/api/auth/me');

        $userData = $response->json('data.user');

        if ($response->failed() || !$userData) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa',
            ], 401);
        }

        $request->attributes->set('external_user', $userData);

        return $next($request);
    }
}