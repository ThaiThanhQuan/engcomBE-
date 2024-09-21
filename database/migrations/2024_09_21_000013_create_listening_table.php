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
        Schema::create('listening', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');

            $table->foreign('course_id')
                  ->references('id')
                  ->on('course')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listening', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });

        Schema::dropIfExists('listening');
    }
};
