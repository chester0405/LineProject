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
        Schema::create('group', function (Blueprint $table) {
            $table->id('idx');

            $table->string('title')->comment('群組標題名稱');
            $table->string('is_default')->nullable()->comment('預設群組');
            $table->boolean('schedule_status')->comment('狀態 1:已排程 0:未排程');
            $table->boolean('record_status')->nullable()->comment('紀錄正在上架中的狀態');
            $table->dateTime('release_at')->nullable()->comment('開始時間');
            $table->dateTime('removal_at')->nullable()->comment('結束時間');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group');
    }
};
