<?php

// 上传限制配置（云存储密钥/驱动见 config/services.php 的 storage 段）

return [
    'upload' => [
        // 单文件大小上限（MB），env 可覆盖
        'max_size_mb' => env('UPLOAD_MAX_SIZE_MB', 100),
        // ffmpeg 可执行文件路径（视频截帧用），env FFMPEG_BIN 可覆盖
        'ffmpeg_bin' => env('FFMPEG_BIN', '/usr/local/bin/ffmpeg'),
        // 允许的扩展名（小写，不带点）
        'image_ext' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video_ext' => ['mp4', 'mov'],
        'doc_ext'   => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'zip'],
    ],
];
