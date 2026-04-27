<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

return [
    'route' => [
        // 前缀
        'prefix' => 'admin',
        // HTTP 方法
        'http_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD',]
    ],
    'permission' => [
        //开启权限检查
        'enable' => true,
        // 超管组名
        'administrator' => 'administrator',
        // 超级管理员ID
        'administrator_id' => 1,
        // 例外路由
        'except' => [
            '/admin/index',// 后台首页
            'admin/admins/person',  // 修改个人信息
            'admin.system-config',     // 系统配置
            'admin/auth/login',      // 登录
            'admin.logout',     // 退出登录
            'admin.menus.left_menu', // 管理菜单
            'admin.menus.permission', // 权限检查
        ],
    ]
];
