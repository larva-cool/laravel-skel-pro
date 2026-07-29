<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * 用户助手类
 * 提供用户相关的静态方法，如通过账号查找用户
 */
class UserHelper
{
    /**
     * 通过账号查找用户
     */
    public static function findForAccount(string $account): ?User
    {
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return User::query()->whereNotNull('email')->where('email', $account)->first();
        } elseif (preg_match('/^1[2-9]\d{9}$/', $account)) {
            return User::query()->whereNotNull('phone')->where('phone', $account)->first();
        } else {
            return User::query()->whereNotNull('username')->where('username', $account)->first();
        }
    }
}
