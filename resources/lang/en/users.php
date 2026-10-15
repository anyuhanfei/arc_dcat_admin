<?php

return [
    'fields' => [
        'id'                => 'ID',
        'account'           => 'Account',
        'phone'             => 'Phone',
        'email'             => 'Email',
        'wallet'            => 'Wallet',
        'nickname'          => 'Nickname',
        'avatar'            => 'Avatar',
        'gender'            => 'Gender',
        'birthday'          => 'Birthday',
        'status'            => 'Status',
        'created_at'        => 'Registered At',
        'password'          => 'Password',
        'signature'         => 'Signature',
        'phone_verified_at' => 'Phone Verified At',
        'email_verified_at' => 'Email Verified At',
        'remember_token'    => 'Remember Token',
        'updated_at'        => 'Updated At',
    ],
    'enums' => [
        'gender' => [0 => 'Unknown', 1 => 'Male', 2 => 'Female'],
        'status' => [0 => 'Disabled', 1 => 'Active'],
    ],
    'labels' => [
        'UsersController' => 'Users',
    ],
    'tabs' => [
        'user_info' => 'User Info',
        'wallet'    => 'Wallet',
    ],
];
