<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id()->from(10000000)->comment('管理员ID');
            $table->unsignedBigInteger('user_id')->unique()->nullable()->comment('用户ID');
            $table->string('username')->unique()->nullable()->comment('用户名');
            $table->string('email')->unique()->nullable()->comment('邮箱');
            $table->string('phone', 11)->unique()->nullable()->comment('手机号');
            $table->string('name')->nullable()->comment('昵称');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1、active，0、frozen');
            $table->string('socket_id')->index()->nullable()->comment('SocketId');
            $table->string('password')->nullable()->comment('密码');
            $table->rememberToken()->comment('记住我token');
            $table->ipAddress('last_login_ip')->nullable()->comment('最后登录IP地址');
            $table->unsignedBigInteger('login_count')->nullable()->default(0)->comment('登录次数');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->timestamps();
            $table->softDeletes()->comment('删除时间');

            $table->comment('管理员表');
        });
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('角色名');
            $table->string('desc')->nullable()->comment('描述');
            $table->string('slug', 50)->unique();
            $table->timestamps();

            $table->comment('管理员角色表');
        });
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_id')->index()->nullable()->comment('父权限');
            $table->string('name', 50)->comment('权限名称');
            $table->string('slug', 50)->unique()->comment('权限标识');
            $table->string('http_method')->nullable()->comment('HTTP 方法');
            $table->text('http_path')->nullable()->comment('HTTP path');
            $table->integer('order')->default(0)->comment('排序');
            $table->timestamps();

            $table->comment('管理员权限表');
        });
        Schema::create('admin_menus', function (Blueprint $table) {
            $table->id()->comment('菜单ID');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父级ID');
            $table->string('title', 255)->comment('标题');
            $table->string('icon', 255)->nullable()->comment('图标');
            $table->string('key', 255)->nullable()->comment('标识');
            $table->string('href', 255)->nullable()->comment('url');
            $table->unsignedTinyInteger('type')->comment('类型');
            $table->unsignedInteger('order')->default(0)->comment('排序');
            $table->timestamps();
            // 核心索引：树形菜单查询（全覆盖、不回表、不排序）
            $table->index(['parent_id', 'order', 'id'], 'idx_admin_menus_parent_id');

            $table->comment('管理员菜单表');
        });
        Schema::create('admin_role_permissions', function (Blueprint $table) {
            $table->bigInteger('role_id')->comment('角色ID');
            $table->bigInteger('permission_id')->comment('权限ID');
            $table->unique(['role_id', 'permission_id']);
            $table->timestamps();
            $table->comment('管理员角色权限表');
        });
        Schema::create('admin_role_menus', function (Blueprint $table) {
            $table->bigInteger('role_id')->comment('角色ID');
            $table->bigInteger('menu_id')->comment('菜单ID');
            $table->timestamps();
            $table->unique(['role_id', 'menu_id']);
            $table->comment('管理员角色菜单表');
        });
        Schema::create('admin_permission_menus', function (Blueprint $table) {
            $table->bigInteger('permission_id')->comment('权限ID');
            $table->bigInteger('menu_id')->comment('菜单ID');
            $table->timestamps();
            $table->unique(['permission_id', 'menu_id']);
            $table->index(['menu_id']);
            $table->comment('管理员权限菜单表');
        });
        Schema::create('admin_role_users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('user_id')->comment('管理员ID');
            $table->timestamps();
            $table->unique(['role_id', 'user_id']);
            $table->index(['user_id'], 'idx_admin_role_users');
            $table->comment('管理员角色用户表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('admin_role_users');
        Schema::dropIfExists('admin_permission_menus');
        Schema::dropIfExists('admin_role_menus');
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admin_menus');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('admin_users');
    }
};
