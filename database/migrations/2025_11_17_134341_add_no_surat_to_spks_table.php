<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
      {
        Schema::table('spks', function (Blueprint $table) {
            // Jika kolom file belum json, boleh ganti ke json
            if (!Schema::hasColumn('spks', 'no_surat')) {
                $table->json('no_surat')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            if (Schema::hasColumn('spks', 'no_surat')) {
                $table->dropColumn('no_surat');
            }
        });
    }
};
