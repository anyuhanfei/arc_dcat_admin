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
        Schema::create('user_message_reads', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('message_id')->index()->comment('消息ID（关联 user_messages.id）');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID（关联 users.id）');
            $table->tinyInteger('is_read')->default(0)->comment('是否已读：0未读 1已读');
            $table->timestamp('read_at')->nullable()->comment('阅读时间');
            $table->timestamps();

            $table->unique(['message_id', 'user_id']);

            $table->comment('消息已读关联表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_message_reads');
    }
};
