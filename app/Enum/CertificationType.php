<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 实名认证类型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum CertificationType: string implements \JsonSerializable
{
    use HasLabel;

    case PERSONAL = 'personal'; // 个人
    case ENTERPRISE = 'enterprise'; // 企业

    /**
     * 获取认证类型标签
     */
    public function label(): string
    {
        return match ($this) {
            self::PERSONAL => '个人',
            self::ENTERPRISE => '企业',
        };
    }
}
