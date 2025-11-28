<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterJenisPekerjaan;
use Illuminate\Support\Facades\Http;

class MasterJenisPekerjaanController extends Controller
{
    public function index(Request $request)
    {
        try {
          
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token tidak ditemukan'
                ], 401);
            }
            $data = MasterJenisPekerjaan::where('status', 1)->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'ok',
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
