<?php

return [
    'labels' => [
        'SysArticlesController' => 'Articles',
    ],
    'fields' => [
        'id'          => 'ID',
        'category_id' => 'Category',
        'title'       => 'Title',
        'author'      => 'Author',
        'image'       => 'Image',
        'summary'     => 'Summary',
        'keywords'    => 'Keywords',
        'content'     => 'Content',
        'status'      => 'Status',
        'created_at'  => 'Created At',
        'updated_at'  => 'Updated At',
    ],
    'enums' => [
        'status' => [0 => 'Draft', 1 => 'Published'],
    ],
    'buttons' => [
        'category_manage' => 'Manage Categories',
        'category_manage_title' => 'Article Categories',
    ],
];
