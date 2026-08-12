<?php

return [
    'fields' => [
        'id'          => 'ID',
        'category_id' => '分類',
        'question'    => '問題',
        'answer'      => '答案',
        'is_show'     => '狀態',
        'created_at'  => '建立時間',
    ],
    'buttons' => [
        '分类管理' => '分類管理',
    ],
    'enums' => [
        'is_show' => [0 => '隱藏', 1 => '顯示'],
    ],
    'labels' => [
        'SysFaqQuestionsController' => '常見問題管理',
    ],
];
