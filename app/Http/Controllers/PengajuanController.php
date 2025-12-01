<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Spk;
use App\Models\user;
use App\Models\MasterHal;

class PengajuanController extends Controller
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


    public function store(Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.create');
            if ($cek !== true) return $cek;

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user eksternal tidak ditemukan. Pastikan token valid.'
                ], 401);
            }
            $request->validate([
                'hal_id' => 'required|exists:masterhal,id',
                'kepada' => 'nullable|string',
                'satker' => 'nullable|string',
                'kode_barang' => 'nullable|string',
                'keterangan' => 'nullable|string',
                'file_paths' => 'required|string|min:1',
                'ttd_pelapor' => 'nullable|string',
                'no_referensi' => 'nullable|string',
                'mengetahui'      => 'nullable|string',
                'mengetahui_name' => 'nullable|string',
                'mengetahui_npp'  => 'nullable|string',
                'mengetahui_tlp'  => 'nullable|string',
            ]);
            $extractPath = function ($url) {
                if (!$url) return null;

                $parsed = parse_url($url);
                $path = $parsed['path'] ?? $url;

                return ltrim($path, '/');
            };
            $npp  = $externalUser['npp'] ?? null;
            $name = $externalUser['name'] ?? null;
            $tlp  = $externalUser['tlp'] ?? ($externalUser['rl_pegawai']['tlp'] ?? null);

            $user = User::where('npp', $npp)->first();

            $cleanTtd = $extractPath($request->ttd_pelapor);

            if (!$user) {
                $user = User::create([
                    'name'     => $name,
                    'npp'      => $npp,
                    'ttd_path' => $cleanTtd,
                ]);
            } else {

                if ($cleanTtd) {
                    $user->update([
                        'ttd_path' => $cleanTtd
                    ]);
                }
            }

            $finalTtdPelapor = $extractPath($user->ttd_path);

            $filePaths = $request->file_paths;

            if (is_string($filePaths)) {
                $filePaths = str_replace(['[', ']', '"'], '', $filePaths);
                $filePaths = array_map('trim', explode(',', $filePaths));
            }

            $filePaths = array_map(function ($f) use ($extractPath) {
                return $extractPath($f);
            }, $filePaths);

            $masterHal = MasterHal::find($request->hal_id);

            if (!$masterHal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data master hal tidak ditemukan.'
                ], 404);
            }

            $hal = MasterHal::find($request->hal_id);

            $prefix = $hal->kode;
            $bulan = date('m');
            $tahun = date('Y');

            $last = Pengajuan::whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->orderByRaw("CAST(SUBSTRING(no_surat, 1, 6) AS UNSIGNED) DESC")
                ->first();

            $increment = $last ? intval(substr($last->no_surat, 0, 6)) + 1 : 1;
            $incrementFormatted = str_pad($increment, 6, '0', STR_PAD_LEFT);

            $noSurat = "{$incrementFormatted}/{$prefix}/{$bulan}/{$tahun}";

            $uuid = (string) Str::uuid();

            $pengajuan = Pengajuan::create([
                'uuid'               => $uuid,
                'hal_id'            => $request->hal_id,
                'hal'          => $masterHal->nama_jenis,
                'kepada'             => $request->kepada,
                'satker'             => $request->satker,
                'kode_barang'        => $request->kode_barang,
                'keterangan'         => $request->keterangan,
                'file'               => $filePaths,
                'ttd_pelapor'        => $finalTtdPelapor,
                'status'             => 'pending',
                'is_deleted'         => 0,
                'name_pelapor'       => $user->name,
                'npp_pelapor'        => $user->npp,
                'tlp_pelapor'        => $tlp,
                'mengetahui'         => $request->mengetahui,
                'mengetahui_name'    => $request->mengetahui_name,
                'mengetahui_npp'     => $request->mengetahui_npp,
                'mengetahui_tlp'     => $request->mengetahui_tlp,
                'no_surat'           => $noSurat,
                'no_referensi'       => $request->no_referensi,
            ]);

            $pengajuan->load('masterhal');

            //   if ($request->mengetahui_tlp) {

            //   $message = \App\Services\FonnteMessageService::pengajuanBaru(
            //       $pengajuan
            //   );

            //   \App\Services\FonnteService::sendMessage(
            //    $request->mengetahui_tlp,
            //    $message
            // );
            //  }

            if ($pengajuan->tlp_pelapor) {

                $messagePelapor = \App\Services\FonnteMessageService::pengajuanBerhasilDikirim(
                    $pengajuan
                );

                \App\Services\FonnteService::sendMessage(
                    $pengajuan->tlp_pelapor,
                    $messagePelapor
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dibuat & WA terkirim.',
                'data' => $pengajuan,
            ]);
        } catch (\Exception $e) {
            Log::error('Pengajuan store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }


    //update status\\ 
    public function updateStatus(Request $request, $uuid)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.approval');
            if ($cek !== true) return $cek;

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user eksternal tidak ditemukan. Pastikan token valid.'
                ], 401);
            }

            // VALIDASI: TTD wajib upload
            $request->validate([
                'status' => 'required|in:pending,approved,rejected',
                'ttd_mengetahui' => 'required|string', // WAJIB
            ]);

            $pengajuans = Pengajuan::where('uuid', $uuid)->first();

            if (!$pengajuans) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengajuan tidak ditemukan',
                ], 404);
            }

            if (
                ($pengajuans->is_deleted ?? 0) == 1 ||
                ($pengajuans->deleted ?? 0) == 1 ||
                ($pengajuans->is_delete ?? 0) == 1
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan ini sudah dihapus, tidak dapat diperbarui.',
                ], 400);
            }


            $pengajuans->update([
                'status' => $request->status,
            ]);

            $originalMengetahuiName = $pengajuans->mengetahui_name;
            $originalMengetahuiNpp  = $pengajuans->mengetahui_npp;
            $originalMengetahuiTlp  = $pengajuans->mengetahui_tlp;

            $normalizeUrl = function ($url) {
                if (!$url) return null;
                $cleaned = str_replace(['\\', '//'], ['/', '/'], $url);

                if (Str::startsWith($cleaned, 'http:/') && !Str::startsWith($cleaned, 'http://')) {
                    $cleaned = str_replace('http:/', 'http://', $cleaned);
                }
                if (Str::startsWith($cleaned, 'https:/') && !Str::startsWith($cleaned, 'https://')) {
                    $cleaned = str_replace('https:/', 'https://', $cleaned);
                }

                return $cleaned;
            };

            $finalTtdMengetahui = $normalizeUrl($request->ttd_mengetahui);

            $pengajuans->update([
                'mengetahui_name' => $originalMengetahuiName,
                'mengetahui_npp'  => $originalMengetahuiNpp,
                'mengetahui_tlp'  => $originalMengetahuiTlp,
                'ttd_mengetahui'  => $finalTtdMengetahui,
            ]);

            $spk = Spk::where('uuid_pengajuan', $uuid)->first();
            if ($request->status == 'approved' && !$spk) {
                $spk = Spk::create([
                    'uuid_pengajuan' => $uuid,
                    'no_surat'       => $pengajuans->no_surat,
                    'tanggal'        => now(),
                    'no_referensi'   => $pengajuans->no_referensi,
                    'status'         => 'draft',
                ]);
            }

            $noReferensi = $spk->no_referensi ?? null;
            $pengajuans->load('masterhal');

            if (!empty($pengajuans->tlp_pelapor)) {


                switch ($request->status) {

                    case 'approved':
                        $waMessage = \App\Services\FonnteMessageService::statuspengajuan($pengajuans);
                        break;

                    case 'rejected':
                        $waMessage = \App\Services\FonnteMessageService::statuspengajuan($pengajuans);
                        break;

                    case 'pending':
                        $waMessage = \App\Services\FonnteMessageService::statuspengajuan($pengajuans);
                        break;

                    default:
                        $waMessage = \App\Services\FonnteMessageService::statuspengajuan($pengajuans);
                        break;
                }

                \App\Services\FonnteService::sendMessage(
                    $pengajuans->tlp_pelapor,
                    $waMessage
                );
            }

            if (!empty($pengajuans->mengetahui_tlp)) {

                $msgMengetahui = \App\Services\FonnteMessageService::pesanUpdateStatusMengetahui($pengajuans);


                \App\Services\FonnteService::sendMessage(
                    $pengajuans->mengetahui_tlp,
                    $msgMengetahui
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pengajuan berhasil diperbarui',
                'data' => [
                    'pengajuan'     => $pengajuans,
                    'no_referensi'  => $noReferensi,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Update status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    //edit pengajuan\\
    public function edit(Request $request, $uuid)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.edit');
            if ($cek !== true) return $cek;

            $pengajuan = Pengajuan::where('uuid', $uuid)->first();

            if (!$pengajuan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengajuan tidak ditemukan',
                ], 404);
            }


            $request->validate([
                'hal_id' => 'sometimes|exists:masterhal,id',
                'kepada' => 'nullable|string',
                'satker' => 'nullable|string',
                'kode_barang' => 'nullable|string',
                'keterangan' => 'nullable|string',
                'file_paths' => 'required|string|min:1',
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


            if ($request->filled('hal_id')) $pengajuan->hal_id = $request->hal_id;
            if ($request->filled('kepada')) $pengajuan->kepada = $request->kepada;
            if ($request->filled('satker')) $pengajuan->satker = $request->satker;
            if ($request->filled('kode_barang')) $pengajuan->kode_barang = $request->kode_barang;
            if ($request->filled('keterangan')) $pengajuan->keterangan = $request->keterangan;


            $filePaths = $normalizeFilePaths($request->file_paths);
            $pengajuan->file = $filePaths;

            $pengajuan->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil diperbarui',
                'data' => $pengajuan,
            ]);
        } catch (\Exception $e) {
            Log::error('Update pengajuan error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    //delete pengajuan\\
    public function softDelete(Request $request, $uuid)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.delete');
            if ($cek !== true) return $cek;

            $pengajuan = Pengajuan::where('uuid', $uuid)->firstOrFail();


            $pengajuan->is_deleted = 1;
            $pengajuan->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dihapus (soft delete)',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan tidak ditemukan untuk UUID: ' . $uuid,
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengajuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    //views\\
    public function index(Request $request)
    {
        $externalUser = $request->attributes->get('external_user');
        $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.views');
        if ($cek !== true) return $cek;

        $data = Pengajuan::where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    //view\\
    public function showPengajuan(Request $request, $uuid)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.view');
            if ($cek !== true) return $cek;

            $pengajuan = \App\Models\Pengajuan::where('uuid', $uuid)
                ->where('is_deleted', 0)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $pengajuan
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan untuk UUID: ' . $uuid
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    //ttd\\
    public function getMyTTD(Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }

            $user = User::where('npp', $externalUser['npp'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $ttdList = $user->ttd_list ?? [];

            if (is_string($ttdList)) {
                $ttdList = json_decode($ttdList, true) ?? [];
            }

            if (!is_array($ttdList)) {
                $ttdList = [];
            }

            return response()->json([
                'success' => true,
                'ttd_path' => $user->ttd_path ?? null,
                'ttd_list' => $ttdList
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $th->getMessage()
            ], 500);
        }
    }

    //tambah ttd\\
    public function create(Request $request)
    {
        try {

            $externalUser = $request->attributes->get('external_user');

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau user tidak ditemukan.'
                ], 401);
            }


            $request->validate([
                'ttd_path' => 'required|string'
            ]);


            $user = User::where('npp', $externalUser['npp'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan.'
                ], 404);
            }


            $ttdList = $user->ttd_list ?? [];

            if (is_string($ttdList)) {
                $ttdList = json_decode($ttdList, true) ?? [];
            }

            if (!is_array($ttdList)) {
                $ttdList = [];
            }


            $ttdList[] = $request->ttd_path;


            $user->ttd_list = $ttdList;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'TTD baru berhasil ditambahkan.',
                'data' => $ttdList
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $th->getMessage()
            ], 500);
        }
    }

    //delete ttd\\
    public function deleteTtd(Request $request, $url)
    {
        $url = urldecode($url);

        $externalUser = $request->attributes->get('external_user');

        if (!$externalUser) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.'
            ], 401);
        }

        $user = User::where('npp', $externalUser['npp'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }


        if ($user->ttd_path === $url) {

            $absolute = public_path($user->ttd_path);

            if ($user->ttd_path && file_exists($absolute)) {
                @unlink($absolute);
            }

            $user->ttd_path = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'TTD utama berhasil dihapus.'
            ]);
        }


        $list = json_decode($user->ttd_list ?? '[]', true);
        $index = array_search($url, $list);

        if ($index !== false) {

            $absolute = public_path($list[$index]);

            if (!empty($list[$index]) && file_exists($absolute)) {
                @unlink($absolute);
            }

            unset($list[$index]);
            $list = array_values($list);

            $user->ttd_list = json_encode($list);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'TTD list berhasil dihapus.'
            ]);
        }


        return response()->json([
            'success' => false,
            'message' => 'URL TTD tidak ditemukan di ttd_path atau ttd_list.'
        ], 404);
    }



    //Riwayat pengajuan rilet pelapor\\
    public function getByNpp($npp, Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.riwayat');
            if ($cek !== true) return $cek;

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid.'
                ], 401);
            }

            $data = Pengajuan::where('npp_pelapor', $npp)
                ->where('is_deleted', 0)
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan untuk NPP tersebut.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pengajuan ditemukan.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
    //riwayat pengajuan rilt ygmengetahui\\
    public function byMengetahui($npp, Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.riwayat');
            if ($cek !== true) return $cek;

            if (!$externalUser || !isset($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau NPP tidak ditemukan.'
                ], 401);
            }


            $data = Pengajuan::where('mengetahui_npp', $npp)
                ->where('is_deleted', 0)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pengajuan berdasarkan mengetahui_npp.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    // Ambil no_surat + keterangan \\
    public function listNoSurat(Request $request)
    {
        try {

            $externalUser = $request->attributes->get('external_user');

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid.'
                ], 401);
            }

            $data = Pengajuan::select('no_surat', 'keterangan')
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data no surat.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data no surat berhasil dimuat.',
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
