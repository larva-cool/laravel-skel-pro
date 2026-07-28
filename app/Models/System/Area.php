<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 地区表
 *
 * @property int $id ID
 * @property int $parent_id 父地区
 * @property string $name 名称
 * @property int|null $city_code 区号
 * @property float|null $lat 纬度
 * @property float|null $lng 经度
 * @property int|null $area_code 区域编码
 * @property int $order 排序
 * @property Carbon $created_at 添加时间
 * @property Carbon $updated_at 更新时间
 *
 * 关系模型：
 * @property Area|null $parent 父地区
 * @property Collection<int,Area> $children 子地区
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[Table('areas')]
#[Fillable(['parent_id', 'name', 'area_code', 'lat', 'lng', 'city_code', 'order'])]
class Area extends Model
{
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'order' => 99,
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
            'parent_id' => 'integer',
            'name' => 'string',
            'area_code' => 'integer',
            'lat' => 'float',
            'lng' => 'float',
            'city_code' => 'string',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the parent relation.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class);
    }

    /**
     * Get the children relation.
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id', 'id')
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * 获取子地区ID
     */
    public function getChildrenIds(): array
    {
        return $this->children()
            ->pluck('id')
            ->all();
    }

    /**
     * 获取逗号分隔的子ID
     */
    public static function getChildIds(int|string $id): string
    {
        return static::query()->where('parent_id', $id)
            ->pluck('id')
            ->implode(',');
    }
}
