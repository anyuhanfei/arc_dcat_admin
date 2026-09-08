<?php

return [
    'labels' => [
        'Feedback' => 'Feedback',
        'feedback' => 'Feedback',
    ],
    'fields' => [
        'id'            => 'ID',
        'user_id'       => 'User',
        'title'         => 'Title',
        'content'       => 'Content',
        'images'        => 'Images',
        'contact_name'  => 'Contact Name',
        'contact_phone' => 'Contact Phone',
        'status'        => 'Status',
        'handler_id'    => 'Handler',
        'handled_at'    => 'Handled At',
        'reply'         => 'Reply',
        'replied_at'    => 'Replied At',
        'admin_remark'  => 'Admin Remark',
        'created_at'    => 'Created At',
        'updated_at'    => 'Updated At',
    ],
    'enums' => [
        'status' => [0 => 'Pending', 1 => 'Processing', 2 => 'Completed', 3 => 'On Hold'],
    ],
];
