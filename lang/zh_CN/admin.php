<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | 后台管理语言包
    |--------------------------------------------------------------------------
    |
    | 以下语言行用于后台管理模块的各种提示消息。
    |
    */

    // 认证
    'login_success' => '登录成功',
    'logout_success' => '退出成功',
    'blocked' => '该账号已被禁用',

    // 管理员管理
    'admin_create_success' => '管理员创建成功',
    'admin_update_success' => '管理员更新成功',
    'admin_delete_success' => '管理员删除成功',
    'admin_cannot_delete_self' => '不能删除自己',
    'admin_cannot_delete_super' => '超级管理员不可删除',
    'admin_cannot_disable_self' => '不能禁用自己',
    'admin_assign_roles_success' => '角色分配成功',
    'admin_toggle_status_success' => '状态切换成功',
    'admin_reset_password_success' => '密码重置成功',

    // 角色管理
    'role_create_success' => '角色创建成功',
    'role_update_success' => '角色更新成功',
    'role_delete_success' => '角色删除成功',
    'role_assign_permissions_success' => '权限分配成功',
    'role_cannot_modify_super' => '超级管理员角色不可修改',
    'role_cannot_delete_super' => '超级管理员角色不可删除',
    'role_in_use' => '该角色已被管理员使用，无法删除',

    // 菜单管理
    'menu_create_success' => '菜单创建成功',
    'menu_update_success' => '菜单更新成功',
    'menu_delete_success' => '菜单删除成功',
    'menu_has_children' => '存在子菜单，无法删除',
    'menu_invalid_parent' => '父级菜单不能是自身或其下级菜单',
];
