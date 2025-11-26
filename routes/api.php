<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SatkerController;
use App\Http\Controllers\Auth\MasterJenisPekerjaanController;
use App\Http\Controllers\Auth\MasterStatusController;
use App\Http\Controllers\Auth\PengajuanController;
use App\Http\Controllers\HalController;
use App\Http\Controllers\Auth\SpkController;
use App\Http\Controllers\Auth\TrackingController;
use App\Helpers\PermissionStore;
Route::get('/satker', [SatkerController::class, 'getSatker']); 
Route::get('/jabsatker', [SatkerController::class, 'getJabSatker']);
Route::get('/satker_name', [SatkerController::class, 'getsatkername']);


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
    Route::delete('/user/delete/ttd', [PengajuanController::class, 'delete']);

               //menampilkan data pengajuan yg rilet//
    Route::get('/pengajuan/pelapor/{npp}', [PengajuanController::class, 'getByNpp']);
    Route::get('/pengajuan/mengetahui/{npp}', [PengajuanController::class, 'byMengetahui']);
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
    Route::put('/spk/update/{uuid_pengajuan}', [SpkController::class, 'updateByPenanggungJawab']);
    Route::get('/spk/views/data', [SPKController::class, 'index']);
    Route::delete('/spk/delete/{uuid_pengajuan}', [SPKController::class, 'softDelete']); 
    Route::get('/spk/view/{uuid_pengajuan}', [SpkController::class, 'showSpk']);

            //menampilkan data riwayat spk yg rilet\\
    Route::get('/spk/pic/{npp}', [SpkController::class, 'getSpkBypic']);
    Route::get('/spk/staf/{npp}', [SpkController::class, 'getSpkBystaf']);
    Route::get('/spk/mengetahui/{npp}', [SpkController::class, 'getSpkBymengetahui']);
});

        //tracking//
Route::middleware(['check.external.token'])->group(function () {
    Route::get('/tracking/nosurat/{no_surat}', [TrackingController::class, 'getByNoSurat'])
    ->where('no_surat', '.*');
     Route::get('/tracking/uuid/{uuid}', [TrackingController::class, 'tracking']);

});

Route::get('/workorder/permissions', function () {
   return response()->json([
    "status" => 200,
    "message" => "OK",
    "data" => [
        "permissions" => [
            "Workorder.pengajuan.create",   
            "Workorder.pengajuan.edit",
            "Workorder.pengajuan.delete",
            "Workorder.pengajuan.view",
            "Workorder.pengajuan.views",
            "Workorder.pengajuan.riwayat",
            "Workorder.pengajuan.approval",

            "Workorder.spk.create.menugaskan",
            "Workorder.spk.update",
            "Workorder.spk.views",
            "Workorder.spk.view",
            "Workorder.spk.delete",
            "Workorder.spk.riwayat",
        ]
    ]
 ]);

Route::middleware(['check.external.token'])->group(function () {
Route::post('/workorder/permissions/set', function (Request $request) {

    $externalUser = $request->attributes->get('external_user');
    
    if (!$externalUser) {
        return response()->json([
            'success' => false,
            'message' => 'External user tidak valid.'
        ], 401);
    }

    $request->validate([
        'npp' => 'required|string',
        'permissions' => 'required|array'
    ]);

    PermissionStore::setPermissions(
        $request->npp,
        $request->permissions
    );

    return response()->json([
        "success" => true,
        "message" => "Permissions updated",
        "npp" => $request->npp,
        "permissions" => $request->permissions
    ]);
});
});

Route::middleware(['check.external.token'])->group(function () {
Route::get('/workorder/permissions/{npp}', function ($npp) {
    return response()->json([
        "success" => true,
        "npp" => $npp,
        "permissions" => PermissionStore::getPermissionsFor($npp)
    ]);
});
});

});


