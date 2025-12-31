<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            
            // tambah kolom baru
            $table->string('npp_kepada')->nullable()->after('id');

            // rename mengetahui_1 → menyetujui
            $table->renameColumn('mengetahui_1_npp', 'menyetujui_npp');
            $table->renameColumn('mengetahui_1', 'menyetujui');
            $table->renameColumn('ttd_mengetahui_1', 'menyetujui_ttd');
            $table->renameColumn('mengetahui_1_name', 'menyetujui_name');
            $table->renameColumn('mengetahui_1_tlp', 'menyetujui_tlp');

            // rename mengetahui_2 → tanpa angka
            $table->renameColumn('mengetahui_2', 'mengetahui');
            $table->renameColumn('mengetahui_2_name', 'mengetahui_name');
            $table->renameColumn('mengetahui_2_npp', 'mengetahui_npp');
            $table->renameColumn('mengetahui_2_tlp', 'mengetahui_tlp');
            $table->renameColumn('ttd_mengetahui_2', 'mengetahui_ttd');
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {

            $table->dropColumn('npp_kepada');

            // revert menyetujui → mengetahui_1
            $table->renameColumn('menyetujui_npp', 'mengetahui_1_npp');
            $table->renameColumn('menyetujui', 'mengetahui_1');
            $table->renameColumn('menyetujui_ttd', 'ttd_mengetahui_1');
            $table->renameColumn('menyetujui_name', 'mengetahui_1_name');
            $table->renameColumn('menyetujui_tlp', 'mengetahui_1_tlp');

            // revert mengetahui → mengetahui_2
            $table->renameColumn('mengetahui', 'mengetahui_2');
            $table->renameColumn('mengetahui_name', 'mengetahui_2_name');
            $table->renameColumn('mengetahui_npp', 'mengetahui_2_npp');
            $table->renameColumn('mengetahui_tlp', 'mengetahui_2_tlp');
            $table->renameColumn('mengetahui_ttd', 'ttd_mengetahui_2');
        });
    }
};
