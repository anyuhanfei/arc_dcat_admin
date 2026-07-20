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
        Schema::create('sys_agreements', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->string('title', 255)->default('')->comment('标题');
            $table->longText('content')->nullable()->comment('内容');
            $table->timestamps();
            $table->softDeletes();

            $table->comment('协议配置管理');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_agreements');
    }
};
