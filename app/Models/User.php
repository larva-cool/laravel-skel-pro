<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\DateTimeFormatter;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * 用户模型
 *
 * @property int $id 用户ID
 * @property string|null $username 用户名
 * @property string|null $email 邮箱
 * @property string|null $phone 手机号
 * @property string|null $name 昵称
 * @property string|null $avatar 头像
 * @property int $status 状态
 * @property int|null $available_points 可用积分
 * @property int|null $available_coins 可用金币
 * @property string|null $password 密码
 * @property string|null $remember_token 记住我令牌
 * @property int|null $login_count 登录次数
 * @property string|null $last_login_ip 最后登录IP
 * @property Carbon|null $vip_expires_at VIP过期时间
 * @property Carbon|null $last_active_at 最后活动时间
 * @property Carbon|null $last_login_at 最后登录时间
 * @property Carbon|null $email_verified_at 邮箱验证时间
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 删除时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[Table('users')]
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use DateTimeFormatter;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'remember_token' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
