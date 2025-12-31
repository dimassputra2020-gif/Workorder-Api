<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spks', function (Blueprint $table) {

            
            $table->dropColumn([
                'mengetahui_name',
                'mengetahui_npp',
                'mengetahui',        
                'ttd_mengetahui',
                'pelaksana_npp',
                'pelaksana',
                'ttd_pelaksana',
            ]);

            
            $table->string('mengetahui_1_name')->nullable();
            $table->string('mengetahui_2_name')->nullable();
            $table->string('penanggung_jawab_ttd')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {

            
            $table->dropColumn([
                'mengetahui_1_name',
                'mengetahui_2_name',
                'penanggung_jawab_ttd'
            ]);

          
            $table->string('mengetahui_name')->nullable();
            $table->string('mengetahui_npp')->nullable();
            $table->string('mengetahui')->nullable();
            $table->string('ttd_mengetahui')->nullable();
            $table->string('pelaksana_npp')->nullable();
            $table->string('pelaksana')->nullable();
            $table->string('ttd_pelaksana')->nullable();

        });
    }
};
