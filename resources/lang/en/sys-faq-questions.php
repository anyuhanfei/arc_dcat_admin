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
        'category_manage'       => 'Manage Categories',
        'category_manage_title' => 'FAQ Categories',
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
