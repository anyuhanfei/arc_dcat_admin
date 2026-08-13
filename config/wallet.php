<?php

// 钱包币种配置
// 币种增删改 = 修改本文件 currencies 数组：
//   key 为币种标识（数据库存储值），name 为多语言名称（zh_CN/en/zh_TW，与后台语言体系一致）
// 注意：删除币种后，历史钱包余额与流水记录保留（不再可操作该币种）
return [
    'currencies' => [
        'MONEY' => [
            'name' => [
                'zh_CN' => '余额',
                'en'     => 'money',
                'zh_TW' => '余额',
            ],
        ],
        'POINT' => [
            'name' => [
                'zh_CN' => '积分',
                'en'     => 'Points',
                'zh_TW' => '積分',
            ],
        ],
    ],

    // 流水类型配置（标识 => 多语言名称），增删改 = 修改本数组
    'types' => [
        'admin' => [
            'name' => [
                'zh_CN' => '后台操作',
                'en'     => 'Admin Operation',
                'zh_TW' => '後台操作',
            ],
        ],
    ],
];
