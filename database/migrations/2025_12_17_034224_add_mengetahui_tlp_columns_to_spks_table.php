<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->string('mengetahui_1_tlp', 20)->nullable()->after('mengetahui_1_npp');
            $table->string('mengetahui_2_tlp', 20)->nullable()->after('mengetahui_2_npp');
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->dropColumn([
                'mengetahui_1_tlp',
                'mengetahui_2_tlp'
            ]);
        });
    }
};
