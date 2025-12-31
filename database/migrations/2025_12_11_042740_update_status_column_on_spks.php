<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('id');
        });

        Schema::table('spks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('spks', function (Blueprint $table) {
            $table->string('status')->nullable();
        });

        Schema::table('spks', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });
    }
};
