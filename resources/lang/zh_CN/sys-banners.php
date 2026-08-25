<?php

return [
    'labels' => [
        'SysBanners' => '轮播图管理',
        'banners' => '轮播图管理',
    ],
    'fields' => [
        'id'            => 'ID',
        'position'      => '位置',
        'image'         => '图片',
        'link_type'     => '关联类型',
        'link_value'    => '关联数据',
        'link_url'      => '外链URL',
        'link_internal' => '内链',
        'link_article'  => '文章',
        'is_show'       => '状态',
        'updated_at'    => '更新时间',
    ],
    'enums' => [
        'is_show' => [0 => '隐藏', 1 => '显示'],
    ],
];
