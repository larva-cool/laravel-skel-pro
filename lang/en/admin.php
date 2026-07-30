<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for admin module messages.
    | Grouped by feature: auth (authentication), admin (administrator),
    | role (role management), menu (menu management).
    |
    */

    // Authentication
    'login_success' => 'Login successful.',
    'logout_success' => 'Logout successful.',
    'blocked' => 'This account has been disabled.',

    // Administrator management
    'admin' => [
        'create_success' => 'Administrator created successfully.',
        'update_success' => 'Administrator updated successfully.',
        'delete_success' => 'Administrator deleted successfully.',
        'cannot_delete_self' => 'You cannot delete yourself.',
        'cannot_delete_super' => 'Super admin cannot be deleted.',
        'cannot_disable_self' => 'You cannot disable yourself.',
        'assign_roles_success' => 'Roles assigned successfully.',
        'toggle_status_success' => 'Status toggled successfully.',
        'reset_password_success' => 'Password reset successfully.',
    ],

    // Role management
    'role' => [
        'create_success' => 'Role created successfully.',
        'update_success' => 'Role updated successfully.',
        'delete_success' => 'Role deleted successfully.',
        'assign_permissions_success' => 'Permissions assigned successfully.',
        'cannot_modify_super' => 'Super admin role cannot be modified.',
        'cannot_delete_super' => 'Super admin role cannot be deleted.',
        'in_use' => 'This role is in use and cannot be deleted.',
    ],

    // Menu management
    'menu' => [
        'create_success' => 'Menu created successfully.',
        'update_success' => 'Menu updated successfully.',
        'delete_success' => 'Menu deleted successfully.',
        'has_children' => 'Cannot delete: menu has children.',
        'invalid_parent' => 'Parent menu cannot be itself or its descendants.',
    ],
];
