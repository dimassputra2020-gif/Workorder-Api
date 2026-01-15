<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterJenisPekerjaanController;
use App\Http\Controllers\MasterStatusController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\HalController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\TrackingController;
use App\Helpers\PermissionStore;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PermissionController;
use Mockery\Matcher\Not;

Route::middleware(['check.external.token'])->group(function () {
    Route::get('/satker', [SatkerController::class, 'getSatker']);
    Route::get('/user/tlp/{npp}', [SatkerController::class, 'getTlpByNpp']);
});



Route::middleware(['check.external.token'])->group(function () {
    Route::post('/pengajuan', [PengajuanController::class, 'store']);
    Route::put('/pengajuan/{uuid}/status', [PengajuanController::class, 'updateStatus']);
    Route::put('/pengajuan/edit/{uuid}', [PengajuanController::class, 'edit']);
    Route::get('/pengajuan/views', [PengajuanController::class, 'index']);
    Route::delete('/pengajuan/delete/{uuid}', [PengajuanController::class, 'softDelete']);
    Route::get('/pengajuan/view/{uuid}', [PengajuanController::class, 'showPengajuan']);
    Route::get('/user/ttd/{npp}', [PengajuanController::class,  'getMyTTD']);
    Route::get('/pengajuan/rferensi/surat', [PengajuanController::class, 'listNoSurat']);
    Route::post('/user/create/ttd', [PengajuanController::class, 'create']);
    Route::delete('/user/delete/ttd', [PengajuanController::class, 'deleteTtd'])
        ->where('url', '.*');

    //menampilkan data pengajuan yg rilet//
    Route::get('/pengajuan/riwayat', [PengajuanController::class, 'riwayat']);
});

//master//
Route::middleware(['check.external.token'])->group(function () {
    Route::get('/hal', [HalController::class, 'index']);
    Route::get('/master-jenis-pekerjaan', [MasterJenisPekerjaanController::class, 'index']);
    Route::get('/master/status/spk', [MasterStatusController::class, 'index']);
});


//====SPK===\\
Route::middleware(['check.external.token'])->group(function () {
    Route::post('/spk/menugaskan', [SpkController::class, 'menugaskan']);
    Route::put('/spk/update/{uuid_pengajuan}', [SpkController::class, 'updateSpk']);
    Route::get('/spk/views/data', [SPKController::class, 'index']);
    Route::delete('/spk/delete/{uuid_pengajuan}', [SPKController::class, 'softDelete']);
    Route::get('/spk/view/{uuid_pengajuan}', [SpkController::class, 'showSpk']);
    Route::put(
        '/update/mengetahui/{uuid_pengajuan}',
        [SpkController::class, 'updateByMengetahui']
    );

    //menampilkan data riwayat spk yg rilet\\
    Route::get('/spk/riwayat', [SpkController::class, 'getRiwayatSpk']);
});

//tracking//
Route::middleware(['check.external.token'])->group(function () {
    Route::get('/tracking/nosurat/{no_surat}', [TrackingController::class, 'getByNoSurat'])
        ->where('no_surat', '.*');
    Route::get('/tracking/uuid/{uuid}', [TrackingController::class, 'tracking']);
});

//notif//
Route::middleware(['check.external.token'])->group(function () {
    Route::put('/notifications/update/{id}', [NotifController::class, 'update']);
    Route::get('/notifications/all/{npp}', [NotifController::class, 'getAllNotifications']);
    Route::put('/notifications/update/all/{npp}', [NotifController::class, 'markAllAsRead']);
});

  Route::get('/notifications/{npp}', [NotifController::class, 'getNotifications']);

//dashboard//
Route::middleware(['check.external.token'])->group(function () {
    Route::get('/dashboard/data', [DashboardController::class, 'index']);
});

Route::middleware(['check.external.token'])->group(function () {
    Route::get('/report/data/pengajuan', [ReportController::class, 'pengajuan']);
    Route::get('/report/data/spk', [ReportController::class, 'spk']);
});

Route::get('/workorder/permissions', function () {
    return response()->json([
        "status" => 200,
        "message" => "OK",
        "data" => [
            "permissions" => [
                "workorder-pti.pengajuan.create", // ini untuk membuat pengajuan
                "workorder-pti.pengajuan.edit",  // ini untuk mengedit pengajuan
                "workorder-pti.pengajuan.delete",  // ini untuk menghapus pengajuan
                "workorder-pti.pengajuan.view",  // ini untuk melihat detail pengajuan
                "workorder-pti.pengajuan.views",  // ini untuk melihat list pengajuan
                "workorder-pti.pengajuan.riwayat.views", // ini untuk melihat list riwayat pengajuan (untuk pelapor dan yg mengetahui)
                "workorder-pti.pengajuan.riwayat.view", // ini untuk melihat detail riwayat pengajuan (untuk pelapor dan yg mengetahui)
                "workorder-pti.pengajuan.riwayat.delete", // ini untuk menghapus riwayat pengajuan (untuk pelapor dan yg mengetahui)
                "workorder-pti.pengajuan.riwayat.edit", // ini untuk mengedit riwayat pengajuan (untuk pelapor dan yg mengetahui)
                "workorder-pti.pengajuan.approval", // ini untuk menyetujui atau menolak pengajuan (untuk yg mengetahui)

                "workorder-pti.spk.menugaskan", // ini untuk menugaskan spk (kasub)
                "workorder-pti.spk.update", // ini untuk mengupdate spk (pic, kasub, kabid)
                "workorder-pti.spk.views", // ini untuk melihat list spk (pic, kasub, kabid)
                "workorder-pti.spk.view",  // ini untuk melihat detail spk (pic, kasub, kabid)
                "workorder-pti.spk.delete", // ini untuk menghapus spk (pic, kasub, kabid)
                "workorder-pti.spk.riwayat.views", // ini untuk melihat list riwayat spk (pic,staf)
                "workorder-pti.spk.riwayat.delete", // ini untuk menghapus riwayat spk (pic,staf)
                "workorder-pti.spk.riwayat.view", // ini untuk melihat detail riwayat spk (pic,staf)
                "workorder-pti.spk.riwayat.edit", // ini untuk mengedit riwayat spk (pic,staf)

                "workorder-pti.view.dashboard", // ini untuk melihat data di dashboard
                "workorder-pti.Admin",  // ini untuk akses semua fitur di workorder (halmn admin)
                "workorder-pti.view.pengaturan", // ini untuk melihat halaman pengaturan
                "workorder-pti.view.pengaturan.profil", // ini untuk melihat halaman pengaturan profil
                "workorder-pti.view.history.ttd", // ini untuk melihat halaman history ttd
                "workorder-pti.delete.history.ttd", // ini untuk menghapus history ttd

                "workorder-pti.view.laporan", // ini untuk melihat halaman laporan
                "workorder-pti.view.laporan.pengaturan.cetak", // ini untuk melihat pengaturan cetak laporan

                "workorder-pti.spk.next", // ini untuk melanjutkan spk yg statusnya belum selesai
            ]
        ]
    ]);
});
Route::middleware(['check.external.token'])->group(function () {
    Route::post(
        '/workorder/permissions/set',
        [PermissionController::class, 'setPermissions']
    );

    Route::get(
        '/workorder/permissions/{npp}',
        [PermissionController::class, 'getPermissions']
    );
});
