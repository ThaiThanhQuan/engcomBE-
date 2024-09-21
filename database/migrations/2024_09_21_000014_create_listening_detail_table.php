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
        Schema::create('listening_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listening_id');
            $table->text('content');
            $table->string('video')->nullable();
            $table->timestamps();

            $table->foreign('listening_id')
                  ->references('id')
                  ->on('listening')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listening_detail', function (Blueprint $table) {
            $table->dropForeign(['listening_id']);
        });

        Schema::dropIfExists('listening_detail');
    }
};
