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

            // NPP baru untuk pelaksana & mengetahui
            $table->string('pelaksana_npp')->nullable()->after('pelaksana');
            $table->string('mengetahui_1_npp')->nullable()->after('mengetahui_1');
            $table->string('mengetahui_2_npp')->nullable()->after('mengetahui_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->dropColumn([
                'pelaksana_npp',
                'mengetahui_1_npp',
                'mengetahui_2_npp',
            ]);
        });
    }
};
