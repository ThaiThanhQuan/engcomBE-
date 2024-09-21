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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacherexam_id');
            $table->unsignedBigInteger('studentexam_id');
            $table->float('score');
            $table->time('timesuccess');
            $table->timestamp('created_at');

            $table->foreign('teacherexam_id')
                  ->references('id')
                  ->on('teacher_exam')
                  ->onDelete('restrict');

            $table->foreign('studentexam_id')
                  ->references('id')
                  ->on('student_exam')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropForeign(['teacherexam_id', 'studentexam_id']);
        });
        
        Schema::dropIfExists('exam_attempts');
    }
};
