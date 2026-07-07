<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 实名认证状态
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum CertificationStatus: int implements \JsonSerializable
{
    use HasLabel;

    case UNSUBMITTED = 0; // 未提交
    case PENDING = 1; // 待审核
    case REJECTED = 2; // 认证被拒绝
    case APPROVED = 3; // 已认证

    /**
     * 获取认证状态标签
     */
    public function label(): string
    {
        return match ($this) {
            self::UNSUBMITTED => '未提交',
            self::PENDING => '待审核',
            self::REJECTED => '认证被拒绝',
            self::APPROVED => '已认证',
        };
    }

    /**
     * 获取状态 Dot 颜色
     */
    public function dot(): string
    {
        return match ($this) {
            self::UNSUBMITTED => 'info',
            self::PENDING => 'info',
            self::REJECTED => 'error',
            self::APPROVED => 'success',
        };
    }
}
