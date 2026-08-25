<?php

return [
    'labels' => [
        'SysBannersController' => 'Banners',
    ],
    'fields' => [
        'id'            => 'ID',
        'position'      => 'Position',
        'image'         => 'Image',
        'link_type'     => 'Link Type',
        'link_value'    => 'Link Value',
        'link_url'      => 'External URL',
        'link_internal' => 'Internal Link',
        'link_article'  => 'Article',
        'is_show'       => 'Status',
        'updated_at'    => 'Updated At',
    ],
    'enums' => [
        'is_show' => [0 => 'Hidden', 1 => 'Visible'],
    ],
];
