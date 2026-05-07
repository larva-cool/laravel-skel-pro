<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Enum\StatusSwitch;
use App\Models\Admin\Admin;
use App\Models\System\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::create(['guard_name' => 'admin', 'name' => 'Super Admin']);
        $adminRole = Role::create(['guard_name' => 'admin', 'name' => 'Admin']);
        /** @var Admin $admin */
        $admin = Admin::create([
            'username' => 'super-admin',
            'phone' => '13800138000',
            'email' => 'super-admin@msn.com',
            'password' => Hash::make('password'),
            'status' => StatusSwitch::ENABLED->value,
        ]);

        $admin->assignRole('Super Admin');
        $admin1 = Admin::create([
            'username' => 'admin',
            'phone' => '13700137000',
            'email' => 'admin@msn.com',
            'password' => Hash::make('password'),
            'status' => StatusSwitch::ENABLED->value,
        ]);
        $admin1->assignRole('Admin');

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'pulse', 'display_name' => 'Pulse', 'guard_name' => 'admin']);
        Permission::create(['name' => 'telescope', 'display_name' => 'Telescope', 'guard_name' => 'admin']);
        Permission::create(['name' => 'horizon', 'display_name' => 'Horizon', 'guard_name' => 'admin']);

        // 管理员管理
        Permission::create(['name' => 'admins.*', 'display_name' => '管理员管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'admins.index', 'display_name' => '管理员列表', 'guard_name' => 'admin']);
        Permission::create(['name' => 'admins.create', 'display_name' => '创建管理员', 'guard_name' => 'admin']);
        Permission::create(['name' => 'admins.edit', 'display_name' => '修改管理员', 'guard_name' => 'admin']);
        Permission::create(['name' => 'admins.delete', 'display_name' => '删除管理员', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['admins.*']);

        // 角色管理
        Permission::create(['name' => 'roles.*', 'display_name' => '角色管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'roles.index', 'display_name' => '角色列表', 'guard_name' => 'admin']);
        Permission::create(['name' => 'roles.create', 'display_name' => '创建角色', 'guard_name' => 'admin']);
        Permission::create(['name' => 'roles.edit', 'display_name' => '修改角色', 'guard_name' => 'admin']);
        Permission::create(['name' => 'roles.delete', 'display_name' => '删除角色', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['roles.*']);

        // 权限管理
        Permission::create(['name' => 'permissions.*', 'display_name' => '权限管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'permissions.index', 'display_name' => '权限列表', 'guard_name' => 'admin']);
        Permission::create(['name' => 'permissions.create', 'display_name' => '创建权限', 'guard_name' => 'admin']);
        Permission::create(['name' => 'permissions.edit', 'display_name' => '修改权限', 'guard_name' => 'admin']);
        Permission::create(['name' => 'permissions.delete', 'display_name' => '删除权限', 'guard_name' => 'admin']);

        // 菜单管理
        Permission::create(['name' => 'menus.*', 'display_name' => '菜单管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'menus.index', 'display_name' => '菜单列表', 'guard_name' => 'admin']);
        Permission::create(['name' => 'menus.create', 'display_name' => '创建菜单', 'guard_name' => 'admin']);
        Permission::create(['name' => 'menus.edit', 'display_name' => '修改菜单', 'guard_name' => 'admin']);
        Permission::create(['name' => 'menus.delete', 'display_name' => '删除菜单', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['menus.*']);

        // 配置管理
        Permission::create(['name' => 'settings.*', 'display_name' => '配置管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'settings.index', 'display_name' => '配置列表', 'guard_name' => 'admin']);
        Permission::create(['name' => 'settings.create', 'display_name' => '创建配置', 'guard_name' => 'admin']);
        Permission::create(['name' => 'settings.edit', 'display_name' => '修改配置', 'guard_name' => 'admin']);
        Permission::create(['name' => 'settings.delete', 'display_name' => '删除配置', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['settings.*']);

        // 地区管理
        Permission::create(['name' => 'areas.*', 'display_name' => '地区管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'areas.create', 'display_name' => '创建地区', 'guard_name' => 'admin']);
        Permission::create(['name' => 'areas.edit', 'display_name' => '修改地区', 'guard_name' => 'admin']);
        Permission::create(['name' => 'areas.delete', 'display_name' => '删除地区', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['areas.*']);

        // 字典管理
        Permission::create(['name' => 'dicts.*', 'display_name' => '字典管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'dicts.create', 'display_name' => '创建字典', 'guard_name' => 'admin']);
        Permission::create(['name' => 'dicts.edit', 'display_name' => '修改字典', 'guard_name' => 'admin']);
        Permission::create(['name' => 'dicts.delete', 'display_name' => '删除字典', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['dicts.*']);

        // 用户组管理
        Permission::create(['name' => 'user_groups.*', 'display_name' => '用户组管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'user_groups.create', 'display_name' => '创建用户组', 'guard_name' => 'admin']);
        Permission::create(['name' => 'user_groups.edit', 'display_name' => '修改用户组', 'guard_name' => 'admin']);
        Permission::create(['name' => 'user_groups.delete', 'display_name' => '删除用户组', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['user_groups.*']);

        // 用户管理
        Permission::create(['name' => 'users.*', 'display_name' => '用户管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'users.create', 'display_name' => '创建用户', 'guard_name' => 'admin']);
        Permission::create(['name' => 'users.edit', 'display_name' => '修改用户', 'guard_name' => 'admin']);
        Permission::create(['name' => 'users.delete', 'display_name' => '删除用户', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['users.*']);

        // 协议管理
        Permission::create(['name' => 'agreements.*', 'display_name' => '协议管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'agreements.create', 'display_name' => '创建协议', 'guard_name' => 'admin']);
        Permission::create(['name' => 'agreements.edit', 'display_name' => '修改协议', 'guard_name' => 'admin']);
        Permission::create(['name' => 'agreements.delete', 'display_name' => '删除协议', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['agreements.*']);

        // 公告管理
        Permission::create(['name' => 'announcements.*', 'display_name' => '公告管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'announcements.create', 'display_name' => '创建公告', 'guard_name' => 'admin']);
        Permission::create(['name' => 'announcements.edit', 'display_name' => '修改公告', 'guard_name' => 'admin']);
        Permission::create(['name' => 'announcements.delete', 'display_name' => '删除公告', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['announcements.*']);

        // 单页管理
        Permission::create(['name' => 'pages.*', 'display_name' => '单页管理', 'guard_name' => 'admin']);
        Permission::create(['name' => 'pages.create', 'display_name' => '创建单页', 'guard_name' => 'admin']);
        Permission::create(['name' => 'pages.edit', 'display_name' => '修改单页', 'guard_name' => 'admin']);
        Permission::create(['name' => 'pages.delete', 'display_name' => '删除单页', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(['pages.*']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
