<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Enum\SocialProvider;
use App\Models\Model;
use App\Models\Traits;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * 用户社交账号
 *
 * @property int $id ID
 * @property int|null $user_id 用户ID
 * @property SocialProvider $provider 渠道
 * @property string $openid 开放平台ID
 * @property string $unionid 开放平台UnionID
 * @property string $access_token 访问令牌
 * @property string $refresh_token 刷新令牌
 * @property Carbon $expiry_at 过期时间
 * @property string $identity_token 身份令牌
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property User $user 用户
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UserSocial extends Model
{
    use Traits\HasUser;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_socials';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'provider', 'openid', 'unionid', 'access_token', 'refresh_token', 'expiry_at', 'identity_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'provider' => SocialProvider::class,
            'openid' => 'string',
            'unionid' => 'string',
            'access_token' => 'string',
            'refresh_token' => 'string',
            'expiry_at' => 'datetime',
            'identity_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required before the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
    }
}
