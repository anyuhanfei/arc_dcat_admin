<?php

namespace App\Admin\Models;

use Dcat\Admin\Models\Permission as BasePermission;
use Illuminate\Support\Facades\Lang;

class Permission extends BasePermission
{
    /**
     * 显示层翻译：树/下拉/角色权限树等展示时 name 翻译为当前语言文本.
     *
     * 注意：不改访问器、不改底层数据——表单编辑字段值来自 repository 独立查询（无访问器），
     * 仍显示/保存数据库原文；仅 allNodes() 数据源（列表树/表单树/selectOptions）显示翻译。
     */
    public function allNodes()
    {
        $nodes = parent::allNodes();

        return $nodes->map(function ($node) {
            $name = $node->getRawOriginal('name');

            $node->setAttribute('name', Lang::has('permissions.'.$name) ? __('permissions.'.$name) : $name);

            return $node;
        });
    }
}
