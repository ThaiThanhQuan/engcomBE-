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
        Schema::create('subsribe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at');

            $table->foreign('class_id')
                  ->references('id')
                  ->on('classes')
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
        Schema::table('subsribe', function (Blueprint $table) {
            $table->dropForeign(['class_id', 'user_id']);
        });

        Schema::dropIfExists('subsribe');
    }
};
