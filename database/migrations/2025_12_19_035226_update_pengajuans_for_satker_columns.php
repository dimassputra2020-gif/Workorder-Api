<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengajuans', function (Blueprint $table) {
          
            $table->renameColumn('kepada', 'kd_satker');

            
            $table->string('npp_kepala_satker')->nullable()->after('kd_satker');
        });
    }

    public function down()
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->renameColumn('kd_satker', 'kepada');
            $table->dropColumn('npp_kepala_satker');
        });
    }
};
