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
        Schema::create('sys_articles', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedInteger('category_id')->index()->comment('分类ID');
            $table->string('title', 255)->default('')->comment('标题');
            $table->string('author', 50)->default('')->comment('作者');
            $table->string('image', 255)->nullable()->comment('封面图片');
            $table->string('summary', 500)->nullable()->comment('简介');
            $table->string('keywords', 255)->nullable()->comment('关键词');
            $table->longText('content')->nullable()->comment('正文（富文本内容）');
            $table->tinyInteger('status')->default(0)->comment('状态：0草稿 1发布');
            $table->timestamps();

            $table->comment('文章表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_articles');
    }
};
