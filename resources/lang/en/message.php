<?php

return [
    'labels' => [
        'Message' => 'Message',
        'message' => 'Message',
    ],
    'fields' => [
        'id'              => 'ID',
        'type'            => 'Message Type',
        'title'           => 'Title',
        'image'           => 'Cover Image',
        'content'         => 'Content',
        'target_type'     => 'Target Type',
        'status'          => 'Status',
        'is_read'         => 'Is Read',
        'read_at'         => 'Read At',
        'is_system_generated' => 'System Generated',
        'relation_type'   => 'Relation Type',
        'relation_id'     => 'Relation ID',
        'relation_user_id' => 'Relation User ID',
        'relation_info'    => 'Relation Info',
        'created_at'      => 'Created At',
        'updated_at'      => 'Updated At',
    ],
    'enums' => [
        'type' => [
            1 => 'System',
            2 => 'Order',
        ],
        'status' => [
            0 => 'Draft',
            1 => 'Sent',
            2 => 'Recalled',
        ],
        'target_type' => [
            0 => 'Custom',
            1 => 'All Users',
        ],
    ],
    'buttons' => [
        'recall' => 'Recall',
    ],
    'messages' => [
        'not_found'      => 'Message not found or no permission',
        'read_all_success' => 'All marked as read successfully',
    ],
];
