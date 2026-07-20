<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * 反馈类型
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
enum FeedbackType: string implements \JsonSerializable
{
    use HasLabel;

    case SUGGESTION = 'suggestion'; // 建议
    case BUG = 'bug'; // Bug 反馈
    case COMPLAINT = 'complaint'; // 投诉
    case OTHER = 'other'; // 其它

    /**
     * 获取反馈类型的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::SUGGESTION => '意见建议',
            self::BUG => 'Bug 反馈',
            self::COMPLAINT => '投诉',
            self::OTHER => '其它',
        };
    }
}
