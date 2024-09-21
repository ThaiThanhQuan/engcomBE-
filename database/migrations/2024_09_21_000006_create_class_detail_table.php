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
        Schema::create('class_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classes_id');
            $table->unsignedBigInteger('course_id');

            $table->foreign('classes_id')
                  ->references('id')
                  ->on('classes')
                  ->onDelete('restrict');
                  
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
        Schema::table('class_detail', function (Blueprint $table) {
            $table->dropForeign(['classes_id', 'course_id']);
        });

        Schema::dropIfExists('class_detail');
    }
};
