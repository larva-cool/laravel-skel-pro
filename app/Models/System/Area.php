<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Enum\CacheKey;
use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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
        'parent_id',
        'name',
        'area_code',
        'lat',
        'lng',
        'city_code',
        'order',
    ];

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
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        $clearCache = function (Area $model) {
            Cache::forget(CacheKey::key(CacheKey::AREA_TREE, $model->parent_id));
            Cache::forget(CacheKey::key(CacheKey::AREA_TREE, 'xm-select', 0));
            Cache::forget(CacheKey::key(CacheKey::AREA_TREE, 'xm-select', $model->parent_id));
        };

        static::saved($clearCache);
        static::deleted($clearCache);
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

    /**
     * 根据 ID 获取地区名称
     */
    public static function getNameById(int|string $id): ?string
    {
        return static::query()->where('id', $id)->value('name');
    }

    /**
     * 根据 area_code 获取地区
     */
    public static function findByAreaCode(int $code): ?self
    {
        return static::query()->where('area_code', $code)->first();
    }

    /**
     * 获取省列表
     */
    public static function getProvinces()
    {
        return Cache::remember(CacheKey::AREA_TREE.':province', 86400, function () {
            return static::query()
                ->whereNull('parent_id')
                ->orderBy('order')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * 根据父ID获取子地区（市/区/街道）
     */
    public static function getByParentId(int|string $parentId)
    {
        return Cache::remember(CacheKey::key(CacheKey::AREA_TREE, $parentId), 86400, function () use ($parentId) {
            return static::query()
                ->where('parent_id', $parentId)
                ->orderBy('order')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * 省市区三级联动结构
     */
    public static function areaTree(int|string $parentId = 0): array
    {
        $items = static::getByParentId($parentId);

        $tree = [];
        foreach ($items as $item) {
            $tree[] = [
                'id' => $item->id,
                'parent_id' => $item->parent_id,
                'name' => $item->name,
                'area_code' => $item->area_code,
                'city_code' => $item->city_code,
                'lat' => $item->lat,
                'lng' => $item->lng,
                'children' => static::areaTree($item->id),
            ];
        }

        return $tree;
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

        // 规范化 parentId：null 或 0 都表示顶级
        $normalizedParentId = empty($parentId) || $parentId === '0' ? null : $parentId;
        $cacheParentId = $normalizedParentId ?? 'root';

        // 使用缓存
        $cacheKey = CacheKey::key(CacheKey::AREA_TREE, 'xm-select', $cacheParentId);

        $tree = Cache::remember($cacheKey, 86400, function () use ($normalizedParentId) {
            // 一次查询获取所有地区
            $allAreas = self::query()
                ->select('id', 'name', 'parent_id', 'order')
                ->orderBy('order')
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            // 按 parent_id 分组，null 键需要特殊处理
            $grouped = [];
            foreach ($allAreas as $area) {
                $pid = $area->parent_id;
                if (!isset($grouped[$pid])) {
                    $grouped[$pid] = [];
                }
                $grouped[$pid][] = $area;
            }

            // 递归构建树形结构（内存操作）
            $buildTree = function ($pid) use (&$buildTree, $grouped) {
                $tree = [];
                $children = $grouped[$pid] ?? [];

                foreach ($children as $area) {
                    $node = [
                        'value' => $area->id,
                        'name' => $area->name,
                        'icon' => 'layui-icon layui-icon-set',
                    ];

                    $childNodes = $buildTree($area->id);
                    if (!empty($childNodes)) {
                        $node['children'] = $childNodes;
                    }

                    $tree[] = $node;
                }

                return $tree;
            };

            return $buildTree($normalizedParentId);
        });

        // 处理选中状态（每次调用都需要重新处理）
        $applySelected = function (&$tree) use (&$applySelected, $options) {
            foreach ($tree as &$node) {
                $node['selected'] = in_array($node['value'], $options['selectedValues']);
                if (isset($node['children'])) {
                    $applySelected($node['children']);
                }
            }
        };

        $applySelected($tree);

        return $tree;
    }
}
