<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            // Tambahan kolom acuan no surat lama
            $table->string('no_referensi')->nullable()->after('no_surat');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->dropColumn('no_referensi');
        });
    }
};
