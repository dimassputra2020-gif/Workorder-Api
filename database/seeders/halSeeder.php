<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Masterhal;
class HalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $data = [
            ['kode' => 'perbaikan_pc', 'nama_jenis' => 'Perbaikan PC'],
            ['kode' => 'perbaikan_printer', 'nama_jenis' => 'Perbaikan Printer'],
            ['kode' => 'perbaikan_jaringan', 'nama_jenis' => 'Perbaikan Jaringan'],
            ['kode' => 'pengadaan_barang', 'nama_jenis' => 'Pengadaan Barang'],
            ['kode' => 'permintaan_atk', 'nama_jenis' => 'Permintaan ATK'],
        ];

        MasterHal::insert($data);
    }
}
