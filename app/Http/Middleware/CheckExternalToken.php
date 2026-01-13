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
        // $token = $request->header('Authorization');

        // if (!$token) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Authorization token tidak ditemukan'
        //     ], 401);
        // }

        // $baseUrl = 'https://gateway.pdamkotasmg.co.id/api-gw-dev/portal-pegawai';

        // $response = Http::withHeaders([
        //     'Authorization' => $token,
        // ])->get($baseUrl . '/api/auth/me');



        // // ✅ Ambil data user dari struktur 'data.user'
        // $userData = $response->json('data.user');

        // if ($response->failed() || !$userData) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Token tidak valid atau sudah kedaluwarsa',
        //     ], 401);
        // }

        // // ✅ Simpan data user eksternal di request
        // $request->attributes->set('external_user', $userData);

        $request->attributes->set('external_user', user_login_data());

        return $next($request);
    }
}
