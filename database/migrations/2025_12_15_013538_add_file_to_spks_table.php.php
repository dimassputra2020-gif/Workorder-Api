<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            // tambah kolom file jika belum ada
            if (!Schema::hasColumn('spks', 'file')) {
                $table->text('file')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            if (Schema::hasColumn('spks', 'file')) {
                $table->dropColumn('file');
            }
        });
    }
};



