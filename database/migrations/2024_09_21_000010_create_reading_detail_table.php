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
        Schema::create('reading_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reading_id');
            $table->text('content');
            $table->string('video')->nullable();
            $table->timestamps();

            $table->foreign('reading_id')
                  ->references('id')
                  ->on('reading')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_detail', function (Blueprint $table) {
            $table->dropForeign(['reading_id']);
        });

        Schema::dropIfExists('reading_detail');
    }
};
