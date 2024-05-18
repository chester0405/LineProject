<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_area', function (Blueprint $table) {
            $table->id('idx');

            $table->unsignedBigInteger('_menu')->comment('觸發_id');
            $table->json('bounds')->comment('範圍');
            $table->json('action')->comment('操作');
            $table->softDeletes();

            $table->foreign('_menu')->references('idx')->on('menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_area');
    }
};
