<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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
        // 尝试从缓存中获取昵称列表
        $nicknameCount = Cache::get('nickname_count');
        if (! $nicknameCount) {
            $nicknameCount = self::query()->count();
            Cache::put('nickname_count', $nicknameCount, 60);
        }
        if ($nicknameCount > 1000) {
            $nickname = self::query()->inRandomOrder()->value('nickname');

            return $nickname ?? '';
        }
        $id = rand(1, $nicknameCount);
        $nickname = self::query()->where('id', $id)->value('nickname');

        return $nickname ?? '';
    }
}
