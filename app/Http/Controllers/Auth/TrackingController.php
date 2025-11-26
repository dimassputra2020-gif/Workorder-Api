<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Spk;


class TrackingController extends Controller
{
     ///trecking//
    public function tracking($uuid)
    {
        try {
            $pengajuan = Pengajuan::where('uuid', $uuid)
                ->where('is_deleted', 0)
                ->firstOrFail();

            $timelinePengajuan = [
                [
                    'source' => 'pengajuan',
                    'status' => $pengajuan->status,
                    'title' => 'Pengajuan Dibuat',
                    'message' => $pengajuan->keterangan ?? '',
                    'created_at' => $pengajuan->created_at,
                ],
                [
                    'source' => 'pengajuan',
                    'status' => $pengajuan->status,
                    'title' => 'Status Pengajuan Diperbarui',
                    'message' => $pengajuan->catatan_status ?? '',
                    'created_at' => $pengajuan->updated_at,
                ]
            ];
            $spk = Spk::where('uuid_pengajuan', $uuid)
                ->where('is_deleted', 0)
                ->first();

            $timelineSpk = [];

            if ($spk) {
                $timelineSpk = [
                    [
                        'source' => 'spk',
                        'status' => 'SPK Dibuat',
                        'title' => 'Surat Perintah Kerja Diterbitkan',
                        'message' => "Nomor SPK: {$spk->no_surat}",
                        'created_at' => $spk->created_at,
                    ],
                    [
                        'source' => 'spk',
                        'status' => $spk->status,
                        'title' => 'Status SPK Diperbarui',
                        'message' => $spk->keterangan ?? '',
                        'created_at' => $spk->updated_at,
                    ]
                ];
            }
            $mergedTimeline = collect(array_merge($timelinePengajuan, $timelineSpk))
                ->sortBy('created_at')
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'tracking_id' => $uuid,


                'no_referensi' => $pengajuan->no_referensi,
                'no_surat' => $pengajuan->no_surat,

                // seluruh data pengajuan (full)
                'pengajuan' => [
                    'hal_id' => $pengajuan->hal_id,
                    'kepada' => $pengajuan->kepada,
                    'satker' => $pengajuan->satker,
                    'kode_barang' => $pengajuan->kode_barang,
                    'keterangan' => $pengajuan->keterangan,
                    'file' => $pengajuan->file,
                    'status' => $pengajuan->status,
                    'uuid' => $pengajuan->uuid,
                    'is_deleted' => $pengajuan->is_deleted,
                    'name_pelapor' => $pengajuan->name_pelapor,
                    'npp_pelapor' => $pengajuan->npp_pelapor,
                    'tlp_pelapor' => $pengajuan->tlp_pelapor,
                    'mengetahui' => $pengajuan->mengetahui,
                    'no_surat' => $pengajuan->no_surat,
                    'mengetahui_name' => $pengajuan->mengetahui_name,
                    'mengetahui_npp' => $pengajuan->mengetahui_npp,
                    'mengetahui_tlp' => $pengajuan->mengetahui_tlp,
                    'ttd_pelapor' => $pengajuan->ttd_pelapor,
                    'ttd_mengetahui' => $pengajuan->ttd_mengetahui,
                    'no_referensi' => $pengajuan->no_referensi,
                ],

                // seluruh data SPK (full)
                'spk' => $spk ? [
                    'uuid_pengajuan' => $spk->uuid_pengajuan,
                    'tanggal' => $spk->tanggal,
                    'jenis_pekerjaan' => $spk->jenis_pekerjaan,
                    'kode_barang' => $spk->kode_barang,
                    'uraian_pekerjaan' => $spk->uraian_pekerjaan,
                    'stafs' => $spk->stafs,
                    'penanggung_jawab_npp' => $spk->penanggung_jawab_npp,
                    'penanggung_jawab_name' => $spk->penanggung_jawab_name,
                    'mengetahui_npp' => $spk->mengetahui_npp,
                    'mengetahui_name' => $spk->mengetahui_name,
                    'mengetahui' => $spk->mengetahui,
                    'file' => $spk->file,
                    'status' => $spk->status,
                    'ttd_mengetahui' => $spk->ttd_mengetahui,
                    'no_surat' => $spk->no_surat,
                ] : null,

                'timeline' => $mergedTimeline
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
            ->where('is_deleted', 0)
            ->first();

        if (!$pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }


        $spk = Spk::where('uuid_pengajuan', $pengajuan->uuid)
            ->where('is_deleted', 0)
            ->first();

        $timelinePengajuan = [
            [
                'source' => 'pengajuan',
                'status' => $pengajuan->status,
                'title' => 'Pengajuan Dibuat',
                'message' => $pengajuan->keterangan ?? '',
                'created_at' => $pengajuan->created_at,
            ],
            [
                'source' => 'pengajuan',
                'status' => $pengajuan->status,
                'title' => 'Status Pengajuan Diperbarui',
                'message' => $pengajuan->catatan_status ?? '',
                'created_at' => $pengajuan->updated_at,
            ]
        ];

        $timelineSpk = [];
        if ($spk) {
            $timelineSpk = [
                [
                    'source' => 'spk',
                    'status' => 'SPK Dibuat',
                    'title' => 'Surat Perintah Kerja Diterbitkan',
                    'message' => "Nomor SPK: {$spk->no_surat}",
                    'created_at' => $spk->created_at,
                ],
                [
                    'source' => 'spk',
                    'status' => $spk->status,
                    'title' => 'Status SPK Diperbarui',
                    'message' => $spk->keterangan ?? '',
                    'created_at' => $spk->updated_at,
                ]
            ];
        }

        $mergedTimeline = collect(array_merge($timelinePengajuan, $timelineSpk))
            ->sortBy('created_at')
            ->values()
            ->all();

        
        return response()->json([
            'success' => true,
            'tracking_id' => $pengajuan->uuid,
            'no_referensi' => $pengajuan->no_referensi,
            'no_surat' => $pengajuan->no_surat,
            'pengajuan' => $pengajuan, 
            'spk' => $spk,             
            'timeline' => $mergedTimeline
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