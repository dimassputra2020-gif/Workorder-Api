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
        $table->string('no_referensi')->nullable()->index()->after('no_surat');
    });
}

public function down(): void
{
    Schema::table('spks', function (Blueprint $table) {
        $table->dropColumn('no_referensi');
    });
}

};
