<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spks', function (Blueprint $table) {
            $table->id();
            $table->string('uuid_pengajuan');
            $table->date('tanggal');
            $table->string('jenis_pekerjaan_id');
            $table->string('kode_barang');
            $table->text('uraian_pekerjaan')->nullable();
            $table->json('stafs');
            $table->string('penanggung_jawab_npp');
            $table->string('penanggung_jawab_name');
            $table->string('status')->default('pending'); 
           
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spks');
    }
};
