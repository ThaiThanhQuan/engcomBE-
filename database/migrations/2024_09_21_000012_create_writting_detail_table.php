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
        Schema::create('writting_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('writting_id');
            $table->text('content');
            $table->string('video')->nullable();
            $table->timestamps();

            $table->foreign('writting_id')
                  ->references('id')
                  ->on('writting')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('writting_detail', function (Blueprint $table) {
            $table->dropForeign(['writting_id']);
        });

        Schema::dropIfExists('writting_detail');
    }
};
