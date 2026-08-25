<?php

// 业务配置（钱包、轮播图等所有业务逻辑配置统一放本文件）
// 增删改 = 修改对应数组，代码全部动态读取

return [
    // ============ 钱包配置 ============
    'wallet' => [
        // 币种配置（标识 => 多语言名称），key 为数据库存储值
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
        // 流水类型配置（标识 => 多语言名称）
        'types' => [
            'admin' => [
                'name' => [
                    'zh_CN' => '后台操作',
                    'en'     => 'Admin Operation',
                    'zh_TW' => '後台操作',
                ],
            ],
        ],
    ],

    // ============ 轮播图配置 ============
    'banner' => [
        // 位置配置（标识 => 多语言名称），增删改 = 修改本数组
        'positions' => [
            'home' => [
                'name' => [
                    'zh_CN' => '首页轮播',
                    'en'     => 'Home Banner',
                    'zh_TW' => '首頁輪播',
                ],
            ],
        ],
        // 关联类型（可扩展：新增类型加数组项 + 后台表单为该类型配对应下拉；删除某项则表单自动不展示该项）
        'link_types' => [
            'none' => [
                'name' => [
                    'zh_CN' => '无关联',
                    'en'     => 'None',
                    'zh_TW' => '無關聯',
                ],
            ],
            'link' => [
                'name' => [
                    'zh_CN' => '外链',
                    'en'     => 'External Link',
                    'zh_TW' => '外鏈',
                ],
            ],
            'internal' => [
                'name' => [
                    'zh_CN' => '内链',
                    'en'     => 'Internal Link',
                    'zh_TW' => '內鏈',
                ],
            ],
            'article' => [
                'name' => [
                    'zh_CN' => '文章',
                    'en'     => 'Article',
                    'zh_TW' => '文章',
                ],
            ],
        ],
        // 内链标识（标识 => 名称 + 链接），用于 link_type=internal 的下拉
        'internal_links' => [
            'about' => [
                'name' => [
                    'zh_CN' => '关于我们',
                    'en'     => 'About Us',
                    'zh_TW' => '關於我們',
                ],
                'url' => '/about',
            ],
            'contact' => [
                'name' => [
                    'zh_CN' => '联系我们',
                    'en'     => 'Contact Us',
                    'zh_TW' => '聯繫我們',
                ],
                'url' => '/contact',
            ],
        ],
    ],

    // ============ App 版本配置 ============
    'app_version' => [
        // 端配置（标识 => 多语言名称），增删改 = 修改本数组
        'platforms' => [
            'android' => [
                'name' => [
                    'zh_CN' => 'Android',
                    'en'     => 'Android',
                    'zh_TW' => 'Android',
                ],
            ],
            'ios' => [
                'name' => [
                    'zh_CN' => 'iOS',
                    'en'     => 'iOS',
                    'zh_TW' => 'iOS',
                ],
            ],
            'harmony' => [
                'name' => [
                    'zh_CN' => '鸿蒙',
                    'en'     => 'HarmonyOS',
                    'zh_TW' => '鴻蒙',
                ],
            ],
        ],
    ],
];
