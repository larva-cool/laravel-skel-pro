<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 审核状态
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum ReviewStatus: string implements \JsonSerializable
{
    use HasLabel;

    case PENDING = 'pending'; // 待审核
    case APPROVED = 'approved'; // 已审核
    case REVIEW = 'review'; // 可疑
    case REJECTED = 'rejected'; // 已拒绝

    /**
     * 获取审核状态的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待审核',
            self::REVIEW => '需要复核',
            self::APPROVED => '已审核',
            self::REJECTED => '已拒绝',
        };
    }

    /**
     * 是否 待审核
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * 是否已审核
     */
    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    /**
     * 是否 需要人工复核
     */
    public function isReview(): bool
    {
        return $this === self::REJECTED;
    }

    /**
     * 是否已经拒绝
     */
    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}
