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
use Illuminate\Support\Facades\Http;


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

    private function extractPath($url)
    {
        if (!$url) return null;

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? $url;

        return ltrim($path, '/');
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
                'satker' => 'nullable|string',
                'kd_satker' => 'required|string',
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

            $token = $request->bearerToken();
            $kdSatker = $request->kd_satker;

            $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';

            $response = Http::withToken($token)->timeout(5)->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat data satker dari API eksternal'
                ], 500);
            }

            $data = collect($response->json()['data'] ?? []);

            $satker = $data->firstWhere('kd_satker', $kdSatker);

            if (!$satker) {
                return response()->json([
                    'success' => false,
                    'message' => 'kd_satker tidak ditemukan.'
                ], 404);
            }

            $nppKepalaSatker = $satker['npp_kepala_satker'] ?? null;


            $kdSatker = $request->kd_satker;
            $nppSatker = $request->npp_kepala_satker;

            $pengajuan = Pengajuan::create([
                'uuid'               => $uuid,
                'hal_id'             => $request->hal_id,
                'satker'             => $request->satker,
                'hal'                => $masterHal->nama_jenis,
                'kd_satker'          => $kdSatker,
                'npp_kepala_satker'  => $nppKepalaSatker, // AUTO FILL
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



            $this->addTimeline(
                $uuid,
                'pengajuan',
                'Pengajuan Baru Dibuat',
                'pending',
                'Pengajuan berhasil dibuat oleh ' . $pengajuan->name_pelapor . '.'
            );



            $pengajuan->load('masterhal');

            if ($request->mengetahui_tlp) {

                $message = \App\Services\FonnteMessageService::pengajuanBaru(
                    $pengajuan
                );

                \App\Services\FonnteService::sendMessage(
                    $request->mengetahui_tlp,
                    $message
                );
            }

            if ($pengajuan->tlp_pelapor) {

                $messagePelapor = \App\Services\FonnteMessageService::pengajuanBerhasilDikirim(
                    $pengajuan
                );

                \App\Services\FonnteService::sendMessage(
                    $pengajuan->tlp_pelapor,
                    $messagePelapor
                );
            }


            if ($pengajuan->mengetahui_npp) {
                \App\Helpers\Notif::push(
                    $pengajuan->uuid,
                    $pengajuan->mengetahui_npp,
                    "Pengajuan Baru Masuk",
                    "Ada pengajuan baru dengan nomor surat $noSurat menunggu persetujuan Anda."
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
                    'message' => 'Data user eksternal tidak ditemukan.'
                ], 401);
            }

            $rules = [
                'status' => 'required|in:pending,approved,rejected',
            ];

            if ($request->status !== 'rejected') {

                $rules['ttd_mengetahui'] = 'required|string';
            }

            if ($request->status === 'rejected') {
                $rules['catatan_status'] = 'required|string|min:3';
            }

            $request->validate($rules);


            $pengajuans = Pengajuan::where('uuid', $uuid)->first();
            if (!$pengajuans) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengajuan tidak ditemukan.'
                ], 404);
            }

            if (
                ($pengajuans->is_deleted ?? 0) == 1 ||
                ($pengajuans->deleted ?? 0) == 1 ||
                ($pengajuans->is_delete ?? 0) == 1
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan sudah dihapus, tidak dapat diperbarui.'
                ], 400);
            }


$extractPath = function ($url) {
    if (!$url) return null;
    $parsed = parse_url($url);
    $path = $parsed['path'] ?? $url;
    return ltrim($path, '/');
};

$mengetahuiNpp  = $externalUser['npp'] ?? null;
$mengetahuiName = $externalUser['name'] ?? null;

$cleanTtdMengetahui = null;

if ($request->status !== 'rejected') {
    $cleanTtdMengetahui = $extractPath($request->ttd_mengetahui);
}

if ($mengetahuiNpp && $cleanTtdMengetahui) {

    $userMengetahui = User::where('npp', $mengetahuiNpp)->first();

    if (!$userMengetahui) {
        $userMengetahui = User::create([
            'name'     => $mengetahuiName,
            'npp'      => $mengetahuiNpp,
            'ttd_path' => $cleanTtdMengetahui,
        ]);
    } else {
        $userMengetahui->update([
            'ttd_path' => $cleanTtdMengetahui,
        ]);
    }

    // gunakan TTD dari tabel users sebagai final
    $finalTtdMengetahui = $userMengetahui->ttd_path;
} else {
    $finalTtdMengetahui = null;
}


$pengajuans->update([
    'status'         => $request->status,
    'catatan_status' => $request->status === 'rejected' ? $request->catatan_status : null,
    'ttd_mengetahui' => $finalTtdMengetahui,
]);



            $namaUpdater = $pengajuans->mengetahui_name ?? 'Mengetahui';

            $this->addTimeline(
                $uuid,
                'status',
                'Status Pengajuan Diupdate',
                $request->status,
                $request->status === 'rejected'
                    ? 'Ditolak oleh ' . $namaUpdater . '. Catatan: ' . $request->catatan_status
                    : 'Status diupdate menjadi ' . $request->status . ' oleh ' . $namaUpdater . '.'
            );


            


            $pengajuans->update([
                'status' => $request->status,
            ]);

            $originalMengetahuiName = $pengajuans->mengetahui_name;
            $originalMengetahuiNpp  = $pengajuans->mengetahui_npp;
            $originalMengetahuiTlp  = $pengajuans->mengetahui_tlp;

            $pengajuans->update([
                'mengetahui_name' => $originalMengetahuiName,
                'mengetahui_npp'  => $originalMengetahuiNpp,
                'mengetahui_tlp'  => $originalMengetahuiTlp,
                'kode_barang'    => $pengajuans->kode_barang,
            ]);
            $token = $request->bearerToken();

            $spk = Spk::where('uuid_pengajuan', $uuid)->first();

            if ($request->status === 'approved') {
                $nppKepala = $pengajuans->npp_kepala_satker ?? null;
                $tlpYangMenugaskan = null;

                if ($nppKepala) {
                    $url = rtrim(env('BASE_URL'), '/') . "/api/masterdata/users/search-npps/{$nppKepala}";

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
                        Log::error("Gagal mengambil data telepon: " . $e->getMessage());
                    }
                }

                if ($tlpYangMenugaskan) {
                    $message = \App\Services\FonnteMessageService::penugasanspk($pengajuans, $token);
                    \App\Services\FonnteService::sendMessage($tlpYangMenugaskan, $message);

                    \App\Helpers\Notif::push(
                        $pengajuans->uuid,
                        $nppKepala,
                        "Ada Penugasan SPK Baru",
                        "Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat {$pengajuans->no_surat}."
                    );
                }
                $spk = Spk::create([
                    'uuid_pengajuan' => $uuid,
                    'no_surat'       => $pengajuans->no_surat,
                    'kd_satker'       => $pengajuans->kd_satker,
                    'tanggal'        => now(),
                    'no_referensi'   => $pengajuans->no_referensi,
                    'kode_barang'    => $pengajuans->kode_barang,
                    'status_id'         => '4',
                    'npp_kepala_satker'  => $pengajuans->npp_kepala_satker,
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
            $nppPelapor = $pengajuans->npp_pelapor;
            $uuidpengajuan = $pengajuans->uuid;
            $statusText = $request->status;

            \App\Helpers\Notif::push(
                $uuidpengajuan,
                $nppPelapor,
                "Status Pengajuan Diupdate",
                "Pengajuan Anda telah diupdate menjadi: $statusText",
                [
                    'uuid' => $pengajuans->uuid,
                    'status' => $statusText
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Status pengajuan berhasil diperbarui',
                'data' => [
                    'pengajuan'     => $pengajuans,
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


    //edit/
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
                'hal_id'       => 'sometimes|exists:masterhal,id',
                'kd_satker'       => 'nullable|string',
                'satker'       => 'nullable|string',
                'kode_barang'  => 'nullable|string',
                'keterangan'   => 'nullable|string',
                'file_paths'   => 'nullable|string',
            ]);

            $extractPath = function ($url) {
                if (!$url) return null;

                $parsed = parse_url($url);
                $path = $parsed['path'] ?? $url;

                return ltrim($path, '/');
            };

            if ($request->filled('hal_id')) {
                $hal_id = MasterHal::find($request->hal_id);
                if ($hal_id) {
                    $pengajuan->hal_id = $hal_id->id;
                }
            }

            if ($request->filled('kd_satker')) $pengajuan->kepada = $request->kepada;
            if ($request->filled('satker')) $pengajuan->satker = $request->satker;
            if ($request->filled('kode_barang')) $pengajuan->kode_barang = $request->kode_barang;
            if ($request->filled('keterangan')) $pengajuan->keterangan = $request->keterangan;

            if ($request->has('file_paths')) {

                $existing = is_array($pengajuan->file) ? $pengajuan->file : [];

                $filePaths = $request->file_paths;

                if (is_string($filePaths)) {
                    $filePaths = str_replace(['[', ']', '"'], '', $filePaths);
                    $filePaths = array_map('trim', explode(',', $filePaths));
                }

                $newFiles = array_map(function ($f) use ($extractPath) {
                    return $extractPath($f);
                }, $filePaths);


                $mergedFiles = array_values(array_unique(array_merge($existing, $newFiles)));

                $pengajuan->file = $mergedFiles;
            }

            $pengajuan->save();

            $this->addTimeline(
                $uuid,
                'edit',
                'Pengajuan Diedit',
                $pengajuan->status,
                'Pengajuan telah diperbarui.'
            );

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

            $this->addTimeline(
                $uuid,
                'delete',
                'Pengajuan Delete',
                $pengajuan->is_deleted,
                'Pengajuan telah didelete.'
            );


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

        $npp = $externalUser['npp'];

        $data = Pengajuan::where('is_deleted', 0)
            ->where('mengetahui_npp', $npp)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

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

            $masterHal = \App\Models\MasterHal::find($pengajuan->hal_id);

            $token = $request->bearerToken();
            $url = rtrim(env('BASE_URL'), '/') . '/api/client/satker/all';

            $response = Http::withToken($token)
                ->timeout(30)
                ->retry(3, 200)
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception('Gagal mengambil data satker dari API eksternal');
            }

            $satkers = $response->json()['data'] ?? [];

            $satker = collect($satkers)
                ->where('kd_satker', $pengajuan->kd_satker)
                ->map(function ($item) {
                    return [
                        'kd_satker' => $item['kd_satker'] ?? null,
                        'satker_name' => $item['satker_name'] ?? null,
                    ];
                })
                ->first();



            $kdparents = $response->json()['data'] ?? [];

            $kdparent = collect($kdparents)
                ->where('kd_parent', $pengajuan->satker)
                ->map(function ($item) {
                    return [
                        'kd_parent' => $item['kd_parent'] ?? null,
                        'parent_satker' => $item['parent_satker'] ?? null,
                    ];
                })
                ->first();

            return response()->json([
                'success' => true,
                'data' => $pengajuan,
                'masterhal' => $masterHal,
                'kd_satker' => $satker,
                'kd_parent' => $kdparent
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

    ///delete ttd//
    public function deleteTtd(Request $request)
    {
        $incomingUrl = $request->input('ttd_url');

        if (empty($incomingUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter ttd_url wajib ada.'
            ], 400);
        }

        $targetPath = $incomingUrl;
        $parsed = parse_url($incomingUrl);

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            if (isset($queryParams['path'])) {
                $targetPath = $queryParams['path'];
            }
        }


        $targetPathClean = ltrim($targetPath, '/');
        $externalUser = $request->attributes->get('external_user');
        if (!$externalUser) {
            return response()->json(['success' => false, 'message' => 'Token invalid'], 401);
        }
        $user = User::where('npp', $externalUser['npp'])->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($user->ttd_path) {
            $dbPath = str_replace('\/', '/', $user->ttd_path);
            $dbPathClean = ltrim($dbPath, '/');

            if ($dbPathClean === $targetPathClean) {


                $absolute = public_path($dbPath);
                if (file_exists($absolute)) {
                    @unlink($absolute);
                }

                $user->ttd_path = null;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'TTD utama berhasil dihapus.'
                ]);
            }
        }

        $list = json_decode($user->ttd_list ?? '[]', true);

        $foundIndex = null;
        foreach ($list as $index => $item) {
            $itemClean = ltrim(str_replace('\/', '/', $item), '/');
            if ($itemClean === $targetPathClean) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== null) {
            $originalPath = str_replace('\/', '/', $list[$foundIndex]);
            $absolute = public_path($originalPath);

            if (file_exists($absolute)) {
                @unlink($absolute);
            }

            unset($list[$foundIndex]);
            $list = array_values($list);

            $user->ttd_list = json_encode($list, JSON_UNESCAPED_SLASHES);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'TTD list berhasil dihapus.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Gagal cocok. Input Bersih: [$targetPathClean]. DB Path User: [" . ltrim($user->ttd_path ?? '', '/') . "]"
        ], 404);
    }


    public function riwayat(Request $request)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $cek = $this->checkPermission($externalUser, 'Workorder.pengajuan.riwayat.views');
            if ($cek !== true) return $cek;

            if (!$externalUser || empty($externalUser['npp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid.'
                ], 401);
            }

            $npp = $externalUser['npp'];

            $data = Pengajuan::where('is_deleted', 0)
                ->where(function ($q) use ($npp) {
                    $q->where('npp_pelapor', $npp);
                       
                })
                ->orderBy('created_at', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada riwayat pengajuan.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pengajuan berhasil diambil.',
                'total' => $data->count(),
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('Riwayat pengajuan error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.'
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
