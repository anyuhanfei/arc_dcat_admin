<?php

return [
    'fields' => [
        'id'                => 'ID',
        'account'           => '登录账号',
        'phone'             => '手机号',
        'email'             => '邮箱',
        'wallet'            => '钱包',
        'nickname'          => '昵称',
        'avatar'            => '头像',
        'gender'            => '性别',
        'birthday'          => '生日',
        'status'            => '状态',
        'created_at'        => '注册时间',
        'password'          => '密码',
        'signature'         => '个性签名',
        'phone_verified_at' => '手机验证时间',
        'email_verified_at' => '邮箱验证时间',
        'remember_token'    => '记住令牌',
        'updated_at'        => '更新时间',
    ],
    'enums' => [
        'gender' => [0 => '未知', 1 => '男', 2 => '女'],
        'status' => [0 => '禁用', 1 => '正常'],
    ],
    'labels' => [
        'Users' => '用户管理',
        'users'=> '用户管理',
        'user'=> '用户管理',
    ],
    'tabs' => [
        'user_info' => '用户信息',
        'wallet'    => '钱包',
    ],
];
