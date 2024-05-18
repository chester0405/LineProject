<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_group', function (Blueprint $table) {
            $table->id('idx');

            $table->unsignedBigInteger('_group')->comment('外鍵idx');
            $table->unsignedBigInteger('_menu')->comment('外鍵idx');
            $table->softDeletes();

            $table->foreign('_group')->references('idx')->on('group');
            $table->foreign('_menu')->references('idx')->on('menu');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_group');
    }
};
