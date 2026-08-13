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
        Schema::create('sys_notice_reads', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedInteger('notice_id')->index()->comment('公告ID');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->timestamps();

            // 同一用户对同一公告仅一条已读记录
            $table->unique(['notice_id', 'user_id']);

            $table->comment('公告已读记录表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_notice_reads');
    }
};
