<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Spk;
use App\Models\Pengajuan;
use App\Services\FonnteMessageService;
use App\Services\FonnteService;
use App\models\InternalNotification;
use App\Models\MasterStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

    private function addTimeline($uuidPengajuan, $source, $title, $status, $message = null)
    {
        \App\Models\Timeline::create([
            'uuid_pengajuan' => $uuidPengajuan,
            'source'         => $source,
            'title'          => $title,
            'status'         => $status,
            'message'        => $message,
        ]);
    }



    //views\\
    public function index(Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.views');
            if ($cek !== true) return $cek;

            $npp = $externalUser['npp'] ?? null;

            if (!$npp) {
                return response()->json([
                    'success' => false,
                    'message' => 'NPP tidak ditemukan pada token.'
                ], 400);
            }

            $data = Spk::where('is_deleted', 0)
                ->where(function ($query) use ($npp) {
                    $query->where('npp_kepala_satker', $npp)
                        ->orWhere('menyetujui_npp', $npp)
                        ->orWhere('mengetahui_npp', $npp)
                        ->orWhere('penanggung_jawab_npp', $npp);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data SPK tidak ditemukan untuk NPP tersebut.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data SPK berhasil diambil.',
                'total' => $data->count(),
                'data' => $data->fresh()->load('status', 'jenisPekerjaan'),
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
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.menugaskan');
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
            $nppKepalaSatker = $spk->npp_kepala_satker ?? null;
            $tlpYangMenugaskan = null;

            if ($nppKepalaSatker) {
                $token = $request->bearerToken();
                $url = rtrim(env('BASE_URL'), '/') . "/api/masterdata/users/search-npps/{$nppKepalaSatker}";

                try {
                    $response = Http::withToken($token)
                        ->acceptJson()
                        ->timeout(15)
                        ->get($url);

                    if ($response->successful()) {
                        $data = $response->json();
                        $tlpRaw = $data['data'][0]['rl_pegawai_local']['tlp'] ?? null;

                        if ($tlpRaw) {
                            $tlpArray = preg_split('/[,\s]+/', $tlpRaw);
                            $tlpYangMenugaskan = collect($tlpArray)
                                ->map(fn($n) => preg_replace('/[^0-9]/', '', $n))
                                ->first(fn($n) => preg_match('/^08\d{6,12}$/', $n));
                        }
                    }
                } catch (\Illuminate\Http\Client\RequestException $e) {
                }
            }



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
                "status_id" => "5"
            ]);

            $namaMenugaskan = '-';

            if (!empty($spk->npp_kepala_satker)) {
                $token = $request->bearerToken();
                $url = rtrim(env('BASE_URL'), '/') . "/api/masterdata/users/search-npps/{$spk->npp_kepala_satker}";

                try {
                    $response = Http::withToken($token)
                        ->acceptJson()
                        ->timeout(10)
                        ->get($url);

                    if ($response->successful()) {
                        $data = $response->json();
                        $namaMenugaskan = $data['data'][0]['name']
                            ?? $data['data'][0]['nama']
                            ?? $externalUser['name']
                            ?? '-';
                    }
                } catch (\Exception $e) {
                    Log::warning('Gagal ambil nama kepala satker: ' . $e->getMessage());
                }
            }


            $namaStafDitugaskan = collect($spk->stafs ?? [])
                ->pluck('nama')
                ->implode(', ');



            $this->addTimeline(
                $spk->uuid_pengajuan,
                'spk',
                'SPK Ditugaskan',
                'Ditugaskan',
                'SPK ditugaskan oleh ' . $namaMenugaskan .
                    ' kepada staf: ' . ($namaStafDitugaskan ?: '-')
            );




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

            if ($pengajuan->npp_pelapor) {
                InternalNotification::create([
                    'uuid_pengajuan' => $pengajuan->uuid,
                    'npp' => $pengajuan->npp_pelapor,
                    'title' => 'SPK Ditugaskan',
                    'judul' => 'Penugasan ',
                    'pesan' => 'Pengajuan Anda telah ditugaskan oleh ' . $externalUser['name'],
                    'status' => 'unread'
                ]);
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

                if ($pengajuan->uuid) {
                    InternalNotification::create([
                        'uuid_pengajuan' => $pengajuan->uuid,
                        'npp' => $staf['npp'],
                        'title' => 'Penugasan Baru',
                        'judul' => 'Penugasan',
                        'pesan' => "Anda ditugaskan dalam SPK: {$spk->no_surat}",
                        'status' => 'unread'
                    ]);
                }
            }

            if ($pengajuan->uuid) {
                InternalNotification::create([
                    'uuid_pengajuan' => $pengajuan->uuid,
                    'npp' => $penanggung_jawab['npp'],
                    'title' => 'Penanggung Jawab Baru',
                    'judul' => 'Penugasan',
                    'pesan' => "Anda telah ditugaskan menjadi PIC pada SPK: {$spk->no_surat}",
                    'status' => 'unread'
                ]);
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
            if (!empty($tlpYangMenugaskan)) {
                $msgBoss = FonnteMessageService::pesanUntukYangMenugaskan(
                    $spk,
                    $listStaf,
                    $penanggung_jawab['name']
                );

                FonnteService::sendMessage($tlpYangMenugaskan, $msgBoss);
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
    public function updateSpk(Request $request, $uuid_pengajuan)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.update');
            if ($cek !== true) return $cek;

            if (!$externalUser || empty($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid.'
                ], 401);
            }

            $npp = $externalUser['npp'];

            $spk = Spk::where('uuid_pengajuan', $uuid_pengajuan)
                ->where('is_deleted', 0)
                ->firstOrFail();

            $extractPath = fn($url) =>
            $url ? ltrim(parse_url($url, PHP_URL_PATH), '/') : null;

            if ($spk->penanggung_jawab_npp === $npp) {

                $request->validate([
                    'status_id'            => 'required|exists:master_status,id',
                    'jenis_pekerjaan_id'   => 'required|exists:masterjenispekerjaan,id',
                    'kode_barang'          => 'nullable|string',
                    'uraian_pekerjaan'     => 'nullable|string',
                    'file'                 => 'nullable|array',
                    'penanggung_jawab_ttd' => 'required|string',

                    'menyetujui'         => 'nullable|string',
                    'menyetujui_name'    => 'nullable|string',
                    'menyetujui_npp'     => 'nullable|string',
                    'menyetujui_tlp'     => 'nullable|string',

                    'mengetahui'         => 'nullable|string',
                    'mengetahui_name'    => 'nullable|string',
                    'mengetahui_npp'     => 'nullable|string',
                    'mengetahui_tlp'     => 'nullable|string',

                    'tanggal' => 'nullable|date',
                ]);

                $extractPath = fn($url) =>
                $url ? ltrim(parse_url($url, PHP_URL_PATH), '/') : null;

                $filePaths = $request->file ?? $spk->file;

                $ttdpenanggungjawab = $request->penanggung_jawab_ttd
                    ? $extractPath($request->penanggung_jawab_ttd)
                    : $spk->penanggung_jawab_ttd;

                $spk->update([
                    'status_id'              => $request->status_id,
                    'jenis_pekerjaan_id'     => $request->jenis_pekerjaan_id ?? $spk->jenis_pekerjaan_id,
                    'kode_barang'            => $request->kode_barang ?? $spk->kode_barang,
                    'uraian_pekerjaan'       => $request->uraian_pekerjaan ?? $spk->uraian_pekerjaan,
                    'file'                   => $filePaths,
                    'penanggung_jawab_ttd'   => $ttdpenanggungjawab,

                    'menyetujui'           => $request->menyetujui      ?? $spk->menyetujui,
                    'menyetujui_name'      => $request->menyetujui_name ?? $spk->menyetujui_name,
                    'menyetujui_npp'       => $request->menyetujui_npp  ?? $spk->menyetujui_npp,
                    'menyetujui_tlp'       => $request->menyetujui_tlp  ?? $spk->menyetujui_tlp,

                    'mengetahui'           => $request->mengetahui      ?? $spk->mengetahui,
                    'mengetahui_name'      => $request->mengetahui_name ?? $spk->mengetahui_name,
                    'mengetahui_npp'       => $request->mengetahui_npp  ?? $spk->mengetahui_npp,
                    'mengetahui_tlp'       => $request->mengetahui_tlp  ?? $spk->mengetahui_tlp,

                    'tanggal'              => $request->tanggal ?? $spk->tanggal,
                ]);

                $namaPic = $spk->penanggung_jawab_name ?? 'PIC';


                $this->addTimeline(
                    $spk->uuid_pengajuan,
                    'spk',
                    'SPK Diperbarui oleh PIC',
                    'updated',
                    'SPK diperbarui oleh ' . $namaPic . ' '
                );


                // ntif menyetujui
                if (!empty($spk->menyetujui_npp)) {

                    if (!empty($spk->menyetujui_tlp)) {
                        FonnteService::sendMessage(
                            $spk->menyetujui_tlp,
                            FonnteMessageService::notifMenyetujui($spk)
                        );
                    }

                    InternalNotification::create([
                        'uuid_pengajuan' => $spk->uuid_pengajuan,
                        'npp'            => $spk->menyetujui_npp,
                        'title'          => 'SPK Menunggu Persetujuan',
                        'judul'          => 'Persetujuan SPK',
                        'pesan'          => "Halo {$spk->menyetujui_name}, SPK menunggu tanda tangan Anda.",
                        'status'         => 'unread'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'actor'   => 'PIC',
                    'message' => 'SPK berhasil diperbarui & notifikasi dikirim.',
                    'data'    => $spk->fresh()
                ], 200);
            }



            $request->validate([
                'ttd' => 'required|string'
            ]);

            $ttdPath = $extractPath($request->ttd);

            //menyetujui
            if ($spk->menyetujui_npp === $npp) {

                if ($spk->menyetujui_ttd) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mengetahui 1 sudah menandatangani.'
                    ], 400);
                }

                $spk->update(['menyetujui_ttd' => $ttdPath]);
                $namaMenyetujui = $spk->menyetujui_name ?? 'Pejabat Penyetuju';

                $this->addTimeline(
                    $spk->uuid_pengajuan,
                    'spk',
                    'Persetujuan SPK',
                    'approved',
                    'SPK telah disetujui oleh ' . $namaMenyetujui . '.'
                );



                // mengtahui ntif
                if (!empty($spk->mengetahui_npp)) {

                    if (!empty($spk->mengetahui_tlp)) {
                        FonnteService::sendMessage(
                            $spk->mengetahui_tlp,
                            FonnteMessageService::notifMengetahui($spk)
                        );
                    }

                    InternalNotification::create([
                        'uuid_pengajuan' => $spk->uuid_pengajuan,
                        'npp'   => $spk->mengetahui_npp,
                        'title' => 'SPK Menunggu tanda Tangan Anda',
                        'judul' => 'TTD SPK',
                        'pesan' => "Halo {$spk->mengetahui_name}, SPK  menunggu tanda tangan Anda.",
                        'status' => 'unread'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'actor' => 'menyetujui',
                    'message' => 'TTD  berhasil.',
                    'data' => $spk->fresh()
                ], 200);
            }

            //mengetahui
            if ($spk->mengetahui_npp === $npp) {

                if ($spk->mengetahui_ttd) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah menandatangani spk.'
                    ], 400);
                }

                $spk->update(['mengetahui_ttd' => $ttdPath]);

                $namaMengetahui = $spk->mengetahui_name ?? 'Pejabat Mengetahui';

                $this->addTimeline(
                    $spk->uuid_pengajuan,
                    'spk',
                    'TTD SPK',
                    'signed',
                    'SPK telah ditandatangani oleh ' . $namaMengetahui . '.'
                );


                //notif ke pelapor
                $pengajuan = Pengajuan::where('uuid', $spk->uuid_pengajuan)->first();

                if ($pengajuan) {

                    if (!empty($pengajuan->tlp_pelapor)) {
                        FonnteService::sendMessage(
                            $pengajuan->tlp_pelapor,
                            FonnteMessageService::notifSpkSelesai($spk, $pengajuan)
                        );
                    }

                    InternalNotification::create([
                        'uuid_pengajuan' => $pengajuan->uuid,
                        'npp'   => $pengajuan->npp_pelapor,
                        'title' => 'SPK Selesai',
                        'judul' => 'SPK Disetujui',
                        'pesan' => "Halo {$pengajuan->nama_pelapor}, SPK Anda telah disetujui oleh seluruh pihak.",
                        'status' => 'unread'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'actor' => 'MENGETAHUI',
                    'message' => 'SPK selesai & pelapor diberi notifikasi.',
                    'data' => $spk->fresh()
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak pada SPK ini.'
            ], 403);
        } catch (\Exception $e) {
            Log::error('Update SPK gagal', [
                'uuid_pengajuan' => $uuid_pengajuan,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
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
                'data' => $spk->fresh()->load('status', 'jenisPekerjaan'),
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

    public function getRiwayatSpk(Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.spk.riwayat');
            if ($cek !== true) return $cek;

            if (!$externalUser || empty($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid.'
                ], 401);
            }

            $npp = $externalUser['npp'];

            $spks = Spk::where('is_deleted', 0)
                ->where(function ($q) use ($npp) {
                    $q->where('penanggung_jawab_npp', $npp)
                        ->orWhereJsonContains('stafs', [['npp' => $npp]]);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($spks as $spk) {
                $stafMatches = collect($spk->stafs ?? [])
                    ->where('npp', $npp);

                if ($stafMatches->count() > 1) {
                    Log::warning('Duplikasi NPP di staf SPK', [
                        'uuid_pengajuan' => $spk->uuid_pengajuan,
                        'npp'            => $npp,
                        'count'          => $stafMatches->count()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'total'   => $spks->count(),
                'data'    => $spks
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Riwayat SPK error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }
}
