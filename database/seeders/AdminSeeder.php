<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * 超级管理员与角色初始化填充器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 创建超级管理员角色（admin guard）
        $superRole = Role::findOrCreate('super_admin', AdminMenu::GUARD_NAME);
        $superRole->update(['name' => 'super_admin', 'display_name' => '超级管理员']);

        // 将所有权限赋给超级管理员角色
        $permissions = \Spatie\Permission\Models\Permission::where('guard_name', AdminMenu::GUARD_NAME)->pluck('name');
        $superRole->syncPermissions($permissions);

        // 创建默认管理员账号
        $admin = Admin::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => '超级管理员',
                'email' => 'admin@example.com',
                'phone' => '13800000000',
                'password' => Hash::make('123456'),
                'status' => 1,
                'login_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $admin->assignRole($superRole);
    }
}
