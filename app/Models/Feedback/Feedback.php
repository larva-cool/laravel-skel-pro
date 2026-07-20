<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Feedback;

use App\Casts\AsJson;
use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Models\Admin\Admin;
use App\Models\Model;
use App\Models\Traits;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 用户反馈模型
 *
 * @property int $id 反馈ID
 * @property int $user_id 用户ID
 * @property FeedbackType $type 反馈类型
 * @property string|null $title 反馈标题
 * @property string $content 反馈内容
 * @property string|null $contact 联系方式
 * @property array|null $attachments 附件URL
 * @property FeedbackStatus $status 处理状态
 * @property string|null $reply 管理员回复
 * @property int|null $handled_by 处理人
 * @property Carbon|null $handled_at 处理时间
 * @property string|null $ip_address 提交IP
 * @property string|null $user_agent 客户端UA
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property User $user 提交用户
 * @property Admin|null $handler 处理管理员
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class Feedback extends Model
{
    use HasFactory;
    use Traits\HasUser;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feedbacks';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'type', 'title', 'content', 'contact', 'attachments',
        'status', 'reply', 'handled_by', 'handled_at', 'ip_address', 'user_agent',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type' => FeedbackType::OTHER->value,
        'status' => FeedbackStatus::PENDING->value,
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
            'type' => FeedbackType::class,
            'title' => 'string',
            'content' => 'string',
            'contact' => 'string',
            'attachments' => AsJson::class,
            'status' => FeedbackStatus::class,
            'reply' => 'string',
            'handled_by' => 'integer',
            'handled_at' => 'datetime',
            'ip_address' => 'string',
            'user_agent' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 处理人（管理员）
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by')->withDefault([
            'name' => '',
            'username' => '',
        ]);
    }

    /**
     * 由管理员回复
     */
    public function replyByAdmin(Admin $admin, string $reply, FeedbackStatus $status = FeedbackStatus::REPLIED): bool
    {
        $this->reply = $reply;
        $this->status = $status;
        $this->handled_by = $admin->id;
        $this->handled_at = now();

        return $this->save();
    }
}
