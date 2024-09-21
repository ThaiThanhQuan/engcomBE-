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
        Schema::create('speaking_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('speaking_id');
            $table->text('content');
            $table->string('video')->nullable();
            $table->timestamps();

            $table->foreign('speaking_id')
                  ->references('id')
                  ->on('speaking')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speaking_detail', function (Blueprint $table) {
            $table->dropForeign(['speaking_id']);
        });

        Schema::dropIfExists('speaking_detail');
    }
};
