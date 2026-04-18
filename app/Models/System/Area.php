<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 地区表
 *
 * @property int $id ID
 * @property int $parent_id 父地区
 * @property string $name 名称
 * @property string $child_ids 子ID
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
 * @property Area[] $children 子地区
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class Area extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'areas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id', 'name', 'child_ids', 'area_code', 'lat', 'lng', 'city_code', 'order',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'order' => 0,
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
            'child_ids' => 'string',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();

        // 保存后更新父地区的子ID
        static::saved(function (Area $model) {
            if ($model->parent_id) {
                static::query()->where('id', $model->parent_id)->update([
                    'child_ids' => static::getChildIds($model->parent_id),
                ]);
            }
        });

        // 删除后更新父地区的子ID
        static::deleted(function (Area $model) {
            if ($model->parent_id) {
                static::query()->where('id', $model->parent_id)->update([
                    'child_ids' => static::getChildIds($model->parent_id),
                ]);
            }
        });
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
        return $this->hasMany(static::class, 'parent_id', 'id')->orderBy('order');
    }

    /**
     * 获取子地区ID
     */
    public function getChildrenIds(): array
    {
        return $this->children()->pluck('id')->all();
    }

    /**
     * 获取逗号分隔的子ID
     */
    public static function getChildIds(int|string $id): string
    {
        return static::query()->where('parent_id', $id)->pluck('id')->implode(',');
    }

    /**
     * 获取地区
     *
     * @param  string[]  $columns
     */
    public static function getAreas(int|string|null $parent_id = null, array $columns = ['id', 'name', 'area_code']): Collection
    {
        $query = self::query();
        if ($parent_id == 0 || empty($parent_id)) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parent_id);
        }

        return $query->select($columns)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    /**
     * 通过 ID 获取名称
     */
    public static function getNameById(int|string $id): ?string
    {
        return self::query()->where('id', $id)->value('name');
    }

    /**
     * 获取菜单树（兼容xm-select格式）
     *
     * @param  int|string|null  $parentId  父菜单ID
     * @param  array  $options  配置选项
     * @return array 树形结构数组
     */
    public static function getTreeForXmSelect(int|string|null $parentId = null, array $options = []): array
    {
        // 合并默认选项
        $options = array_merge([
            'selectedValues' => [],
        ], $options);

        // 构建查询
        $query = self::query()
            ->withCount('children')
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->orderBy('id');

        $query->select('id as value', 'name', 'order');

        // 获取当前层级菜单
        $items = $query->get()->toArray();

        // 递归获取子菜单，构建树形结构
        foreach ($items as &$item) {
            $item['icon'] = 'layui-icon layui-icon-set';
            // 确保有value字段
            $item['value'] = $item['value'] ?? $item['id'];
            // 检查是否需要标记为选中
            $item['selected'] = in_array($item['value'], $options['selectedValues']);

            // 递归获取子菜单
            $item['children'] = self::getTreeForXmSelect($item['value'], $options);

            // 移除空children数组，避免xm-select显示空折叠图标
            if (empty($item['children'])) {
                unset($item['children']);
            }
        }

        return $items;
    }
}
