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
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->string('currency', 20)->comment('币种标识（对应 config/wallet.php 的 key）');
            $table->decimal('balance', 18, 2)->default(0)->comment('当前余额');
            $table->timestamps();

            // 每用户每币种一行
            $table->unique(['user_id', 'currency']);

            $table->comment('用户钱包余额表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};
