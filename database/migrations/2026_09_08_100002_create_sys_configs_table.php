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
        Schema::create('sys_configs', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->unsignedInteger('category_id')->index()->comment('分类ID');
            $table->string('key', 100)->unique()->comment('配置键名（英文唯一标识，如 alipay_app_key）');
            $table->string('name', 100)->default('')->comment('配置项显示名称（如 支付宝AppKey）');
            $table->string('type', 20)->default('text')->comment('表单类型（对应 ConfigType enum）');
            $table->json('options')->nullable()->comment('选项数据（仅 radio/checkbox/select 用），格式 [{"key":"1","value":"选项1"}]');
            $table->string('allowed_extensions', 255)->nullable()->comment('允许的文件扩展名（仅 file 类型用），逗号分隔如 jpg,png,pdf');
            $table->longText('value')->nullable()->comment('配置值（多选类型存JSON数组，其他存字符串）');
            $table->tinyInteger('is_editable')->default(1)->comment('是否在公开编辑页展示：0否 1是');
            $table->string('description', 500)->nullable()->comment('配置项说明（编辑页字段下方灰色提示文字）');
            $table->integer('sort')->default(0)->comment('排序值，数值越小越靠前');
            $table->timestamps();

            $table->comment('系统配置项表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_configs');
    }
};
