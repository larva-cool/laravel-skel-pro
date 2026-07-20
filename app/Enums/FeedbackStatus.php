<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enums;

/**
 * 反馈处理状态
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
enum FeedbackStatus: string implements \JsonSerializable
{
    use HasLabel;

    case PENDING = 'pending'; // 待处理
    case PROCESSING = 'processing'; // 处理中
    case REPLIED = 'replied'; // 已回复
    case CLOSED = 'closed'; // 已关闭

    /**
     * 获取反馈处理状态的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待处理',
            self::PROCESSING => '处理中',
            self::REPLIED => '已回复',
            self::CLOSED => '已关闭',
        };
    }

    /**
     * 是否 待处理
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * 是否 已回复
     */
    public function isReplied(): bool
    {
        return $this === self::REPLIED;
    }

    /**
     * 是否 已关闭
     */
    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }
}
