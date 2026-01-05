<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('npp', 50)->unique()->after('name');
            $table->string('ttd_path')->nullable();
            $table->json('ttd_list')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['npp', 'ttd_path', 'ttd_list']);
        });
    }
};
