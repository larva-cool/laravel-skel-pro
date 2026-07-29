<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MenuType;
use App\Models\Admin\AdminMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 后台菜单与权限初始化填充器
 *
 * 菜单结构参考 art-design-pro：
 * - Dashboard (首页控制台)
 * - System (系统管理：用户/角色/菜单)
 * - 内嵌/外链示例
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        AdminMenu::query()->delete();

        $menus = $this->menus();

        // 扁平插入，按 parent_key→id 映射构建树形关系
        $idMap = [];
        foreach ($menus as $menu) {
            $children = $menu['children'] ?? [];
            $buttons = $menu['buttons'] ?? [];
            unset($menu['children'], $menu['buttons']);

            $menu['created_at'] = $now;
            $menu['updated_at'] = $now;
            $parentKey = $menu['parent_key'] ?? null;
            unset($menu['parent_key']);

            $menu['parent_id'] = $parentKey && isset($idMap[$parentKey]) ? $idMap[$parentKey] : 0;

            $model = AdminMenu::query()->create($menu);
            $idMap[$menu['key']] = $model->id;

            // 创建子菜单
            foreach ($children as $child) {
                $childButtons = $child['buttons'] ?? [];
                unset($child['buttons']);
                $child['parent_id'] = $model->id;
                $child['created_at'] = $now;
                $child['updated_at'] = $now;
                $childModel = AdminMenu::query()->create($child);
                $idMap[$child['key']] = $childModel->id;

                $this->createButtons($childModel->id, $childButtons, $now);
            }
        }
    }

    /**
     * 创建按钮（权限）子项
     *
     * @param  array<int, array<string, mixed>>  $buttons
     */
    protected function createButtons(int $parentId, array $buttons, Carbon $now): void
    {
        foreach ($buttons as $i => $btn) {
            AdminMenu::query()->create(array_merge([
                'parent_id' => $parentId,
                'type' => MenuType::BUTTON,
                'sort' => $i + 1,
                'is_enable' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $btn));
        }
    }

    /**
     * 菜单数据（扁平声明，按 key 映射 parent_id）
     *
     * @return array<int, array<string, mixed>>
     */
    protected function menus(): array
    {
        return [
            // ========== Dashboard ==========
            [
                'key' => 'dashboard',
                'parent_key' => null,
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
                'parent_key' => null,
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
                            ['title' => '新增', 'permission' => 'admin:add'],
                            ['title' => '编辑', 'permission' => 'admin:edit'],
                            ['title' => '删除', 'permission' => 'admin:delete'],
                            ['title' => '重置密码', 'permission' => 'admin:reset-password'],
                            ['title' => '分配角色', 'permission' => 'admin:assign-role'],
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
                            ['title' => '新增', 'permission' => 'role:add'],
                            ['title' => '编辑', 'permission' => 'role:edit'],
                            ['title' => '删除', 'permission' => 'role:delete'],
                            ['title' => '分配权限', 'permission' => 'role:assign-permission'],
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
                            ['title' => '新增', 'permission' => 'menu:add'],
                            ['title' => '编辑', 'permission' => 'menu:edit'],
                            ['title' => '删除', 'permission' => 'menu:delete'],
                        ],
                    ],
                ],
            ],

            // ========== 监控管理 ==========
            [
                'key' => 'monitor',
                'parent_key' => null,
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
                            ['title' => '下线', 'permission' => 'online-user:kick'],
                        ],
                    ],
                ],
            ],

            // ========== 外部页面示例 ==========
            [
                'key' => 'outside',
                'parent_key' => null,
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
}
