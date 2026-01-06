<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Spk;
use App\Models\Timeline;


class TrackingController extends Controller
{
     ///trecking//
   public function tracking($uuid)
{
    try {
        $pengajuan = Pengajuan::where('uuid', $uuid)
            ->firstOrFail();

        $spk = Spk::where('uuid_pengajuan', $uuid)
            ->first();

        $timeline = Timeline::where('uuid_pengajuan', $uuid)
            ->get();

        return response()->json([
            'success' => true,
            'tracking_id' => $uuid,
            'no_referensi' => $pengajuan->no_referensi,
            'no_surat' => $pengajuan->no_surat,

            'pengajuan' => $pengajuan,
            'spk' => $spk,
            'timeline' => $timeline
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function getByNoSurat($no_surat)
{
    try {
        
        $pengajuan = Pengajuan::where('no_surat', $no_surat)
            ->first();

        if (!$pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $spk = Spk::where('uuid_pengajuan', $pengajuan->uuid)
            ->first();

        $timelineDb = Timeline::where('uuid_pengajuan', $pengajuan->uuid)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'source'     => $item->source,
                    'status'     => $item->status,
                    'title'      => $item->title,
                    'message'    => $item->message,
                    'created_at' => $item->created_at,
                ];
            })->toArray();

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
                'message'    => "Nomor SPK: $spk->no_surat",
                'created_at' => $spk->created_at,
            ];
        }

        $mergedTimeline = collect(array_merge($timelineDefault, $timelineDb))
            ->sortBy('created_at')
            ->values()
            ->all();

        return response()->json([
            'success'      => true,
            'tracking_id'  => $pengajuan->uuid,
            'no_referensi' => $pengajuan->no_referensi,
            'no_surat'     => $pengajuan->no_surat,
            'pengajuan'    => $pengajuan,
            'spk'          => $spk,
            'timeline'     => $mergedTimeline
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan server',
            'error' => $e->getMessage()
        ], 500);
    }
}


}