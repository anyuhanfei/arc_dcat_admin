<?php

return [
    'labels' => [
        'SysConfigCategories' => 'Config Categories',
        'config-categories' => 'Config Categories',
    ],
    'fields' => [
        'id'         => 'ID',
        'name'       => 'Category Name',
        'sort'       => 'Sort',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'messages' => [
        'delete_forbidden' => 'This category contains config items and cannot be deleted',
    ],
];
