<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 状态切换枚举
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum StatusSwitch: int implements \JsonSerializable
{
    use HasLabel;

    case DISABLED = 0; // 停用
    case ENABLED = 1;  // 可用

    /**
     * 获取状态切换的标签
     */
    public function label(): string
    {
        return match ($this) {
            self::DISABLED => '停用',
            self::ENABLED => '可用',
        };
    }

    /**
     * 判断是否为开启状态
     */
    public function isEnabled(): bool
    {
        return $this === self::ENABLED;
    }

    /**
     * 切换状态
     */
    public function toggle(): self
    {
        return $this === self::ENABLED ? self::DISABLED : self::ENABLED;
    }
}
