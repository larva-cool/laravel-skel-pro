<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

return [
    'route' => [
        'prefix' => 'admin',
        'http_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD',]
    ],
    'permission' => [
        //开启权限检查
        'enable' => true,
        // 超级管理员ID
        'administrator_id' => 1,
        // 例外
        'except' => [
            'admin.dashboard',  // 后台首页
            'admin.admins.person',  // 管理员个人中心
            'admin.system-config',     // 系统配置
            'admin.login',      // 登录入口
            'admin.login.auth', // 登录认证
            'admin.logout',     // 退出登录
            'admin.index',      // 后台入口
            'admin.account',    // 管理员资料
            'admin.menus.left_menu', // 管理菜单
            'admin.menus.permission', // 权限检查
            'admin.areas.select',// 地区选择
            // 'admin.settings.*', // 匹配所有设置相关路由
        ],
    ]
];
