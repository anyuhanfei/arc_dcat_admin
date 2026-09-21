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
        Schema::create('user_messages', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->tinyInteger('type')->comment('消息类型：1系统 2订单');
            $table->string('title', 200)->comment('消息标题');
            $table->string('image', 500)->nullable()->comment('消息图片（单张）');
            $table->text('content')->comment('消息内容');
            $table->tinyInteger('target_type')->comment('目标用户类型：0自定义选择 1全部用户');
            $table->tinyInteger('status')->default(0)->comment('状态：0草稿 1已发送 2已撤回');
            $table->tinyInteger('is_system_generated')->default(0)->comment('是否系统自动生成：0否 1是');
            $table->string('relation_type', 50)->nullable()->comment('关联类型（如 order、feedback）');
            $table->unsignedBigInteger('relation_id')->nullable()->comment('关联ID');
            $table->unsignedBigInteger('relation_user_id')->nullable()->comment('关联信息所属用户ID');
            $table->timestamps();

            $table->index('status');
            $table->index('target_type');
            $table->index('type');

            $table->comment('系统消息表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_messages');
    }
};
