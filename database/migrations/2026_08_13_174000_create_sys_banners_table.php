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
        Schema::create('sys_banners', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('position', 50)->default('home')->comment('位置标识（对应 config/business.php 的 banner.positions key）');
            $table->string('image', 255)->comment('图片');
            $table->string('link_type', 30)->default('none')->comment('关联类型（对应 config/business.php 的 banner.link_types key：none-无关联 link-外链 internal-内链 article-文章）');
            $table->string('link_value', 255)->nullable()->comment('关联数据（外链=URL，内链=内链标识，文章=文章ID）');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->boolean('is_show')->default(1)->comment('是否显示：1显示 0隐藏');
            $table->timestamps();

            $table->comment('轮播图表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_banners');
    }
};
