<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Content;

use App\Casts\AsJson;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 关注模型
 *
 * @property int $id ID
 * @property int $user_id 用户ID
 * @property int $source_id 资源ID
 * @property string $source_type 资源类型
 * @property Model $source 资源
 * @property AsJson $extra 扩展信息
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property User $user 用户
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class Follow extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'follows';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'source_id', 'source_type', 'extra',
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
            'source_id' => 'integer',
            'source_type' => 'string',
            'extra' => AsJson::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::created(function (Follow $model): void {
            $model->source()->increment('follow_count');
        });
        static::deleted(function (Follow $model): void {
            $model->source()->decrement('follow_count');
        });
    }

    /**
     * Get the user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 多态关联
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
