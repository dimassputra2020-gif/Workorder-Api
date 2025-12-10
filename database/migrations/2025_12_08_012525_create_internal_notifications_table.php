<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('internal_notifications', function (Blueprint $table) {
        $table->id();
        $table->string('npp');            
        $table->string('judul');          
        $table->text('pesan')->nullable();
        $table->string('status')->default('new'); 
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_notifications');
    }
};
