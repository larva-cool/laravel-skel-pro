<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Announcement;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 公告已读
 *
 * @property int $id ID
 * @property int $announcement_id 公告ID
 * @property int $user_id 用户ID
 * @property string $user_type 用户类型
 * @property Carbon $created_at 创建时间
 * @property Announcement $announcement 公告
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AnnouncementRead extends Model
{
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'announcement_reads';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'announcement_id', 'user_id', 'user_type',
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
            'announcement_id' => 'integer',
            'user_id' => 'integer',
            'user_type' => 'string',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::created(function ($model) {
            $model->announcement->increment('read_count');
        });
    }

    /**
     * 公告
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * 用户
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
