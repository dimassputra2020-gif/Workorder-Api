<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\MasterHal;
use App\Models\MasterJenisPekerjaan;
use App\Models\MasterStatus;

class ReportController extends Controller
{


public function pengajuan(Request $request)
{
    $startDate = $request->query('start_date');
    $endDate   = $request->query('end_date');
    $status    = $request->query('status');
    $satker    = $request->query('satker');
    $hal_id    = $request->query('nama_hal');

    $baseQuery = DB::table('pengajuans')
        ->where('is_deleted', 0);

    if ($startDate && $endDate) {
        $baseQuery->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);
    }

    if ($status) {
        $baseQuery->where('status', $status);
    }

    if ($satker) {
        $baseQuery->where('satker', $satker);
    }

    if ($hal_id){
        $baseQuery->where('hal_id', $hal_id); 
    }

    $pengajuan = (clone $baseQuery)
        ->orderBy('created_at', 'desc')
        ->get();

    $token = $request->bearerToken(); 
    $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';
    
    $satkerCollection = collect();

    try {
        $response = Http::withToken($token)
            ->timeout(30)
            ->retry(3, 200)
            ->get($url);

        if ($response->successful()) {
            $satkers = $response->json()['data'] ?? [];
            $satkerCollection = collect($satkers);
        }
    } catch (\Exception $e) {
    }

   

    foreach ($pengajuan as $item) {
        $masterhal = MasterHal::find($item->hal_id);

        $satkerData = $satkerCollection
            ->where('kd_satker', $item->kd_satker)
            ->first();

        $formattedSatker = null;
        if ($satkerData) {
            $formattedSatker = [
                'kd_satker' => $satkerData['kd_satker'] ?? null,
                'satker_name' => $satkerData['satker_name'] ?? null,
                'kepala_satker' => $satkerData['kepala_satker'] ?? null,
                'npp_kepala_satker' => $satkerData['npp_kepala_satker'] ?? null,
            ];
        }

          $token = $request->bearerToken(); 
    $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';
    
    $parentCollection = collect();

    try {
        $response = Http::withToken($token)
            ->timeout(30)
            ->retry(3, 200)
            ->get($url);

        if ($response->successful()) {
            $parents = $response->json()['data'] ?? [];
            $parentCollection = collect($parents);
        }
    } catch (\Exception $e) {
    }

        $parentData = $parentCollection
            ->where('kd_parent', $item->satker)
            ->first();

        $formattedparent = null;
        if ($parentData) {
            $formattedparent = [
                'kd_parent' => $parentData['kd_parent'] ?? null,
                'parent_satker' => $parentData['parent_satker'] ?? null,
            ];
        }

        $item->rl_data = [
            'kd_satker' => $formattedSatker,
            'kd_parent' => $formattedparent,
            'masterhal' => $masterhal,
        ];
               
    }

    $summary = (clone $baseQuery)
        ->selectRaw("
            SUM(status = 'approved') as approved,
            SUM(status = 'rejected') as rejected,
            SUM(status = 'pending')  as pending
        ")
        ->first();

    return response()->json([
        'success' => true,
        'filter' => [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status'     => $status,
            'satker'     => $satker,
            'hal_id'     => $hal_id,
        ],
        'summary' => [
            'approved' => (int) $summary->approved,
            'rejected' => (int) $summary->rejected,
            'pending'  => (int) $summary->pending,
            'total'    => (int) (
                $summary->approved +
                $summary->rejected +
                $summary->pending
            ),
        ],
        'data' => $pengajuan
    ]);
}



public function spk(Request $request)
{
    $startDate = $request->query('start_date');
    $endDate   = $request->query('end_date');
    $status_id = $request->query('status_id');
    $kd_satker = $request->query('kd_satker');
    $jenis_pekerjaan_id = $request->query('jenis_pekerjaan_id');

    $baseQuery = DB::table('spks')
        ->where('is_deleted', 0);

    if ($startDate && $endDate) {
        $baseQuery->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);
    }

    if ($status_id){
        $baseQuery->where('status_id', $status_id); 
    }
   

      if ($kd_satker){
        $baseQuery->where('kd_satker', $kd_satker); 
    }


    if ($jenis_pekerjaan_id){
        $baseQuery->where('jenis_pekerjaan_id', $jenis_pekerjaan_id); 
    }

    $spk = (clone $baseQuery)
        ->orderBy('created_at', 'desc')
        ->get();

    $token = $request->bearerToken(); 
    $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';
    
    $satkerCollection = collect();

    try {
        $response = Http::withToken($token)
            ->timeout(30)
            ->retry(3, 200)
            ->get($url);

        if ($response->successful()) {
            $satkers = $response->json()['data'] ?? [];
            $satkerCollection = collect($satkers);
        }
    } catch (\Exception $e) {
    }

    foreach ($spk as $item) {
        $jenisPekerjaan = MasterJenisPekerjaan::find($item->jenis_pekerjaan_id);

        $statusData = MasterStatus::find($item->status_id);

     
        $nppkepalasatkerKey = $item->npp_kepala_satker ?? null; 
        
        $satkerData = null;
        if ($nppkepalasatkerKey) {
            $satkerData = $satkerCollection
                ->where('npp_kepala_satker', $nppkepalasatkerKey)
                ->first();
        }

        $formattedSatker = null;
        if ($satkerData) {
            $formattedSatker = [
                'kd_satker' => $satkerData['kd_satker'] ?? null,
                'satker_name' => $satkerData['satker_name'] ?? null,
                'kepala_satker' => $satkerData['kepala_satker'] ?? null,
                'npp_kepala_satker' => $satkerData['npp_kepala_satker'] ?? null,
            ];
        }

        $item->rl_master = [
            'jenispekerjaan' => $jenisPekerjaan,
            'status' => $statusData,
            'kd_satker' => $formattedSatker
        ];

        if (isset($item->jenis_pekerjaan_id)) unset($item->jenis_pekerjaan_id);
        if (isset($item->status_id)) unset($item->status_id);
        if (isset($item->kd_satker)) unset($item->kd_satker);
    }

    $summary = (clone $baseQuery)
        ->selectRaw("
            SUM(status_id = 1) as selesai,
            SUM(status_id = 2) as belum_selesai,
            SUM(status_id = 3) as tidak_selesai,
            SUM(status_id = 4) as menunggu,
            SUM(status_id = 5) as proses
        ")
        ->first();

    return response()->json([
        'success' => true,
        'filter' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status_id' => $status_id,
            'kd_satker' => $kd_satker,
            'id_pekerjaan' => $jenis_pekerjaan_id,
        ],
        'summary' => [
            'selesai' => (int) $summary->selesai,
            'belum_selesai' => (int) $summary->belum_selesai,
            'tidak_selesai' => (int) $summary->tidak_selesai,
            'menunggu' => (int) $summary->menunggu,
            'proses' => (int) $summary->proses,
            'total' => (
                $summary->selesai +
                $summary->belum_selesai +
                $summary->tidak_selesai +
                $summary->menunggu +
                $summary->proses
            )
        ],
        'data' => $spk
    ]);
}

}