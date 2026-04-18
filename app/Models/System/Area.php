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
        'parent_id', 'name',  'area_code', 'lat', 'lng', 'city_code', 'order',
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
        static::saved(function (Area $model) {
            Cache::forget(CacheKey::key(CacheKey::AREA_TREE, $model->parent_id));
        });

        static::deleted(function (Area $model) {
            Cache::forget(CacheKey::key(CacheKey::AREA_TREE, $model->parent_id));
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
        return Cache::remember(CacheKey::AREA_TREE . ':province', 86400, function () {
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
                'id'         => $item->id,
                'parent_id'  => $item->parent_id,
                'name'       => $item->name,
                'area_code'  => $item->area_code,
                'city_code'  => $item->city_code,
                'lat'        => $item->lat,
                'lng'        => $item->lng,
                'children'   => static::areaTree($item->id),
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
     */
    public static function getTreeForXmSelect(int|string|null $parentId = null, array $options = []): array
    {
        $options = array_merge([
            'selectedValues' => [],
        ], $options);

        $query = self::query()
            ->withCount('children')
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->orderBy('id');

        $query->select('id as value', 'name', 'order');
        $items = $query->get()->toArray();

        foreach ($items as &$item) {
            $item['icon'] = 'layui-icon layui-icon-set';
            $item['selected'] = in_array($item['value'], $options['selectedValues']);
            $item['children'] = self::getTreeForXmSelect($item['value'], $options);

            if (empty($item['children'])) {
                unset($item['children']);
            }
        }

        return $items;
    }
}
