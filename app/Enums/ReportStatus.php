<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * 举报处理状态
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
enum ReportStatus: string implements \JsonSerializable
{
    use HasLabel;

    case PENDING = 'pending'; // 待处理
    case PROCESSING = 'processing'; // 处理中
    case RESOLVED = 'resolved'; // 已受理
    case REJECTED = 'rejected'; // 已驳回
    case CLOSED = 'closed'; // 已关闭

    /**
     * 获取举报处理状态的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待处理',
            self::PROCESSING => '处理中',
            self::RESOLVED => '已受理',
            self::REJECTED => '已驳回',
            self::CLOSED => '已关闭',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isResolved(): bool
    {
        return $this === self::RESOLVED;
    }
}
