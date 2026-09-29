<?php

namespace App\Admin\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 回收站统一处理控制器：恢复 / 永久删除.
 *
 * 路由：POST admin/recycled/{action}
 * 参数：
 *   - model: 模型类名（如 App\Models\User\Users）
 *   - ids:  ID 数组（批量操作时传）
 *   - all:  是否操作全部（true 时忽略 ids）
 */
class RecycledController
{
    /**
     * 统一入口：action = restore | forceDelete.
     */
    public function handle(Request $request): JsonResponse
    {
        // 从 URL 路径提取 action
        $path = $request->path();
        $action = str_contains($path, 'forceDelete') ? 'forceDelete' : 'restore';

        if (! in_array($action, ['restore', 'forceDelete'], true)) {
            return response()->json(['status' => false, 'message' => '未知操作']);
        }

        $modelClass = $request->input('model');
        $ids = (array) $request->input('ids', []);
        $all = $request->boolean('all');

        // 安全校验
        if (! $modelClass || ! class_exists($modelClass)) {
            return response()->json(['status' => false, 'message' => '模型不存在']);
        }

        $traits = class_uses_recursive($modelClass);
        if (! in_array(SoftDeletes::class, $traits, true)) {
            return response()->json(['status' => false, 'message' => '该模型未使用 SoftDeletes']);
        }

        try {
            $query = $modelClass::onlyTrashed();

            if ($all) {
                $count = (clone $query)->count();
                if ($action === 'restore') {
                    $query->restore();
                } else {
                    // 全部永久删除：分批处理避免内存溢出
                    $query->select('id')->chunkById(100, function ($chunk) use ($action) {
                        foreach ($chunk as $model) {
                            $model->forceDelete();
                        }
                    });
                }
                $affected = $count;
            } else {
                if (empty($ids)) {
                    return response()->json(['status' => false, 'message' => '请选择记录']);
                }
                // flatten 嵌套数组（Dcat.grid.selectedRows 可能返回 [[1],[2]]）
                $ids = collect($ids)->flatten()->filter()->all();
                $models = $query->whereIn('id', $ids)->get();
                $affected = 0;
                foreach ($models as $model) {
                    if ($action === 'restore') {
                        $model->restore();
                    } else {
                        $model->forceDelete();
                    }
                    $affected++;
                }
            }

            $label = $action === 'restore' ? '恢复' : '销毁';

            return response()->json([
                'status'  => true,
                'message' => "已{$label} {$affected} 条记录",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => '操作失败: '.$e->getMessage(),
            ]);
        }
    }
}
