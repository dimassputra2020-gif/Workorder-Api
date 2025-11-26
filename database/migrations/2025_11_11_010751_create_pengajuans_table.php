<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('pengajuans', function (Blueprint $table) {
    $table->id();
    $table->integer('user_id')->nullable(); // bisa kosong
    $table->string('external_user_token')->nullable();
    $table->string('name_pelapor')->nullable();
    $table->string('npp_pelapor')->nullable();
    $table->string('mengetahui_name')->nullable();
    $table->string('mengetahui_npp')->nullable();
    $table->string('mengetahui')->nullable();
     $table->text('hal')->nullable();
      $table->text('catatan')->nullable();
      $table->text('kepada')->nullable();
     $table->text('satker')->nullable();
    $table->string('kode_barang');
    $table->text('keterangan')->nullable();
    $table->string('file')->nullable();
    $table->enum('status', ['pending','approved','rejected'])->default('pending');
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
