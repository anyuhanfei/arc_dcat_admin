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
        Schema::create('sys_faq_questions', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedInteger('category_id')->index()->comment('分类ID');
            $table->string('question', 255)->default('')->comment('问题');
            $table->longText('answer')->nullable()->comment('答案（富文本内容，UEditor 存储）');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->boolean('is_show')->default(1)->comment('是否显示（API端是否展示）：1显示 0隐藏');
            $table->timestamps();

            $table->comment('常见问题表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_faq_questions');
    }
};
