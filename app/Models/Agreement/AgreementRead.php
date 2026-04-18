<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Agreement;

use App\Models\Model;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 协议管理
 *
 * @property int $id ID
 * @property int $agreement_id 协议ID
 * @property int $user_id 用户ID
 * @property Carbon $created_at 创建时间
 * @property Agreement $agreement 协议关联
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AgreementRead extends Model
{
    use HasUser;
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agreement_reads';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agreement_id', 'user_id',
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
            'agreement_id' => 'integer',
            'user_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();

    }

    /**
     * 协议关联
     */
    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
