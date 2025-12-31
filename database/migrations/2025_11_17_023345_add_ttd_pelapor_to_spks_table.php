<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
      {
        Schema::table('spks', function (Blueprint $table) {
            // Jika kolom file belum json, boleh ganti ke json
            if (!Schema::hasColumn('spks', 'ttd_pelapor')) {
                $table->json('ttd_pelapor')->nullable();
            }

            // Tambah kolom ttd_pelapor jika belum ada
            if (!Schema::hasColumn('spks', 'ttd_mengetahui')) {
                $table->string('ttd_mengetahui')->nullable()->after('ttd_pelapor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            if (Schema::hasColumn('spks', 'ttd_pelapor')) {
                $table->dropColumn('ttd_pelapor');
            }
        });
    }
};
