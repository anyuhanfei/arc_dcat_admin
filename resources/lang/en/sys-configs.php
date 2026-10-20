<?php

return [
    'labels' => [
        'SysConfigs' => 'Config Items',
        'configs' => 'Config Items',
    ],
    'fields' => [
        'id'                 => 'ID',
        'category_id'        => 'Category',
        'key'                => 'Config Key',
        'name'               => 'Config Name',
        'type'               => 'Field Type',
        'options'            => 'Options',
        'allowed_extensions' => 'Allowed Extensions',
        'value'              => 'Config Value',
        'is_editable'        => 'Public Editable',
        'description'        => 'Description',
        'sort'               => 'Sort',
        'created_at'         => 'Created At',
        'updated_at'         => 'Updated At',
    ],
    'enums' => [
        'is_editable' => [0 => 'No', 1 => 'Yes'],
    ],
    'buttons' => [
        'category_manage' => 'Manage Categories',
        'fill_value'      => 'Fill Value',
        'back_to_fill'    => 'Back to Fill Config',
    ],
];
