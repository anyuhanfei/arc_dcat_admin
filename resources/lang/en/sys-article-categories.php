<?php

return [
    'labels' => [
        'SysArticleCategoriesController' => 'Article Categories',
    ],
    'fields' => [
        'id'      => 'ID',
        'name'    => 'Name',
        'image'   => 'Image',
        'is_show' => 'Status',
    ],
    'enums' => [
        'is_show' => [0 => 'Hidden', 1 => 'Visible'],
    ],
];
