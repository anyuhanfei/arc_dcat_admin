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
        Schema::create('sys_article_categories', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('name', 50)->default('')->comment('分类名称');
            $table->string('image', 255)->nullable()->comment('分类图片');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->boolean('is_show')->default(1)->comment('是否显示（API端是否展示）：1显示 0隐藏');
            $table->timestamps();

            $table->comment('文章分类表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_article_categories');
    }
};
