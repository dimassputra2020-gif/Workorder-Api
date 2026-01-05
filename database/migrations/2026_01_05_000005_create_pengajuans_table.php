<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('hal_id');
            $table->string('kd_satker', 50)->nullable();
            $table->string('npp_kepala_satker', 50)->nullable();
            $table->string('satker')->nullable();

            $table->string('kode_barang')->nullable();
            $table->text('keterangan')->nullable();
            $table->json('file')->nullable();

            $table->string('status', 30)->default('draft');
            $table->uuid('uuid')->unique();
            $table->boolean('is_deleted')->default(false);

            $table->string('name_pelapor');
            $table->string('npp_pelapor', 50);
            $table->string('tlp_pelapor', 30)->nullable();

            $table->string('mengetahui')->nullable();
            $table->string('no_surat')->nullable();

            $table->string('mengetahui_name')->nullable();
            $table->string('mengetahui_npp', 50)->nullable();
            $table->string('mengetahui_tlp', 30)->nullable();

            $table->longText('ttd_pelapor')->nullable();
            $table->longText('ttd_mengetahui')->nullable();

            $table->string('no_referensi')->nullable();
            $table->text('catatan_status')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
