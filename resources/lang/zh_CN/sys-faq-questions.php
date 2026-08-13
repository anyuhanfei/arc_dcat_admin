<?php

return [
    'fields' => [
        'id'          => 'ID',
        'category_id' => '分类',
        'question'    => '问题',
        'answer'      => '答案',
        'is_show'     => '状态',
        'created_at'  => '创建时间',
    ],
    'buttons' => [
        'category_manage'       => '分类管理',
        'category_manage_title' => '常见问题分类管理',
    ],
    'enums' => [
        'is_show' => [0 => '隐藏', 1 => '显示'],
    ],
    'labels' => [
        'SysFaqQuestions' => '常见问题管理',
        'faq' => '常见问题管理',
        'sys' => '系统管理',
        'faq-questions' => '常见问题管理'
    ],
];
