<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 积分交易类型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum PointType: string implements \JsonSerializable
{
    use HasLabel;

    // 交易类型常量定义
    case TYPE_SIGN_IN = 'sign_in'; // 签到获取积分
    case TYPE_INVITE_REGISTER = 'invite_register'; // 邀请注册获取积分
    case TYPE_SET_UP_AVATAR = 'modify_avatar'; // 设置头像获取积分
    case TYPE_RECOVERY = 'recovery'; // 过期回收积分

    /**
     * 获取积分交易类型的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::TYPE_SIGN_IN => '签到',
            self::TYPE_INVITE_REGISTER => '邀请注册',
            self::TYPE_SET_UP_AVATAR => '设置头像',
            self::TYPE_RECOVERY => '过期回收',
        };
    }
}
