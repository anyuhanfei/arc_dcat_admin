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
        Schema::table('users', function (Blueprint $table) {
            // 移除自增（MODIFY 不带 auto_increment 即移除），保留主键：id 改由应用层 generateId() 生成
            $table->unsignedBigInteger('id')->comment('ID')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 恢复自增
            $table->id()->comment('ID')->change();
        });
    }
};
