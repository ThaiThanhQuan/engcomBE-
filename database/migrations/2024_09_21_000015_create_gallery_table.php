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
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->string('thumbnail');
            $table->tinyInteger('deleted')->default(1);
            $table->timestamp('created_at');

            $table->foreign('class_id')
                  ->references('id')
                  ->on('classes')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });

        Schema::dropIfExists('gallery');
    }
};
