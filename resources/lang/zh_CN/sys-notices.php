<?php

return [
    'labels' => [
        'SysNotices' => '公告管理',
        'notices'      => '公告管理',
    ],
    'fields' => [
        'id'          => 'ID',
        'title'       => '标题',
        'image'       => '封面图',
        'content'     => '内容',
        'status'      => '状态',
        'created_at'  => '创建时间',
        'updated_at'  => '更新时间',
    ],
    'enums' => [
        'status' => [0 => '草稿', 1 => '发布'],
    ],
    'buttons' => [
        'read_users' => '已读用户',
    ],
];
