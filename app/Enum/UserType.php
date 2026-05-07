<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 用户类型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum UserType: string implements \JsonSerializable
{
    use HasLabel;

    case USER = 'user';

    /**
     * 获取用户类型的标签
     */
    public function label(): string
    {
        return match ($this) {
            self::USER => '用户',
        };
    }
}
