<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 用户组
 *
 * @property int $id 用户组ID
 * @property string $name 用户组名称
 * @property string $desc 用户组描述
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Collection<int,User> $users 用户
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'desc',
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
            'name' => 'string',
            'desc' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 用户关系定义
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
