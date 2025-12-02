<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Spk;
use App\Models\Pengajuan;
use App\Services\FonnteMessageService;
use App\Services\FonnteService;
use Illuminate\Support\Str;

class SpkController extends Controller
{
   private function checkPermission($externalUser, $permission)
{
    $npp = $externalUser['npp'] ?? null;
    
    $permissions = \App\Helpers\PermissionStore::getPermissionsFor($npp); 

    if (empty($permissions)) {
         return response()->json([
            'success' => false,
            'message' => 'NPP tidak memiliki data permission atau token tidak valid.'
        ], 403);
    }
    
    if (!in_array($permission, $permissions)) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki permission: ' . $permission
        ], 403);
    }

    return true;
}

    //views\\
    public function index(Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.views');
            if ($cek !== true) return $cek;

            $data = Spk::where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada data SPK.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data SPK berhasil diambil.',
                'total' => $data->count(),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    //menugaskan\\
    public function menugaskan(Request $request)
    {
        try {

            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.create.menugaskan');
            if ($cek !== true) return $cek;

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid.'
                ], 401);
            }

            $request->validate([
                'pengajuan_uuid' => 'required|uuid',
                'stafs' => 'required|array|min:1',
                'stafs.*.npp' => 'required|string',
                'stafs.*.nama' => 'required|string',
                'stafs.*.tlp'  => 'required|string',
                'stafs.*.is_penanggung_jawab' => 'required|boolean',
            ]);


            $spk = Spk::where('uuid_pengajuan', $request->pengajuan_uuid)->first();
            if (!$spk) {
                return response()->json([
                    'success' => false,
                    'message' => 'SPK belum dibuat. Approve pengajuan terlebih dahulu.'
                ], 404);
            }


            $pengajuan = Pengajuan::where('uuid', $request->pengajuan_uuid)->first();
            if (!$pengajuan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan tidak ditemukan.'
                ], 404);
            }

            $tlpPelapor = $pengajuan->tlp_pelapor;
            $tlpMengetahui = $pengajuan->mengetahui_tlp ?? null;


            $stafFinal = [];
            foreach ($request->stafs as $staf) {
                $stafFinal[] = [
                    "npp" => $staf['npp'],
                    "nama" => $staf['nama'],
                    "tlp" => $staf['tlp'],
                    "is_penanggung_jawab" => $staf['is_penanggung_jawab']
                ];
            }


            $penanggung = collect($stafFinal)
                ->firstWhere("is_penanggung_jawab", true);

            if (!$penanggung) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harus ada satu penanggung jawab.',
                ], 400);
            }
            $spk->update([
                "stafs" => $stafFinal,
                "penanggung_jawab_name" => $penanggung['nama'],
                "penanggung_jawab_npp"  => $penanggung['npp'],
                "penanggung_jawab_tlp"  => $penanggung['tlp'],
                "status" => "assigned"
            ]);

            $penanggung_jawab = [
                'name' => $spk->penanggung_jawab_name ?? '',
                'npp'  => $spk->penanggung_jawab_npp ?? '',
                'tlp'  => $spk->penanggung_jawab_tlp ?? '',
            ];

            $listStaf = collect($stafFinal)
                ->map(fn($s) => "- {$s['nama']} ({$s['npp']})")
                ->implode("\n");

            //pesan ke plapor//
            if ($tlpPelapor) {
                $message = FonnteMessageService::pesanPenugasan($spk, $listStaf);
                FonnteService::sendMessage($tlpPelapor, $message);
            }

            foreach ($stafFinal as $staf) {
                if ($staf['is_penanggung_jawab'] === true) {
                    continue;
                }
                //pesan ke staf//
                if (!empty($staf['tlp'])) {
                    $messageStaff = FonnteMessageService::pesanUntukStaf($spk, $staf);
                    FonnteService::sendMessage($staf['tlp'], $messageStaff);
                }
            }
            //pesan ke pic//
            if (!empty($penanggung_jawab['tlp'])) {
                $messagePic = FonnteMessageService::pesanPenugasanPIC($spk, $penanggung_jawab, $listStaf);

                FonnteService::sendMessage(
                    $penanggung['tlp'],
                    $messagePic
                );
            }
            //pesan ke ygmenugaskan//
            if (!empty($tlpMengetahui)) {

                $messageMengetahui = FonnteMessageService::pesanUntukYangMenugaskan(
                    $spk,
                    $listStaf,
                    $penanggung_jawab['name']
                );

                FonnteService::sendMessage(
                    $tlpMengetahui,
                    $messageMengetahui
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil ditugaskan & semua notifikasi terkirim.',
                'data' => $spk
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    //update status spk\\
    public function updateByPenanggungJawab(Request $request, $uuid_pengajuan)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.update');
            if ($cek !== true) return $cek;

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau user eksternal tidak ditemukan.'
                ], 401);
            }

            $spk = Spk::where('uuid_pengajuan', $uuid_pengajuan)->firstOrFail();

            if ($spk->penanggung_jawab_npp !== $externalUser['npp']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk memperbarui SPK ini.'
                ], 403);
            }

            $request->validate([
                'status' => 'nullable|string|in:Pending,Proses,Selesai',
                'jenis_pekerjaan' => 'nullable|string',
                'kode_barang' => 'nullable|string',
                'uraian_pekerjaan' => 'nullable|string',
                'file' => 'nullable|string|min:1',
            ]);

            $normalizeUrl = function ($url) {
                if (!$url) return null;

                $cleaned = str_replace('\\', '/', $url);
                $cleaned = str_replace('//', '/', $cleaned);

                if (Str::startsWith($cleaned, 'http:/') && !Str::startsWith($cleaned, 'http://')) {
                    $cleaned = str_replace('http:/', 'http://', $cleaned);
                }
                if (Str::startsWith($cleaned, 'https:/') && !Str::startsWith($cleaned, 'https://')) {
                    $cleaned = str_replace('https:/', 'https://', $cleaned);
                }

                return $cleaned;
            };

            $normalizeFilePaths = function ($filePaths) use ($normalizeUrl) {
                if (is_array($filePaths)) {
                    return array_map(fn($f) => $normalizeUrl($f), $filePaths);
                }

                if (is_string($filePaths)) {
                    $cleanString = str_replace(['[', ']', '"'], '', $filePaths);
                    $arr = array_map('trim', explode(',', $cleanString));
                    return array_map(fn($f) => $normalizeUrl($f), $arr);
                }

                return [];
            };

            $filePaths = $normalizeFilePaths($request->file);

            $spk->update([
                'status'           => $request->status ?? $spk->status,
                'jenis_pekerjaan'  => $request->jenis_pekerjaan ?? $spk->jenis_pekerjaan,
                'kode_barang'      => $request->kode_barang ?? $spk->kode_barang,
                'uraian_pekerjaan' => $request->uraian_pekerjaan ?? $spk->uraian_pekerjaan,
                'file'             => $filePaths ?? $spk->file,
            ]);

            $pengajuan = Pengajuan::where('uuid', $spk->uuid_pengajuan)->first();
            $tlpPelapor = $pengajuan->tlp_pelapor ?? null;

            if (!empty($tlpPelapor)) {
                $message = \App\Services\FonnteMessageService::pesanUpdateStatusPekerjaan($spk, $pengajuan);

                \App\Services\FonnteService::sendMessage(
                    $tlpPelapor,
                    $message
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil diperbarui & pelapor menerima notifikasi.',
                'data' => $spk->fresh(),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Data SPK tidak ditemukan untuk UUID: ' . $uuid_pengajuan,
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    //delete\\
    public function softDelete(Request $request, $uuid_pengajuan)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.delete');
            if ($cek !== true) return $cek;

            $spk = Spk::where('uuid_pengajuan', $uuid_pengajuan)->firstOrFail();


            $spk->is_deleted = 1;
            $spk->save();

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil dihapus (soft delete)',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Data SPK tidak ditemukan untuk UUID: ' . $uuid_pengajuan,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus SPK: ' . $e->getMessage(),
            ], 500);
        }
    }

    //view\\
    public function showSpk(Request $request, $uuid_pengajuan)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.view');
            if ($cek !== true) return $cek;

            $spk = \App\Models\Spk::where('uuid_pengajuan', $uuid_pengajuan)
                ->where('is_deleted', 0)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $spk
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'SPK tidak ditemukan untuk UUID: ' . $uuid_pengajuan
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Riwayat SPK berdasarkan NPP yang pic\\
    public function getSpkBypic($npp, Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.riwayat');
            if ($cek !== true) return $cek;

            if (!$externalUser || !isset($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau NPP tidak ditemukan.'
                ], 401);
            }

            $data = Spk::where('penanggung_jawab_npp', $npp)
                ->where('is_deleted', 0)
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada atau kosong.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'okkk.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    //staf\\
    public function getSpkBystaf($npp, Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.riwayat');
            if ($cek !== true) return $cek;

            if (!$externalUser || !isset($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau NPP tidak ditemukan.'
                ], 401);
            }

            $data = Spk::where('is_deleted', 0)
                ->whereJsonContains('stafs', [['npp' => $npp]])
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada atau kosong.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'okkk.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getSpkBymengetahui($npp, Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.riwayat');
            if ($cek !== true) return $cek;

            if (!$externalUser || !isset($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau NPP tidak ditemukan.'
                ], 401);
            }

            $data = Spk::where('mengetahui_npp', $npp)
                ->where('is_deleted', 0)
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada atau kosong.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'okkk.',
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
