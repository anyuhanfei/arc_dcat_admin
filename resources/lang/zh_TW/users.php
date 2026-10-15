<?php

return [
    'fields' => [
        'id'                => 'ID',
        'account'           => '登入帳號',
        'phone'             => '手機號碼',
        'email'             => '信箱',
        'wallet'            => '錢包',
        'nickname'          => '暱稱',
        'avatar'            => '大頭貼',
        'gender'            => '性別',
        'birthday'          => '生日',
        'status'            => '狀態',
        'created_at'        => '註冊時間',
        'password'          => '密碼',
        'signature'         => '個性簽名',
        'phone_verified_at' => '手機驗證時間',
        'email_verified_at' => '信箱驗證時間',
        'remember_token'    => '記住令牌',
        'updated_at'        => '更新時間',
    ],
    'enums' => [
        'gender' => [0 => '未知', 1 => '男', 2 => '女'],
        'status' => [0 => '停用', 1 => '正常'],
    ],
    'labels' => [
        'UsersController' => '用戶管理',
    ],
];
