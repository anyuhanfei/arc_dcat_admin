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
        Schema::create('sys_notices', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('title', 255)->default('')->comment('标题');
            $table->string('image', 255)->nullable()->comment('封面图');
            $table->longText('content')->nullable()->comment('内容（富文本）');
            $table->tinyInteger('status')->default(0)->comment('状态：0草稿 1发布');
            $table->timestamps();

            $table->comment('公告表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_notices');
    }
};
