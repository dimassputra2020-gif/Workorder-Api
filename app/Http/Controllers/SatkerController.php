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
            $externalUser = $request->attributes->get('external_user');

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user eksternal tidak ditemukan. Pastikan token valid.'
                ], 401);
            }

            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization Bearer token tidak ditemukan.'
                ], 401);
            }

            $satkers = Cache::remember("satkers_filtered", 300, function () use ($token) {
                $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';

                $response = Http::withToken($token)->timeout(5)->get($url);

                if (!$response->successful()) {
                    throw new \Exception('Gagal mengambil data dari API eksternal: ' . $response->body());
                }

                $data = $response->json()['data'] ?? [];

                return collect($data)->map(function ($item) {
                    return [
                        'kd_satker'         => $item['kd_satker'] ?? '',
                        'satker_name'       => $item['satker_name'] ?? '',
                        'npp_kepala_satker' => $item['npp_kepala_satker'] ?? ''
                    ];
                })->values()->toArray();
            });

            return response()->json([
                'success' => true,
                'data'    => $satkers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

public function getTlpByNpp(Request $request, $npp)
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

            $url = rtrim(env('BASE_URL'), '/') . "/api/masterdata/users/search-npps/{$npp}";

            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->get($url);

            if ($response->status() === 401) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalid atau expired'
                ], 401);
            }

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'API eksternal error',
                    'status' => $response->status(),
                    'response' => $response->json() ?? $response->body()
                ], 500);
            }

            $data = $response->json()['data'][0] ?? null;

            if (!$data || empty($data['rl_pegawai_local']['tlp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor telepon tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'npp' => $npp,
                'name' => $data['name'] ?? '',
                'tlp' => $data['rl_pegawai_local']['tlp']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

}
