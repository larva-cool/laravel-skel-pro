<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Support\Carbon;

/**
 * 昵称
 *
 * @property int $id ID
 * @property string $nickname 昵称
 * @property Carbon|null $updated_at 更新时间
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Nickname extends Model
{
    const CREATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nicknames';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nickname',
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
            'nickname' => 'string',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 生成昵称
     */
    public static function getRandomNickname(): string
    {
        $maxId = self::max('id');
        if (! $maxId) {
            return '用户'.random_int(100000, 999999);
        }

        $randomId = random_int(1, $maxId);

        return self::query()->where('id', $randomId)->limit(1)->value('nickname') ?? '用户'.random_int(100000, 999999);
    }
}
