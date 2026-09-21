<?php

return [
    'labels' => [
        'Message' => '系统消息',
        'message' => '系统消息',
    ],
    'fields' => [
        'id'              => 'ID',
        'type'            => '消息类型',
        'title'           => '标题',
        'image'           => '封面图',
        'content'         => '消息内容',
        'target_type'     => '目标用户类型',
        'status'          => '状态',
        'is_read'         => '是否已读',
        'read_at'         => '已读时间',
        'is_system_generated' => '是否系统生成',
        'relation_type'   => '关联类型',
        'relation_id'     => '关联ID',
        'relation_user_id' => '关联用户ID',
        'relation_info'    => '关联信息',
        'created_at'      => '创建时间',
        'updated_at'      => '更新时间',
    ],
    'enums' => [
        'type' => [
            1 => '系统',
            2 => '订单',
        ],
        'status' => [
            0 => '草稿',
            1 => '已发送',
            2 => '已撤回',
        ],
        'target_type' => [
            0 => '自定义选择',
            1 => '全部用户',
        ],
    ],
    'buttons' => [
        'recall' => '撤回',
    ],
    'messages' => [
        'not_found'      => '消息不存在或无权查看',
        'read_all_success' => '全部标记已读成功',
    ],
];
