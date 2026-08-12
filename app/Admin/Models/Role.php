<?php

namespace App\Admin\Models;

use Dcat\Admin\Models\Role as BaseRole;

class Role extends BaseRole
{
    // 角色名国际化暂不处理（Dcat 角色名无 allNodes 展示路径；如需另行处理）。
    // 保持纯继承，name 属性访问返回数据库原文。
}
