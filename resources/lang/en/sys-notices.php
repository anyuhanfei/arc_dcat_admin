<?php

return [
    'labels' => [
        'SysNoticesController' => 'Notices',
    ],
    'fields' => [
        'id'          => 'ID',
        'title'       => 'Title',
        'image'       => 'Image',
        'content'     => 'Content',
        'status'      => 'Status',
        'created_at'  => 'Created At',
        'updated_at'  => 'Updated At',
    ],
    'enums' => [
        'status' => [0 => 'Draft', 1 => 'Published'],
    ],
    'buttons' => [
        'read_users' => 'Read Users',
    ],
];
