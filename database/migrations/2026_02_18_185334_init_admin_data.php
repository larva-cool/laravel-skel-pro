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
            'username' => 'admin',
            'phone' => '18615574213',
            'email' => 'xutongle@msn.com',
            'password' => Hash::make('password'),
            'status' => StatusSwitch::ENABLED->value,
        ]);

        $admin->assignRole('Super Admin');

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        // 权限管理
        Permission::create(['name' => 'permissions.*', 'display_name' => '权限管理', 'guard_name'=>'admin']);
        Permission::create(['name' => 'permissions.create', 'display_name' => '创建权限', 'guard_name'=>'admin']);
        Permission::create(['name' => 'permissions.edit', 'display_name' => '修改权限', 'guard_name'=>'admin']);
        Permission::create(['name' => 'permissions.delete', 'display_name' => '删除权限', 'guard_name'=>'admin']);

        // 配置管理
        Permission::create(['name' => 'settings.*', 'display_name' => '配置管理', 'guard_name'=>'admin']);
        Permission::create(['name' => 'settings.create', 'display_name' => '创建配置', 'guard_name'=>'admin']);
        Permission::create(['name' => 'settings.edit', 'display_name' => '修改配置', 'guard_name'=>'admin']);
        Permission::create(['name' => 'settings.delete', 'display_name' => '删除配置', 'guard_name'=>'admin']);
        $adminRole->givePermissionTo(['settings.*','settings.create','settings.edit','settings.delete']);

        // 地区管理
        Permission::create(['name' => 'areas.*', 'display_name' => '地区管理', 'guard_name'=>'admin']);
        Permission::create(['name' => 'areas.create', 'display_name' => '创建地区', 'guard_name'=>'admin']);
        Permission::create(['name' => 'areas.edit', 'display_name' => '修改地区', 'guard_name'=>'admin']);
        Permission::create(['name' => 'areas.delete', 'display_name' => '删除地区', 'guard_name'=>'admin']);
        $adminRole->givePermissionTo(['areas.*','areas.create','areas.edit','areas.delete']);

        // 字典管理
        Permission::create(['name' => 'dicts.*', 'display_name' => '字典管理', 'guard_name'=>'admin']);
        Permission::create(['name' => 'dicts.create', 'display_name' => '创建字典', 'guard_name'=>'admin']);
        Permission::create(['name' => 'dicts.edit', 'display_name' => '修改字典', 'guard_name'=>'admin']);
        Permission::create(['name' => 'dicts.delete', 'display_name' => '删除字典', 'guard_name'=>'admin']);
        $adminRole->givePermissionTo(['dicts.*','dicts.create','dicts.edit','dicts.delete']);

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
