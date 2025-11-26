<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuans', 'name')) {
                $table->string('name')->nullable()->after('status');
            }

            if (!Schema::hasColumn('pengajuans', 'npp')) {
                $table->string('npp')->nullable()->after('name');
            }

            if (!Schema::hasColumn('pengajuans', 'mengetahui')) {
                $table->string('mengetahui')->nullable()->after('npp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
           
            if (Schema::hasColumn('pengajuans', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('pengajuans', 'npp')) {
                $table->dropColumn('npp');
            }
            if (Schema::hasColumn('pengajuans', 'mengetahui')) {
                $table->dropColumn('mengetahui');
            }
            
        });
    }
};
