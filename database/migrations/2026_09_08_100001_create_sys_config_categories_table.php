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
        Schema::create('sys_config_categories', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('name', 100)->default('')->comment('分类名称');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->timestamps();

            $table->comment('配置分类表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_config_categories');
    }
};
