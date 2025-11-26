<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom baru
            if (!Schema::hasColumn('users', 'npp')) {
                $table->string('npp')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'ttd_path')) {
                $table->string('ttd_path')->nullable()->after('npp');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ttd_path')) {
                $table->dropColumn('ttd_path');
            }

            if (Schema::hasColumn('users', 'npp')) {
                $table->dropColumn('npp');
            }
        });
    }
};
