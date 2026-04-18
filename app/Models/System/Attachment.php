<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use App\Models\User;
use App\Observers\AttachmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * 附件模型
 *
 * @property int $id ID
 * @property int $user_id 上传用户ID
 * @property string $storage 存储驱动
 * @property string $origin_name 原始文件名
 * @property string $file_name 附件名称
 * @property string $file_path 附件URL
 * @property string $mime_type MIME类型
 * @property string $file_size 文件大小
 * @property string $file_ext 文件扩展名
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property-read string $file_url 文件Url
 * @property User $user 上传用户
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[ObservedBy([AttachmentObserver::class])]
class Attachment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'storage', 'origin_name', 'file_name', 'file_path', 'mime_type', 'file_size', 'file_ext',
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
            'storage' => 'string',
            'origin_name' => 'string',
            'file_name' => 'string',
            'file_path' => 'string',
            'mime_type' => 'string',
            'file_size' => 'integer',
            'file_ext' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取文件Url
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, $attributes) {
                if (URL::isValidUrl($attributes['file_path'])) {
                    return $attributes['file_path'];
                }

                return Storage::url($attributes['file_path']);
            },
        );
    }
}
