<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spks', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid_pengajuan');
            $table->date('tanggal')->nullable();

            $table->unsignedBigInteger('jenis_pekerjaan_id')->nullable();
            $table->string('kode_barang')->nullable();

            $table->text('uraian_pekerjaan')->nullable();
            $table->json('stafs')->nullable();

            $table->string('penanggung_jawab_npp', 50)->nullable();
            $table->string('penanggung_jawab_tlp', 30)->nullable();
            $table->string('penanggung_jawab_name')->nullable();
            $table->longText('penanggung_jawab_ttd')->nullable();

            $table->string('menyetujui')->nullable();
            $table->string('menyetujui_name')->nullable();
            $table->string('menyetujui_npp', 50)->nullable();
            $table->string('menyetujui_tlp', 30)->nullable();
            $table->longText('menyetujui_ttd')->nullable();

            $table->string('mengetahui')->nullable();
            $table->string('mengetahui_name')->nullable();
            $table->string('mengetahui_npp', 50)->nullable();
            $table->string('mengetahui_tlp', 30)->nullable();
            $table->longText('mengetahui_ttd')->nullable();

            $table->string('npp_kepala_satker', 50)->nullable();
            $table->json('file')->nullable();

            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('no_surat')->nullable();
            $table->string('no_referensi')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spks');
    }
};
