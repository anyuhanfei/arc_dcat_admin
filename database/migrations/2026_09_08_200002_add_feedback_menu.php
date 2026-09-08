<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menuTable = config('admin.database.menu_table');

        // 查询「用户管理」菜单作为父级
        $parent = DB::table($menuTable)->where('title', '用户管理')->first();
        $parentId = $parent ? $parent->id : 0;

        // 避免重复插入
        $exists = DB::table($menuTable)->where('uri', 'feedback')->exists();
        if ($exists) {
            return;
        }

        DB::table($menuTable)->insert([
            'parent_id'  => $parentId,
            'order'      => 0,
            'title'      => '意见反馈',
            'icon'       => 'feather icon-message-square',
            'uri'        => 'feedback',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuTable = config('admin.database.menu_table');

        DB::table($menuTable)
            ->where('title', '意见反馈')
            ->where('uri', 'feedback')
            ->delete();
    }
};
