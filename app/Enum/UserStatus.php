<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 用户状态枚举
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
enum UserStatus: int implements \JsonSerializable
{
    use HasLabel;

    // 用户状态
    case STATUS_ACTIVE = 0; // 正常
    case STATUS_FROZEN = 1; // 已冻结
    case STATUS_NOT_ACTIVE = 2; // 未激活

    /**
     * 获取用户状态标签
     */
    public function label(): string
    {
        return match ($this) {
            self::STATUS_ACTIVE => '正常',
            self::STATUS_FROZEN => '已冻结',
            self::STATUS_NOT_ACTIVE => '未激活',
        };
    }

    /**
     * 是否为正常状态
     */
    public function isActive(): bool
    {
        return $this === self::STATUS_ACTIVE;
    }

    /**
     * 是否为已冻结状态
     */
    public function isFrozen(): bool
    {
        return $this === self::STATUS_FROZEN;
    }
}
