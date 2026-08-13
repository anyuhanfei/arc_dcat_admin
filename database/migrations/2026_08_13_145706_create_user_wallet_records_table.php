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
        Schema::create('user_wallet_records', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->string('currency', 20)->index()->comment('币种标识');
            $table->decimal('amount', 18, 2)->comment('流水金额（正数=入账，负数=出账）');
            $table->decimal('before_balance', 18, 2)->comment('记录前余额');
            $table->decimal('after_balance', 18, 2)->comment('记录后余额');
            $table->string('type', 50)->index()->comment('流水类型（业务key，如 recharge/consume/refund）');
            $table->string('remark', 255)->nullable()->comment('说明');
            $table->string('ref1', 255)->nullable()->comment('关联数据1（如订单号）');
            $table->string('ref2', 255)->nullable()->comment('关联数据2');
            // 流水不可变：只有 created_at，无 updated_at
            $table->timestamp('created_at')->nullable()->comment('流水时间');

            $table->comment('用户钱包流水记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wallet_records');
    }
};
