<?php

return [
    'labels' => [
        'SysConfigCategories' => '配置分类管理',
        'config-categories' => '配置分类管理',
    ],
    'fields' => [
        'id'         => 'ID',
        'name'       => '分类名称',
        'sort'       => '排序',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ],
    'messages' => [
        'delete_forbidden' => '该分类下存在配置项，禁止删除',
    ],
];
