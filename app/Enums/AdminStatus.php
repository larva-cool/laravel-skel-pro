<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\HasLabel;

/**
 * 用户状态枚举
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
enum AdminStatus: int implements \JsonSerializable
{
    use HasLabel;

    // 用户状态
    case STATUS_DISABLED = 0; // 禁用
    case STATUS_ACTIVE = 1; // 正常

    /**
     * 获取用户状态标签
     */
    public function label(): string
    {
        return match ($this) {
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ACTIVE => '正常',

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
