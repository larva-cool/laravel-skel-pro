<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Report;

use App\Casts\AsJson;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Admin\Admin;
use App\Models\Model;
use App\Models\Traits;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 举报模型
 *
 * @property int $id 举报ID
 * @property int $user_id 举报人
 * @property string $reportable_type 被举报对象类型
 * @property int $reportable_id 被举报对象ID
 * @property ReportReason $reason 举报原因
 * @property string|null $content 补充说明
 * @property array|null $evidence 证据URL
 * @property ReportStatus $status 处理状态
 * @property string|null $remark 管理员备注
 * @property int|null $handled_by 处理人
 * @property Carbon|null $handled_at 处理时间
 * @property string|null $ip_address 举报IP
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property User $user 举报人
 * @property Admin|null $handler 处理管理员
 * @property Model|null $reportable 被举报对象
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class Report extends Model
{
    use HasFactory;
    use Traits\HasUser;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'reportable_type', 'reportable_id', 'reason', 'content',
        'evidence', 'status', 'remark', 'handled_by', 'handled_at', 'ip_address',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => ReportStatus::PENDING->value,
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
            'reportable_type' => 'string',
            'reportable_id' => 'integer',
            'reason' => ReportReason::class,
            'content' => 'string',
            'evidence' => AsJson::class,
            'status' => ReportStatus::class,
            'remark' => 'string',
            'handled_by' => 'integer',
            'handled_at' => 'datetime',
            'ip_address' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 被举报对象 - 多态关联
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
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
     * 由管理员处理
     */
    public function handleByAdmin(Admin $admin, ReportStatus $status, ?string $remark = null): bool
    {
        $this->status = $status;
        $this->remark = $remark;
        $this->handled_by = $admin->id;
        $this->handled_at = now();

        return $this->save();
    }
}
