<?php
/**
 * 匿名聊天系统配置文件
 */

return [
    // 数据库配置
    'db' => [
        'path' => __DIR__ . '/data/chat.db',
    ],
    
    // 上传配置
    'upload' => [
        'dir' => __DIR__ . '/uploads/',
        'max_size' => 5 * 1024 * 1024,  // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
    ],
    
    // qqwry.dat IP定位库路径
    'qqwry' => [
        'path' => __DIR__ . '/qqwry.dat',
    ],
    
    // 黑名单配置
    'blacklist' => [
        'username' => ['admin', 'root', 'test'],  // 禁用的用户名
        'words' => ['敏感词1', '敏感词2'],         // 聊天禁用词
    ],
    
    // 管理员密码
    'admin_password' => 'admin123',
    
    // session配置
    'session' => [
        'timeout' => 86400,  // 24小时
    ],
];
