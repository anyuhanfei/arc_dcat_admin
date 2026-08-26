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
        Schema::create('sys_app_versions', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('platform', 20)->comment('端');
            $table->string('version', 50)->default('')->comment('版本号（文本），如 1.2.3');
            $table->unsignedInteger('version_code')->comment('版本号（纯数字），用于版本比较，同端唯一');
            $table->longText('description')->nullable()->comment('版本说明（富文本内容）');
            $table->string('wgt', 255)->nullable()->comment('wgt 热更新包路径（不区分端）');
            $table->string('apk', 255)->nullable()->comment('Android 安装包路径');
            $table->string('store_url', 255)->nullable()->comment('应用商店链接（iOS=App Store，harmony=华为应用市场）');
            $table->boolean('force_update')->default(0)->comment('是否强制更新：1是 0否');
            $table->boolean('is_show')->default(1)->comment('是否发布（API端是否展示）：1显示 0隐藏');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->timestamps();

            $table->unique(['platform', 'version_code']);
            $table->comment('App 版本管理表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_app_versions');
    }
};
