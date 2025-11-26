<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WorkorderPermissionSeeder::class);

        // === Buat 3 Role User ===
        $pelapor = User::create([
            'name' => 'Pelapor',
            'email' => 'pelapor@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $penerima = User::create([
            'name' => 'Penerima',
            'email' => 'penerima@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $pelaksana = User::create([
            'name' => 'Pelaksana',
            'email' => 'pelaksana@gmail.com',
            'password' => bcrypt('password'),
        ]);

        // === Bagi Permission ===
        $pelaporPermissions = Permission::where('name', 'like', 'work-order.report.%')->pluck('id');
        $penerimaPermissions = Permission::where('name', 'like', 'work-order.approval.%')
            ->orWhere('name', 'like', 'work-order.workorder.%')->pluck('id');
        $pelaksanaPermissions = Permission::where('name', 'like', 'work-order.task.%')->pluck('id');

        $pelapor->permissions()->sync($pelaporPermissions);
        $penerima->permissions()->sync($penerimaPermissions);
        $pelaksana->permissions()->sync($pelaksanaPermissions);

        $this->command->info('✅ Semua user dan permission berhasil diset!');
    }
}
