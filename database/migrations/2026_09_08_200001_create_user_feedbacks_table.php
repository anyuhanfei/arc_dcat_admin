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
        Schema::create('user_feedbacks', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('user_id')->index()->comment('提交用户ID（关联 users.id）');
            $table->string('title', 200)->default('')->comment('标题');
            $table->text('content')->comment('反馈内容');
            $table->json('images')->nullable()->comment('图片URL数组（最多9张，格式 ["url1","url2"]）');
            $table->string('contact_name', 50)->default('')->comment('联系人姓名');
            $table->string('contact_phone', 20)->default('')->comment('联系电话');
            $table->tinyInteger('status')->default(0)->comment('状态：0待处理 1处理中 2处理完成 3搁置');
            $table->unsignedBigInteger('handler_id')->nullable()->comment('处理人ID（关联 admin_users.id）');
            $table->timestamp('handled_at')->nullable()->comment('处理时间（结案时间）');
            $table->text('reply')->nullable()->comment('对用户回复');
            $table->timestamp('replied_at')->nullable()->comment('回复时间');
            $table->text('admin_remark')->nullable()->comment('对内备注');
            $table->timestamps();

            $table->comment('意见反馈表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedbacks');
    }
};
