<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class SatkerController extends Controller
{
   
    public function getSatker(Request $request)
    {
        try {
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan. Kirim Authorization Bearer token.'
                ], 401);
            }

            $token = str_replace('Bearer ', '', $token);

            $satkers = Cache::remember('satkers_all', 300, function() use ($token) {
                $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';
                $response = Http::withToken($token)->timeout(5)->get($url);

                if (!$response->successful()) {
                    throw new \Exception('Gagal ambil data dari API eksternal: ' . $response->body());
                }

                return $response->json()['data'] ?? [];
            });

            return response()->json([
                'success' => true,
                'data' => $satkers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getJabSatker(Request $request)
    {
        try {
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token tidak ditemukan.'
                ], 401);
            }

            $token = str_replace('Bearer ', '', $token);

            $satkers = Cache::remember('satkers_jabsatker', 300, function() use ($token) {
                $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';
                $response = Http::withToken($token)->timeout(5)->get($url);

                if (!$response->successful()) {
                    throw new \Exception('Gagal ambil data dari API eksternal: ' . $response->body());
                }

                return $response->json()['data'] ?? [];
            });

            
            $result = collect($satkers)
                ->pluck('jabsatker')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function getSatkerName(Request $request)
    {
        
        try {
            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token tidak ditemukan.'
                ], 401);
            }

            $token = str_replace('Bearer ', '', $token);

            $satkers = Cache::remember('satkers_name', 300, function() use ($token) {
                $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';
                $response = Http::withToken($token)->timeout(5)->get($url);

                if (!$response->successful()) {
                    throw new \Exception('Gagal ambil data dari API eksternal: ' . $response->body());
                }

                return $response->json()['data'] ?? [];
            });

            
             $result = collect($satkers)
                ->pluck('satker_name')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}
