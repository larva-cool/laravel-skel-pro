<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Models\Admin;

use App\Models\User\LoginHistory;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

/**
 * 管理员模型
 *
 * @property int $id 管理员ID
 * @property string $username 用户名
 * @property string|null $email 邮件地址
 * @property string|null $phone 手机号
 * @property string $name 昵称
 * @property int $status 状态
 * @property string $password 密码哈希
 * @property string $remember_token 记住我 Token
 * @property int $login_count 登录次数
 * @property string|null $last_login_ip 最后登录IP地址
 * @property Carbon|null $last_active_at 最后活动时间
 * @property Carbon|null $last_login_at 最后登录时间
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 删除时间
 *
 * 关系对象
 * @property Collection<int,LoginHistory> $loginHistories 登录历史
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[Table('admin_users')]
#[Fillable(['username', 'email', 'phone', 'name', 'status', 'password', 'login_count', 'last_login_ip', 'last_login_at', 'last_active_at'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [

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
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'status' => 'integer',
            'password' => 'hashed',
            'login_count' => 'integer',
            'last_login_ip' => 'string',
            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the login histories relation.
     */
    public function loginHistories(): MorphMany
    {
        return $this->morphMany(LoginHistory::class, 'user')->latest('login_at');
    }

    /**
     * 重置用户密码
     */
    public function resetPassword(string $password): void
    {
        $this->password = $password;
        $this->setRememberToken(Str::random(60));
        $this->saveQuietly();
        Event::dispatch(new PasswordReset($this));
    }
}
