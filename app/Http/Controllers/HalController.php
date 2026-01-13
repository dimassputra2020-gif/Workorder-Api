<?php

namespace App\Http\Controllers;

use App\Models\MasterHal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HalController extends Controller
{

    public function index(Request $request)
    {
        ini_set('memory_limit', '512M');
        try {

            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token tidak ditemukan'
                ], 401);
            }

            $data = MasterHal::where('status', 1)->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data master hal berhasil diambil',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}
