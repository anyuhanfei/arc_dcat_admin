<?php

return [
    'labels' => [
        'SysConfigs' => '配置项管理',
        'configs' => '配置项管理',
    ],
    'fields' => [
        'id'                 => 'ID',
        'category_id'        => '所属分类',
        'key'                => '配置键名',
        'name'               => '配置名称',
        'type'               => '表单类型',
        'options'            => '选项数据',
        'allowed_extensions' => '允许扩展名',
        'value'              => '配置值',
        'is_editable'        => '公开编辑',
        'description'        => '配置说明',
        'sort'               => '排序',
        'created_at'         => '创建时间',
        'updated_at'         => '更新时间',
    ],
    'enums' => [
        'is_editable' => [0 => '否', 1 => '是'],
    ],
    'buttons' => [
        'category_manage' => '配置分类管理',
        'fill_value'      => '填写值',
    ],
];
