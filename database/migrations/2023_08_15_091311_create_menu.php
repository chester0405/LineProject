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
        Schema::create('menu', function (Blueprint $table) {
            $table->id('idx');

            $table->string('title')->comment('標題名稱');
            $table->string('chat_bar_text')->nullable()->comment('選單列文字');
            $table->boolean('selected')->comment('預設列是否顯示');
            $table->string('publish_status')->comment('狀態');
            $table->json('size')->nullable()->comment('圖文選單大小');
            $table->string('image')->nullable()->comment('圖片路徑');
            $table->string('alias_name')->nullable()->comment('別名 與 rich_menu_id 進行對應, 之後可移除此欄位, 改用動態產生');
            $table->string('rich_menu_id')->nullable()->comment('Line 提供的 Id');
            $table->boolean('online_status')->comment('選單是否上架');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
