<?php

return [
    'labels' => [
        'SysArticleCategories' => '文章分类管理',
        'sys' => '系统管理',
        'article-categories' => '文章分类管理',
    ],
    'fields' => [
        'id'      => 'ID',
        'name'    => '分类名称',
        'image'   => '分类图片',
        'is_show' => '状态',
    ],
    'enums' => [
        'is_show' => [0 => '隐藏', 1 => '显示'],
    ],
];
