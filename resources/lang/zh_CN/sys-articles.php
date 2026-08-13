<?php

return [
    'labels' => [
        'SysArticles' => '文章管理',
        'sys' => '系统管理',
        'articles' => '文章管理'
    ],
    'fields' => [
        'id'          => 'ID',
        'category_id' => '分类',
        'title'       => '标题',
        'author'      => '作者',
        'image'       => '封面图',
        'summary'     => '摘要',
        'keywords'    => '关键词',
        'content'     => '内容',
        'status'      => '状态',
        'created_at'  => '创建时间',
        'updated_at'  => '更新时间',
    ],
    'enums' => [
        'status' => [0 => '草稿', 1 => '发布'],
    ],
    'buttons' => [
        'category_manage' => '分类管理',
        'category_manage_title' => '文章分类管理',
    ],
];
