<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterJenisPekerjaanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('masterjenispekerjaan')->insert([
            ['kode' => 'Pemeliharaan_jrngn', 'nama_pekerjaan' => 'Pemeliharaan Jaringan'],
            ['kode' => 'perbaikan', 'nama_pekerjaan' => 'Perbaikan'],
            ['kode' => 'printer', 'nama_pekerjaan' => 'Printer'],
            ['kode' => 'monitor', 'nama_pekerjaan' => 'monitor'],
           
        ]);
    }
}
