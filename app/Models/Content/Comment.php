<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Content;

use App\Casts\AsJson;
use App\Enum\ReviewStatus;
use App\Models\Model;
use App\Models\Traits;
use App\Models\User;
use App\Notifications\Content\MentionedInComment;
use App\Policies\CommentPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 评论模型
 *
 * @property int $id 评论ID
 * @property int $user_id 用户ID
 * @property int $source_id 源ID
 * @property string $source_type 源类型
 * @property bool $is_top 是否置顶
 * @property int $like_count 点赞次数
 * @property int $comment_count 评论回复次数
 * @property ReviewStatus $status 评论状态
 * @property array $mentioned_users 艾特 / 提及
 * @property string $content 评论内容
 * @property string $ip_address 评论IP
 * @property Carbon $created_at 评论时间
 * @property Model $source 源模型
 * @property User $user 用户模型
 * @property Comment[] $replies 回复模型
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[UsePolicy(CommentPolicy::class)]
class Comment extends Model
{
    use HasFactory;
    use Traits\HasLikeable;
    use Traits\HasUser;
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'source_id', 'source_type', 'is_top', 'like_count', 'comment_count', 'status', 'content',
        'ip_address', 'mentioned_users',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_id', 'mentioned_users',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => ReviewStatus::PENDING->value,
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
            'is_top' => 'boolean',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'status' => ReviewStatus::class,
            'content' => 'string',
            'mentioned_users' => AsJson::class,
            'ip_address' => 'string',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::deleted(function (Comment $model): void {
            if ($model->status->isApproved()) {
                $model->source()->decrement('comment_count');
            }
        });
    }

    /**
     * 评论回复
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'source');
    }

    /**
     * 获取跟Root
     */
    public function locateRootOfCommentChain(): Model
    {
        $source = $this->source;
        $isRootSource = false;
        do {
            if (! $source instanceof Comment) {
                $isRootSource = true;
            } else {
                $source = $source->source;
            }
        } while (! $isRootSource);

        return $source;
    }

    /**
     * 多态关联
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 标记 已审核
     */
    public function markApproved(): true
    {
        if (! $this->status->isApproved()) {
            foreach ($this->mentioned_users as $userId) {
                $user = User::find($userId);
                $user?->notify(new MentionedInComment($this));
            }
            $this->status = ReviewStatus::APPROVED;
            $this->save();
            $this->source()->increment('comment_count');
        }

        return true;
    }
}
