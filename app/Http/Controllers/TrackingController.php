<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Spk;
use App\Models\Timeline;
use Illuminate\Support\Facades\Http;
use App\Models\MasterHal;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
     ///trecking//
 


private function resolvePengajuanRelation(Request $request, Pengajuan $pengajuan): array
{
    $masterHal = MasterHal::find($pengajuan->hal_id);

    $token = $request->bearerToken();
    $url   = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';

    $response = Http::withToken($token)
        ->timeout(30)
        ->retry(3, 200)
        ->get($url);

    if (!$response->successful()) {
        throw new \Exception('Gagal mengambil data satker dari API eksternal');
    }

    $data = collect($response->json()['data'] ?? []);

    $satker = $data
        ->where('kd_satker', $pengajuan->kd_satker)
        ->map(fn ($item) => [
            'kd_satker'   => $item['kd_satker'] ?? null,
            'satker_name' => $item['satker_name'] ?? null,
        ])
        ->first();

    $parent = $data
        ->where('kd_parent', $pengajuan->satker)
        ->map(fn ($item) => [
            'kd_parent'     => $item['kd_parent'] ?? null,
            'parent_satker' => $item['parent_satker'] ?? null,
        ])
        ->first();

    return [
        'pengajuan' => $pengajuan,
        'masterhal' => $masterHal,
        'kd_satker' => $satker,
        'kd_parent' => $parent,
    ];
}


public function tracking(Request $request, $uuid)
{
    try {
        $pengajuan = Pengajuan::where('uuid', $uuid)->firstOrFail();

        $spk = Spk::where('uuid_pengajuan', $uuid)
            ->where('is_deleted', 0)
            ->first();

        if ($spk) {
            $spk->load('status', 'jenisPekerjaan');
        }

        $timeline = Timeline::where('uuid_pengajuan', $uuid)->get();

        $pengajuanRelation = $this->resolvePengajuanRelation($request, $pengajuan);

        return response()->json([
            'success'      => true,
            'tracking_id'  => $uuid,
            'no_referensi' => $pengajuan->no_referensi,
            'no_surat'     => $pengajuan->no_surat,


            'pengajuan'    => $pengajuanRelation['pengajuan'],
            'masterhal'    => $pengajuanRelation['masterhal'],
            'kd_satker'    => $pengajuanRelation['kd_satker'],
            'kd_parent'    => $pengajuanRelation['kd_parent'],

            'spk'          => $spk,

            'timeline'     => $timeline
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
            'error'   => $e->getMessage()
        ], 500);
    }
}


public function getByNoSurat(Request $request, $no_surat)
{
    try {
        $pengajuan = Pengajuan::where('no_surat', $no_surat)->first();

        if (!$pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $spk = Spk::where('uuid_pengajuan', $pengajuan->uuid)
            ->where('is_deleted', 0)
            ->first();

        if ($spk) {
            $spk->load('status', 'jenisPekerjaan');
        }

        $timelineDb = Timeline::where('uuid_pengajuan', $pengajuan->uuid)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($item) => [
                'source'     => $item->source,
                'status'     => $item->status,
                'title'      => $item->title,
                'message'    => $item->message,
                'created_at' => $item->created_at,
            ])
            ->toArray();

        $timelineDefault = [
            [
                'source'     => 'pengajuan',
                'status'     => $pengajuan->status,
                'title'      => 'Pengajuan Dibuat',
                'message'    => $pengajuan->keterangan ?? '',
                'created_at' => $pengajuan->created_at,
            ]
        ];

        if ($spk) {
            $timelineDefault[] = [
                'source'     => 'spk',
                'status'     => $spk->status,
                'title'      => 'SPK Dibuat',
                'message'    => "Nomor SPK: {$spk->no_surat}",
                'created_at' => $spk->created_at,
            ];
        }

        $mergedTimeline = collect(array_merge($timelineDefault, $timelineDb))
            ->sortBy('created_at')
            ->values()
            ->all();

        $pengajuanRelation = $this->resolvePengajuanRelation($request, $pengajuan);

        return response()->json([
            'success'      => true,
            'tracking_id'  => $pengajuan->uuid,
            'no_referensi' => $pengajuan->no_referensi,
            'no_surat'     => $pengajuan->no_surat,

  
            'pengajuan'    => $pengajuanRelation['pengajuan'],
            'masterhal'    => $pengajuanRelation['masterhal'],
            'kd_satker'    => $pengajuanRelation['kd_satker'],
            'kd_parent'    => $pengajuanRelation['kd_parent'],


            'spk'          => $spk,

            'timeline'     => $mergedTimeline
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan server',
            'error'   => $e->getMessage()
        ], 500);
    }
}

}