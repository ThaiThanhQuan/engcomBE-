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
        Schema::create('vocabulary_note', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notebook_id');
            $table->text('content');

            $table->foreign('notebook_id')
                  ->references('id')
                  ->on('notebook')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocabulary_note', function (Blueprint $table) {
            $table->dropForeign(['notebook_id']);
        });

        Schema::dropIfExists('vocabulary_note');
    }
};
