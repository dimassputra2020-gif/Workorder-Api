<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {


            $table->json('ttd_list')->nullable()->after('ttd_path');
            $table->string('ttd_path')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ttd_list');
            $table->string('ttd_path')->nullable(false)->change();
        });
    }
};
