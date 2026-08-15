<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

use App\Enums\MenuType;
use App\Models\Admin\AdminMenu;
use App\Models\System\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('admin_menus')) {
            return;
        }

        $this->seedMissingMenus();

        // 同步所有权限给超级管理员角色（如有）
        $superRole = \Spatie\Permission\Models\Role::findByName('super_admin', AdminMenu::GUARD_NAME);
        if ($superRole) {
            $permissions = Permission::where('guard_name', AdminMenu::GUARD_NAME)->pluck('name');
            $superRole->syncPermissions($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚时删除新增的菜单（通过 component 路径或权限标识识别）
        $newMenuPermissions = ['users.index'];
        $newButtonPermissions = [
            'users.edit', 'users.delete',
        ];
        $newComponents = [
            '/system/user/index',
            '/system/notification/index',
            '/ai/chat/index',
        ];

        // 删除按钮
        AdminMenu::query()
            ->where('type', MenuType::BUTTON)
            ->whereIn('permission', $newButtonPermissions)
            ->delete();

        // 删除菜单（用户管理、通知管理、AI聊天）
        $menuIds = AdminMenu::query()
            ->where(function ($q) use ($newMenuPermissions, $newComponents) {
                $q->whereIn('component', $newComponents)
                    ->orWhereIn('permission', $newMenuPermissions);
            })
            ->pluck('id');

        // 删除其子按钮（权限标识以 users. 开头的按钮）
        AdminMenu::query()
            ->whereIn('parent_id', $menuIds)
            ->delete();

        AdminMenu::query()->whereIn('id', $menuIds)->delete();

        // 删除空的 AI 目录
        $aiDir = AdminMenu::query()
            ->where('path', '/ai')
            ->where('title', 'AI 助手')
            ->first();
        if ($aiDir && ! $aiDir->children()->exists()) {
            $aiDir->delete();
        }
    }

    /**
     * 追加缺失菜单
     */
    private function seedMissingMenus(): void
    {
        // ========== 系统管理 → 用户管理 ==========
        $systemDir = AdminMenu::query()
            ->whereNull('parent_id')
            ->where('title', '系统管理')
            ->first();

        if ($systemDir) {
            // 用户管理
            if (! AdminMenu::query()->where('path', 'user')->where('parent_id', $systemDir->id)->exists()) {
                $userMenu = AdminMenu::query()->create([
                    'parent_id' => $systemDir->id,
                    'path' => 'user',
                    'name' => 'User',
                    'component' => '/system/user/index',
                    'title' => '用户管理',
                    'icon' => 'ri:user-line',
                    'type' => MenuType::MENU,
                    'sort' => 2,
                    'keep_alive' => true,
                    'is_enable' => true,
                    'is_hide' => false,
                    'is_hide_tab' => false,
                    'permission' => 'users.index',
                ]);

                $buttons = [
                    ['title' => '编辑用户', 'permission' => 'users.edit', 'sort' => 1],
                    ['title' => '删除用户', 'permission' => 'users.delete', 'sort' => 2],
                    ['title' => '冻结/启用用户', 'permission' => 'users.edit', 'sort' => 3],
                    ['title' => '重置用户密码', 'permission' => 'users.edit', 'sort' => 4],
                    ['title' => '重置联系方式', 'permission' => 'users.edit', 'sort' => 5],
                    ['title' => '调整余额', 'permission' => 'users.edit', 'sort' => 6],
                    ['title' => '延长VIP', 'permission' => 'users.edit', 'sort' => 7],
                ];

                foreach ($buttons as $btn) {
                    AdminMenu::query()->create(array_merge([
                        'parent_id' => $userMenu->id,
                        'type' => MenuType::BUTTON,
                        'is_enable' => true,
                        'is_hide' => false,
                        'is_hide_tab' => false,
                    ], $btn));
                }
            }

            // 通知管理
            if (! AdminMenu::query()->where('path', 'notification')->where('parent_id', $systemDir->id)->exists()) {
                AdminMenu::query()->create([
                    'parent_id' => $systemDir->id,
                    'path' => 'notification',
                    'name' => 'Notification',
                    'component' => '/system/notification/index',
                    'title' => '通知管理',
                    'icon' => 'ri:notification-3-line',
                    'type' => MenuType::MENU,
                    'sort' => 8,
                    'keep_alive' => true,
                    'is_enable' => true,
                    'is_hide' => false,
                    'is_hide_tab' => false,
                ]);
            }
        }

        // ========== AI 助手目录 ==========
        $aiDir = AdminMenu::query()
            ->whereNull('parent_id')
            ->where('title', 'AI 助手')
            ->first();

        if (! $aiDir) {
            $aiDir = AdminMenu::query()->create([
                'parent_id' => null,
                'path' => '/ai',
                'name' => 'AI',
                'component' => '/index/index',
                'title' => 'AI 助手',
                'icon' => 'ri:robot-line',
                'type' => MenuType::DIRECTORY,
                'sort' => 96,
                'is_enable' => true,
                'is_hide' => false,
                'is_hide_tab' => false,
            ]);
        }

        // AI 聊天
        if (! AdminMenu::query()->where('path', 'chat')->where('parent_id', $aiDir->id)->exists()) {
            AdminMenu::query()->create([
                'parent_id' => $aiDir->id,
                'path' => 'chat',
                'name' => 'AIChat',
                'component' => '/ai/chat/index',
                'title' => 'AI 聊天',
                'icon' => 'ri:chat-3-line',
                'type' => MenuType::MENU,
                'sort' => 1,
                'keep_alive' => false,
                'is_enable' => true,
                'is_hide' => false,
                'is_hide_tab' => false,
            ]);
        }
    }
};
