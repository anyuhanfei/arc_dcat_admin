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
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary()->comment('ID'); // 非自增主键：id 由应用层 User::generateId() 生成
            $table->string('account')->unique()->comment('登录账号');
            $table->string('phone', 20)->nullable()->unique()->comment('手机号');
            $table->string('email')->nullable()->unique()->comment('邮箱');
            $table->string('password')->nullable()->comment('密码');
            $table->string('nickname', 50)->comment('昵称');
            $table->string('avatar', 255)->nullable()->comment('头像');
            $table->string('signature', 255)->nullable()->comment('个性签名');
            $table->tinyInteger('gender')->default(0)->comment('性别：0未知 1男 2女');
            $table->date('birthday')->nullable()->comment('生日');
            $table->tinyInteger('status')->default(1)->comment('状态：1正常 0禁用');
            $table->timestamp('phone_verified_at')->nullable()->comment('手机号验证时间');
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱验证时间');
            $table->rememberToken()->comment('记住登录令牌');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');
            $table->softDeletes()->comment('删除时间');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
