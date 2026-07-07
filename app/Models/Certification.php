<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

use App\Enum\CertificationStatus;
use App\Enum\CertificationType;
use App\Events\Certification\CertificationApproved;
use App\Events\Certification\CertificationPending;
use App\Events\Certification\CertificationRejected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

/**
 * 实名认证（多态，支持 User或者其他模型）
 *
 * @property int $id 认证ID
 * @property string $certifiable_type 认证主体类型
 * @property int $certifiable_id 认证主体ID
 * @property CertificationType $type 认证类型
 * @property string $real_name 真实姓名/企业名称
 * @property string $id_card_no 身份证号码/营业执照号码
 * @property string|null $id_card_front 证件正面照片
 * @property string|null $id_card_back 证件背面照片
 * @property string|null $id_card_in_hand 手持证件照片
 * @property string|null $license 营业执照照片
 * @property string|null $contact_person 联系人
 * @property string|null $contact_phone 联系手机
 * @property string|null $contact_email 联系邮箱
 * @property CertificationStatus $status 认证状态
 * @property string|null $failed_reason 失败原因
 * @property Carbon|null $verified_at 认证通过时间
 * @property Carbon|null $submitted_at 提交时间
 * @property Carbon $updated_at 更新时间
 * @property-read Model $certifiable 认证主体
 * @property-read bool $is_approved 已认证
 * @property-read bool $is_pending 待审核
 * @property-read bool $is_rejected 已拒绝
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Certification extends Model
{
    use HasFactory;
    public const CREATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'certifications';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'certifiable_type', 'certifiable_id', 'type', 'real_name', 'id_card_no',
        'id_card_front', 'id_card_back', 'id_card_in_hand', 'license',
        'contact_person', 'contact_phone', 'contact_email',
        'status', 'failed_reason', 'verified_at', 'submitted_at', 'updated_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => CertificationStatus::UNSUBMITTED->value,
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
            'certifiable_id' => 'integer',
            'certifiable_type' => 'string',
            'type' => CertificationType::class,
            'real_name' => 'string',
            'id_card_no' => 'string',
            'id_card_front' => 'string',
            'id_card_back' => 'string',
            'id_card_in_hand' => 'string',
            'license' => 'string',
            'contact_person' => 'string',
            'contact_phone' => 'string',
            'contact_email' => 'string',
            'status' => CertificationStatus::class,
            'failed_reason' => 'string',
            'verified_at' => 'datetime',
            'submitted_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 认证主体（User）
     */
    public function certifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 是否已认证
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === CertificationStatus::APPROVED;
    }

    /**
     * 是否待审核
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === CertificationStatus::PENDING;
    }

    /**
     * 是否已拒绝
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === CertificationStatus::REJECTED;
    }

    /**
     * 是否是个人认证
     */
    public function isPersonal(): bool
    {
        return $this->type === CertificationType::PERSONAL;
    }

    /**
     * 是否是企业认证
     */
    public function isEnterprise(): bool
    {
        return $this->type === CertificationType::ENTERPRISE;
    }

    /**
     * 标记已审核
     */
    public function markApproved(): bool
    {
        $this->status = CertificationStatus::APPROVED;
        $this->verified_at = $this->freshTimestamp();
        $this->failed_reason = null;
        $this->updated_at = $this->freshTimestamp();
        $status = $this->saveQuietly();

        $this->syncIdentifiedStatus(true);

        Event::dispatch(new CertificationApproved($this));

        return $status;
    }

    /**
     * 标记审核拒绝
     */
    public function markRejected(string $failedReason): bool
    {
        $this->status = CertificationStatus::REJECTED;
        $this->failed_reason = $failedReason;
        $this->verified_at = null;
        $this->updated_at = $this->freshTimestamp();
        $status = $this->saveQuietly();

        $this->syncIdentifiedStatus(false);

        Event::dispatch(new CertificationRejected($this));

        return $status;
    }

    /**
     * 标记待审核
     */
    public function markPending(): bool
    {
        $this->status = CertificationStatus::PENDING;
        $this->failed_reason = null;
        $this->submitted_at = $this->submitted_at ?? $this->freshTimestamp();
        $this->verified_at = null;
        $this->updated_at = $this->freshTimestamp();
        $status = $this->saveQuietly();

        $this->syncIdentifiedStatus(false);

        Event::dispatch(new CertificationPending($this));

        return $status;
    }

    /**
     * 同步认证主体的 identified 状态
     */
    protected function syncIdentifiedStatus(bool $identified): void
    {
        $this->certifiable->forceFill(['identified' => $identified])->saveQuietly();
    }
}
