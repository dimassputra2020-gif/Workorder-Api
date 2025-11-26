<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WorkorderPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

       $permissions = [
    // === Pelapor ===
    ['name' => 'work-order.report.create', 'label' => 'Buat laporan / pengajuan pekerjaan'],
    ['name' => 'work-order.report.view_own', 'label' => 'Lihat laporan milik sendiri'],
    ['name' => 'work-order.report.edit', 'label' => 'Edit laporan sebelum disetujui'],

    // === Penerima / Approver ===
    ['name' => 'work-order.approval.view_all', 'label' => 'Lihat semua laporan yang masuk'],
    ['name' => 'work-order.approval.approve', 'label' => 'Setujui laporan / work order'],
    ['name' => 'work-order.approval.reject', 'label' => 'Tolak laporan dengan alasan'],
    ['name' => 'work-order.workorder.assign', 'label' => 'Tugaskan pekerjaan ke pelaksana'],
    ['name' => 'work-order.workorder.monitor', 'label' => 'Pantau progres pekerjaan'],

    // === Pelaksana ===
    ['name' => 'work-order.task.view_assigned', 'label' => 'Lihat pekerjaan yang ditugaskan'],
    ['name' => 'work-order.task.update_status', 'label' => 'Update status pekerjaan'],
    ['name' => 'work-order.task.upload_report', 'label' => 'Upload hasil pekerjaan'],
    ['name' => 'work-order.task.comment', 'label' => 'Beri catatan / komentar pekerjaan'],
    ['name' => 'work-order.task.complete', 'label' => 'Selesaikan dan kirim hasil kerja'],
];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'label' => $permission['label'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('okkk');
    }
}
