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
        Schema::table('user_oauths', function (Blueprint $table) {
            // 持久化微信小程序 session_key，用于后续手机号解密（getPhone）
            $table->string('session_key', 64)->nullable()->comment('小程序session_key（用于手机号解密）')->after('openid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_oauths', function (Blueprint $table) {
            $table->dropColumn('session_key');
        });
    }
};
