<?php
return [
    'labels' => [
        'SysAppVersions' => 'APP版本管理',
        'sys-app-versions' => 'APP版本管理',
        'app-versions' => 'APP版本管理',
    ],
    'fields' => [
        'id' => 'ID',
        'app_key' => '端',
        'version' => '版本号（文本）',
        'version_code' => '版本号（数字）',
        'description' => '版本说明',
        'wgt' => 'wgt增量包',
        'apk' => '安卓安装包',
        'ios_store_url' => 'App Store链接',
        'harmony_store_url' => '华为市场链接',
        'force_update' => '是否强制更新',
        'is_show' => '状态',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ],
    'enums' => [
        'force_update' => [0 => '否', 1 => '是'],
        'is_show' => [0 => '隐藏', 1 => '显示'],
    ],
];
