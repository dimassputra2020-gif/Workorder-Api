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

            // ➜ Field baru
            $table->string('pelaksana')->nullable()->after('penanggung_jawab_npp');
            $table->string('mengetahui_1')->nullable()->after('pelaksana');
            $table->string('mengetahui_2')->nullable()->after('mengetahui_1');

            // ➜ TTD untuk tanda tangan digital (URL / path)
            $table->string('ttd_pelaksana')->nullable()->after('mengetahui_2');
            $table->string('ttd_mengetahui_1')->nullable()->after('ttd_pelaksana');
            $table->string('ttd_mengetahui_2')->nullable()->after('ttd_mengetahui_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {

            $table->dropColumn([
                'pelaksana',
                'mengetahui_1',
                'mengetahui_2',
                'ttd_pelaksana',
                'ttd_mengetahui_1',
                'ttd_mengetahui_2',
            ]);
        });
    }
};
