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
        Schema::table('sys_app_versions', function (Blueprint $table) {
            // 先删旧唯一索引（依赖 platform 列）
            $table->dropUnique(['platform', 'version_code']);
            // 端字段改名（platform → app_key，语义：区分 App，如 user/staff）
            $table->renameColumn('platform', 'app_key');
            // 链接拆分：原 store_url → ios_store_url，新增 harmony_store_url
            $table->renameColumn('store_url', 'ios_store_url');
            $table->string('harmony_store_url', 255)->nullable()->after('ios_store_url');
            // 去掉排序值
            $table->dropColumn('sort');
            // 新唯一约束
            $table->unique(['app_key', 'version_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_app_versions', function (Blueprint $table) {
            $table->dropUnique(['app_key', 'version_code']);
            $table->renameColumn('app_key', 'platform');
            $table->renameColumn('ios_store_url', 'store_url');
            $table->dropColumn('harmony_store_url');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->unique(['platform', 'version_code']);
        });
    }
};
