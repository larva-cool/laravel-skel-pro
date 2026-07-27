<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */
namespace App\Enums;

/**
 * 任务类型
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum TaskType: string implements \JsonSerializable
{
    use HasLabel;

    // 任务类型
    case TYPE_SIGN_IN = 'sign_in'; // 签到
    case TYPE_VIDEO_AD = 'video_ad'; // 视频广告
    case TYPE_INCENTIVE_VIDEO = 'incentive_video'; // 激励视频
    case TYPE_INVITE_USER = 'invite_register'; // 邀请用户
    case TYPE_WECHAT_SUBSCRIBER = 'wechat_subscriber'; // 微信关注

    /**
     * 获取任务类型的可读名称
     */
    public function label(): string
    {
        return match ($this) {
            self::TYPE_SIGN_IN => '签到',
            self::TYPE_VIDEO_AD => '视频广告',
            self::TYPE_INCENTIVE_VIDEO => '激励视频',
            self::TYPE_INVITE_USER => '邀请用户',
            self::TYPE_WECHAT_SUBSCRIBER => '微信关注',
        };
    }
}
