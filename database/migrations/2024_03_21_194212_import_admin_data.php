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
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'id' => 10000,
                        'title' => '控制台',
                        'icon' => 'layui-icon-console',
                        'href' => '/admin/dashboard',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'id' => 10001,
                        'title' => '个人设置',
                        'icon' => 'layui-icon-set',
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
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '系统设置',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/system-config',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '管理员管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/admins',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '权限管理',
                        'icon' => 'layui-icon layui-icon-user',
                        'href' => '/admin/permissions',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '角色管理',
                        'icon' => 'layui-icon layui-icon-user',
                        'href' => '/admin/roles',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '菜单管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/menus',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '设置项管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/settings',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '字典管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/dicts',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '地区管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/areas',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '附件管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/attachments',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '用户协议管理',
                        'icon' => 'layui-icon layui-icon-set',
                        'href' => '/admin/agreements',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '单页管理',
                        'icon' => 'layui-icon layui-icon-username',
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
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '用户组管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/user_groups',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '用户管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/users',
                        'type' => 1,
                        'order' => 1000,
                    ],
                ],
            ],
            // 内容管理
            [
                'id' => 4,
                'parent_id' => null,
                'title' => '内容管理',
                'icon' => 'layui-icon-content',
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '反馈管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/feedback',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '公告管理',
                        'icon' => 'layui-icon layui-icon-username',
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
                'href' => '',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'title' => '积分记录',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/integral',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '金币记录',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/coins',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '任务管理',
                        'icon' => 'layui-icon layui-icon-username',
                        'href' => '/admin/task_groups',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'title' => '举报管理',
                        'icon' => 'layui-icon layui-icon-username',
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
                'href' => '/admin/component/code/index.html',
                'type' => 0,
                'order' => 1000,
                'children' => [
                    [
                        'parent_id' => 99,
                        'title' => '代码生成',
                        'icon' => 'layui-icon layui-icon-util',
                        'href' => '/admin/component/code/index.html',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'parent_id' => 99,
                        'title' => 'Pulse',
                        'icon' => 'layui-icon layui-icon-util',
                        'href' => '/admin/pulse',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'parent_id' => 99,
                        'title' => 'Telescope',
                        'icon' => 'layui-icon layui-icon-util',
                        'href' => '/admin/telescope',
                        'type' => 1,
                        'order' => 1000,
                    ],
                    [
                        'parent_id' => 99,
                        'title' => 'Horizon',
                        'icon' => 'layui-icon layui-icon-util',
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
