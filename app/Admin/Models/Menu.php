<?php

namespace App\Admin\Models;

use Dcat\Admin\Models\Menu as BaseMenu;
use Illuminate\Support\Facades\Lang;

class Menu extends BaseMenu
{
    /**
     * 显示层翻译：菜单树/selectOptions/侧边栏展示时 title 翻译为当前语言文本.
     *
     * key 规则与 Dcat Menu::translate() 一致：menu.titles.{strtolower(str_replace(' ', '_', $title))}
     *
     * 注意：不改访问器、不改底层数据——表单编辑字段值来自 repository 独立查询（无访问器），
     * 仍显示/保存数据库原文；缓存集合内仅 attributes 被改写（original 保持原文），重复调用幂等。
     *
     * @param  bool  $force
     * @return \Illuminate\Support\Collection
     */
    public function allNodes(bool $force = false)
    {
        $nodes = parent::allNodes($force);

        return $nodes->map(function ($node) {
            $title = $node->getRawOriginal('title');
            $key = 'menu.titles.'.strtolower(str_replace(' ', '_', $title));

            $node->setAttribute('title', Lang::has($key) ? __($key) : $title);

            return $node;
        });
    }
}
