<?php

return [
    'fields' => [
        'id'          => 'ID',
        'category_id' => 'Category',
        'question'    => 'Question',
        'answer'      => 'Answer',
        'is_show'     => 'Status',
        'created_at'  => 'Created At',
    ],
    'buttons' => [
        '分类管理' => 'Manage Categories',
    ],
    'enums' => [
        'is_show' => [0 => 'Hidden', 1 => 'Visible'],
    ],
    'labels' => [
        'SysFaqQuestionsController' => 'FAQ',
        'SysFaqQuestions' => 'FAQ',
        '列表' => 'list'
    ],
];
