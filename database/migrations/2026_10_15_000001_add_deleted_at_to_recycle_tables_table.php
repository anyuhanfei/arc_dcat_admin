<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToRecycleTablesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sys_faq_questions', function (Blueprint $table) {
            $table->softDeletes()->comment('删除时间');
        });

        Schema::table('sys_faq_categories', function (Blueprint $table) {
            $table->softDeletes()->comment('删除时间');
        });

        Schema::table('sys_articles', function (Blueprint $table) {
            $table->softDeletes()->comment('删除时间');
        });

        Schema::table('sys_article_categories', function (Blueprint $table) {
            $table->softDeletes()->comment('删除时间');
        });

        Schema::table('sys_banners', function (Blueprint $table) {
            $table->softDeletes()->comment('删除时间');
        });

        Schema::table('user_feedbacks', function (Blueprint $table) {
            $table->softDeletes()->comment('删除时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_faq_questions', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('sys_faq_categories', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('sys_articles', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('sys_article_categories', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('sys_banners', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('user_feedbacks', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
