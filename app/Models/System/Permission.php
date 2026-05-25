<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use Illuminate\Support\Carbon;

/**
 * 权限模型
 *
 * @property int $id ID
 * @property string $name 权限
 * @property string $display_name 权限名称
 * @property string $guard_name
 * @property Carbon $created_at 添加时间
 * @property Carbon $updated_at 更新时间
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Permission extends \Spatie\Permission\Models\Permission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'display_name', 'guard_name',
    ];
}
