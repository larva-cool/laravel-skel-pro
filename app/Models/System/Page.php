<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use Illuminate\Support\Carbon;

/**
 * 单页管理
 *
 * @property int $id ID
 * @property string $title 标题
 * @property string $desc 描述
 * @property string $content 内容
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Page extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title', 'desc', 'content',
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
            'title' => 'string',
            'desc' => 'string',
            'content' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
