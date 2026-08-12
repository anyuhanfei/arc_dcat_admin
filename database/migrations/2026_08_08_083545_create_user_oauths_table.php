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
        Schema::create('user_oauths', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->string('type', 30)->comment('三方类型：wechat_mini-微信小程序 wechat_mp-微信公众号 wechat_app-微信APP apple-苹果 other-其他');
            $table->string('openid', 100)->nullable()->comment('三方openid');
            $table->string('session_key', 64)->nullable()->comment('小程序session_key（用于手机号解密）');
            $table->string('unionid', 100)->nullable()->comment('三方unionid');
            $table->string('nickname', 50)->nullable()->comment('三方昵称');
            $table->string('avatar', 255)->nullable()->comment('三方头像');
            $table->json('raw')->nullable()->comment('三方原始资料');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->unique(['type', 'openid']);

            $table->comment('用户三方登录资料表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_oauths');
    }
};
