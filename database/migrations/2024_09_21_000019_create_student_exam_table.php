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
        Schema::create('student_exam', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacherexam_id');
            $table->unsignedBigInteger('user_id');
            $table->float('score');
            $table->time('time');
            $table->time('timesuccess');
            $table->timestamps();

            $table->foreign('teacherexam_id')
                  ->references('id')
                  ->on('teacher_exam')
                  ->onDelete('restrict');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_exam', function (Blueprint $table) {
            $table->dropForeign(['teacherexam_id', 'user_id']);
        });

        Schema::dropIfExists('student_exam');
    }
};
