<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Models\Admin\AdminMenu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menus = [
            // 工作空间
            [
                'id' => 1,
                'parent_id' => null,
                'title' => '工作空间',
                'icon' => 'layui-icon-console',
                'key' => 'admin.workspace',
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'id' => 10000,
                        'title' => '控制台',
                        'icon' => 'layui-icon-console',
                        'key' => 'admin.index.dashboard',
                        'href' => '/admin/dashboard',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'id' => 10001,
                        'title' => '个人设置',
                        'icon' => 'layui-icon-set',
                        'key' => 'admin.admins.person',
                        'href' => '/admin/admins/person',
                        'type' => 1,
                        'order' => 1000,
                    ],
                ],
            ],
            // 系统设置
            [
                'id' => 2,
                'parent_id' => null,
                'title' => '系统设置',
                'icon' => 'layui-icon-set',
                'key' => 'admin.config',
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '系统设置',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.system.config',
                        'href' => '/admin/system-config',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '管理员管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.admins',
                        'href' => '/admin/admins',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.admins', '/admin/admins'),
                    ],
                    [
                        'title' => '权限管理',
                        'icon' => 'layui-icon layui-icon-user',
                        'key' => 'admin.permissions',
                        'href' => '/admin/permissions',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.permissions', '/admin/permissions'),
                    ],
                    [
                        'title' => '角色管理',
                        'icon' => 'layui-icon layui-icon-user',
                        'key' => 'admin.roles',
                        'href' => '/admin/roles',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.roles', '/admin/roles'),
                    ],
                    [
                        'title' => '菜单管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.menus',
                        'href' => '/admin/menus',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.menus', '/admin/menus'),
                    ],
                    [
                        'title' => '设置项管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.settings',
                        'href' => '/admin/settings',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.settings', '/admin/settings'),
                    ],
                    [
                        'title' => '字典管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.dicts',
                        'href' => '/admin/dicts',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.dicts', '/admin/dicts'),
                    ],
                    [
                        'title' => '地区管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.areas',
                        'href' => '/admin/areas',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.areas', '/admin/areas'),
                    ],
                    [
                        'title' => '附件管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.attachments',
                        'href' => '/admin/attachments',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '用户协议管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'key' => 'admin.agreements',
                        'href' => '/admin/agreements',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.agreements', '/admin/agreements'),
                    ],
                    [
                        'title' => '单页管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.pages',
                        'href' => '/admin/pages',
                        'type' => 1,
                        'order' => 1000,
                    ],
                ],
            ],
            // 用户管理
            [
                'id' => 3,
                'parent_id' => null,
                'title' => '用户管理',
                'icon' => 'layui-icon-user',
                'key' => 'admin.user',
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '用户组管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.user_groups',
                        'href' => '/admin/user_groups',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.user_groups', '/admin/user_groups'),
                    ],
                    [
                        'title' => '用户管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.users',
                        'href' => '/admin/users',
                        'type' => 1,
                        'order' => 1000,
                        'children' => AdminMenu::makeSubMenu('admin.users', '/admin/users'),
                    ],
                ],
            ],
            // 内容管理
            [
                'id' => 4,
                'parent_id' => null,
                'title' => '内容管理',
                'icon' => 'layui-icon-content',
                'key' => 'admin.content',
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '反馈管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.feedback',
                        'href' => '/admin/feedback',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '公告管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.announcements',
                        'href' => '/admin/announcements',
                        'type' => 1,
                        'order' => 1000,
                    ],
                ],
            ],
            [
                'id' => 5,
                'parent_id' => null,
                'title' => '运营管理',
                'icon' => 'layui-icon-operation',
                'key' => 'admin.operation',
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '积分记录',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.integral',
                        'href' => '/admin/integral',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '金币记录',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.coins',
                        'href' => '/admin/coins',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '任务管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.task_groups',
                        'href' => '/admin/task_groups',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '举报管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'key' => 'admin.reports',
                        'href' => '/admin/reports',
                        'type' => 1,
                        'order' => 1000,
                    ],
                ],
            ],
            // 开发工具
            [
                'id' => 99,
                'parent_id' => null,
                'title' => '开发工具',
                'icon' => 'layui-icon-util',
                'key' => 'admin.component',
                'href' => '/admin/component/code/index.html',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'parent_id' => 99,
                        'title' => '代码生成',
                        'icon' => 'layui-icon layui-icon-util',
                        'key' => 'admin.component.code',
                        'href' => '/admin/component/code/index.html',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'parent_id' => 99,
                        'title' => 'Pulse',
                        'icon' => 'layui-icon layui-icon-util',
                        'key' => 'laravel.pulse',
                        'href' => '/admin/pulse',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'parent_id' => 99,
                        'title' => 'Telescope',
                        'icon' => 'layui-icon layui-icon-util',
                        'key' => 'laravel.telescope',
                        'href' => '/admin/telescope',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'parent_id' => 99,
                        'title' => 'Horizon',
                        'icon' => 'layui-icon layui-icon-util',
                        'key' => 'laravel.horizon',
                        'href' => '/admin/horizon',
                        'type' => 1,
                        'order' => 1000,
                    ],
                ],
            ],
        ];
        AdminMenu::batchAdd($menus);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        AdminMenu::truncate();
    }
};
