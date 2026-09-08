<?php

return [
    'labels' => [
        'Feedback' => '意见反馈',
        'feedback' => '意见反馈',
    ],
    'fields' => [
        'id'            => 'ID',
        'user_id'       => '提交用户',
        'title'         => '标题',
        'content'       => '反馈内容',
        'images'        => '图片',
        'contact_name'  => '联系人',
        'contact_phone' => '联系电话',
        'status'        => '状态',
        'handler_id'    => '处理人',
        'handled_at'    => '处理时间',
        'reply'         => '对用户回复',
        'replied_at'    => '回复时间',
        'admin_remark'  => '对内备注',
        'created_at'    => '提交时间',
        'updated_at'    => '更新时间',
    ],
    'enums' => [
        'status' => [0 => '待处理', 1 => '处理中', 2 => '处理完成', 3 => '搁置'],
    ],
];
