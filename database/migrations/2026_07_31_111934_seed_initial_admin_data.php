<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use App\Enums\AdminStatus;
use App\Enums\MenuType;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('admin_users') || ! Schema::hasTable('admin_menus')) {
            return;
        }

        // 1. 创建超级管理员角色
        $superRole = Role::findOrCreate('super_admin', AdminMenu::GUARD_NAME);

        // 2. 创建默认管理员账号
        $admin = Admin::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => '超级管理员',
                'email' => 'admin@example.com',
                'phone' => '13800000000',
                'password' => Hash::make('12345678'),
                'status' => AdminStatus::STATUS_ACTIVE,
                'login_count' => 0,
            ]
        );

        $admin->assignRole($superRole);

        // 3. 初始化菜单与权限
        $this->seedMenus();

        // 4. 将所有权限赋给超级管理员角色
        $permissions = Permission::where('guard_name', AdminMenu::GUARD_NAME)->pluck('name');
        $superRole->syncPermissions($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('admin_users')) {
            return;
        }

        // 仅删除初始化创建的管理员账号，不删除角色和菜单
        Admin::query()->where('username', 'admin')->forceDelete();
    }

    /**
     * 初始化菜单数据
     */
    private function seedMenus(): void
    {
        $menus = $this->menus();

        $idMap = [];
        foreach ($menus as $menu) {
            $children = $menu['children'] ?? [];
            $buttons = $menu['buttons'] ?? [];
            $menuKey = $menu['key'] ?? null;
            unset($menu['children'], $menu['buttons'], $menu['key']);

            $menu['parent_id'] = 0;

            $model = AdminMenu::query()->create($menu);
            if ($menuKey !== null) {
                $idMap[$menuKey] = $model->id;
            }

            foreach ($children as $child) {
                $childButtons = $child['buttons'] ?? [];
                $childKey = $child['key'] ?? null;
                unset($child['buttons'], $child['key']);

                $child['parent_id'] = $model->id;
                $childModel = AdminMenu::query()->create($child);
                if ($childKey !== null) {
                    $idMap[$childKey] = $childModel->id;
                }

                $this->createButtons($childModel->id, $childButtons);
            }
        }
    }

    /**
     * 创建按钮（权限）子项
     *
     * @param  array<int, array<string, mixed>>  $buttons
     */
    private function createButtons(int $parentId, array $buttons): void
    {
        foreach ($buttons as $i => $btn) {
            AdminMenu::query()->create(array_merge([
                'parent_id' => $parentId,
                'type' => MenuType::BUTTON,
                'sort' => $i + 1,
                'is_enable' => true,
            ], $btn));
        }
    }

    /**
     * 菜单数据
     *
     * @return array<int, array<string, mixed>>
     */
    private function menus(): array
    {
        return [
            // ========== Dashboard ==========
            [
                'key' => 'dashboard',
                'path' => '/dashboard',
                'name' => 'Dashboard',
                'component' => '/index/index',
                'title' => '仪表盘',
                'icon' => 'ri:pie-chart-line',
                'type' => MenuType::DIRECTORY,
                'sort' => 1,
                'children' => [
                    [
                        'key' => 'dashboard.console',
                        'path' => 'console',
                        'name' => 'Console',
                        'component' => '/dashboard/console',
                        'title' => '工作台',
                        'icon' => 'ri:home-smile-2-line',
                        'type' => MenuType::MENU,
                        'sort' => 1,
                        'keep_alive' => false,
                        'fixed_tab' => true,
                    ],
                ],
            ],

            // ========== 系统管理 ==========
            [
                'key' => 'system',
                'path' => '/system',
                'name' => 'System',
                'component' => '/index/index',
                'title' => '系统管理',
                'icon' => 'ri:settings-3-line',
                'type' => MenuType::DIRECTORY,
                'sort' => 90,
                'children' => [
                    [
                        'key' => 'system.admin',
                        'path' => 'admin',
                        'name' => 'AdminUser',
                        'component' => '/system/admin/index',
                        'title' => '管理员管理',
                        'icon' => 'ri:admin-line',
                        'type' => MenuType::MENU,
                        'sort' => 1,
                        'keep_alive' => true,
                        'buttons' => [
                            ['title' => '新增', 'permission' => 'admin.add'],
                            ['title' => '编辑', 'permission' => 'admin.edit'],
                            ['title' => '删除', 'permission' => 'admin.delete'],
                            ['title' => '重置密码', 'permission' => 'admin.reset-password'],
                            ['title' => '分配角色', 'permission' => 'admin.assign-role'],
                        ],
                    ],
                    [
                        'key' => 'system.role',
                        'path' => 'role',
                        'name' => 'Role',
                        'component' => '/system/role/index',
                        'title' => '角色管理',
                        'icon' => 'ri:shield-user-line',
                        'type' => MenuType::MENU,
                        'sort' => 2,
                        'keep_alive' => true,
                        'buttons' => [
                            ['title' => '新增', 'permission' => 'role.add'],
                            ['title' => '编辑', 'permission' => 'role.edit'],
                            ['title' => '删除', 'permission' => 'role.delete'],
                            ['title' => '分配权限', 'permission' => 'role.assign-permission'],
                        ],
                    ],
                    [
                        'key' => 'system.menu',
                        'path' => 'menu',
                        'name' => 'Menu',
                        'component' => '/system/menu/index',
                        'title' => '菜单管理',
                        'icon' => 'ri:menu-line',
                        'type' => MenuType::MENU,
                        'sort' => 3,
                        'keep_alive' => true,
                        'buttons' => [
                            ['title' => '新增', 'permission' => 'menu.add'],
                            ['title' => '编辑', 'permission' => 'menu.edit'],
                            ['title' => '删除', 'permission' => 'menu.delete'],
                        ],
                    ],
                ],
            ],

            // ========== 监控管理 ==========
            [
                'key' => 'monitor',
                'path' => '/monitor',
                'name' => 'Monitor',
                'component' => '/index/index',
                'title' => '系统监控',
                'icon' => 'ri:computer-line',
                'type' => MenuType::DIRECTORY,
                'sort' => 95,
                'children' => [
                    [
                        'key' => 'monitor.online-user',
                        'path' => 'online-user',
                        'name' => 'OnlineUser',
                        'component' => '/monitor/online-user/index',
                        'title' => '在线用户',
                        'icon' => 'ri:user-voice-line',
                        'type' => MenuType::MENU,
                        'sort' => 1,
                        'keep_alive' => true,
                        'buttons' => [
                            ['title' => '下线', 'permission' => 'online-user.kick'],
                        ],
                    ],
                ],
            ],

            // ========== 外部页面示例 ==========
            [
                'key' => 'outside',
                'path' => '/outside',
                'name' => 'Outside',
                'component' => '/index/index',
                'title' => '外部页面',
                'icon' => 'ri:links-line',
                'type' => MenuType::DIRECTORY,
                'sort' => 100,
                'children' => [
                    [
                        'key' => 'outside.iframe',
                        'path' => '/outside/iframe/element',
                        'name' => 'ElementUI',
                        'component' => '',
                        'title' => '内嵌文档',
                        'icon' => 'ri:apps-2-line',
                        'type' => MenuType::IFRAME,
                        'link' => 'https://element-plus.org/zh-CN/component/overview.html',
                        'is_iframe' => true,
                        'sort' => 1,
                    ],
                    [
                        'key' => 'outside.docs',
                        'path' => 'https://laravel.com/docs',
                        'name' => 'LaravelDocs',
                        'component' => '',
                        'title' => 'Laravel 文档',
                        'icon' => 'ri:book-2-line',
                        'type' => MenuType::LINK,
                        'link' => 'https://laravel.com/docs',
                        'sort' => 2,
                    ],
                ],
            ],
        ];
    }
};
