<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * 举报原因
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
enum ReportReason: string implements \JsonSerializable
{
    use HasLabel;

    case SPAM = 'spam'; // 垃圾广告
    case HARASSMENT = 'harassment'; // 骚扰辱骂
    case PORN = 'porn'; // 色情低俗
    case ILLEGAL = 'illegal'; // 违法违规
    case INFRINGEMENT = 'infringement'; // 侵权盗用
    case OTHER = 'other'; // 其它

    /**
     * 获取举报原因的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::SPAM => '垃圾广告',
            self::HARASSMENT => '骚扰辱骂',
            self::PORN => '色情低俗',
            self::ILLEGAL => '违法违规',
            self::INFRINGEMENT => '侵权盗用',
            self::OTHER => '其它',
        };
    }
}
