<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Enum\CacheKey;
use App\Enum\StatusSwitch;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * 字典
 *
 * @property int $id 主键
 * @property int $parent_id 父ID
 * @property string $name 字典名称
 * @property string $description 描述
 * @property string $code 字典编码
 * @property StatusSwitch $status 状态
 * @property int $order 排序
 * @property Carbon $created_at 添加时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon $deleted_at 删除时间
 *
 * 关系模型：
 * @property Dict|null $parent 父字典
 * @property Dict[] $children 子内容
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class Dict extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dicts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id', 'name', 'description', 'code', 'status', 'order', 'child_ids',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => StatusSwitch::ENABLED->value,
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
            'description' => 'string',
            'code' => 'string',
            'child_ids' => 'string',
            'status' => StatusSwitch::class,
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
        static::saved(function (Dict $model) {
            Cache::forget(CacheKey::key(CacheKey::DICT_TYPE, $model->code));
        });
        static::deleted(function (Dict $model) {
            Cache::forget(CacheKey::key(CacheKey::DICT_TYPE, $model->code));
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
     * 获取子ID
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
        return static::query()->where('parent_id', $id)->where('status', StatusSwitch::ENABLED->value)->pluck('id')->implode(',');
    }

    /**
     * 通过 ID 获取名称
     */
    public static function getNameById(int|string $id): ?string
    {
        return self::query()->where('id', $id)->value('name');
    }

    /**
     * 通过 ID 获取 Code
     */
    public static function getCodeById(int|string $id): ?string
    {
        return self::query()->where('id', $id)->value('code');
    }

    /**
     * 获取字典数据
     */
    public static function getOptions(string $code): array
    {
        return Cache::remember(CacheKey::key(CacheKey::DICT_TYPE, $code), 3600, function () use ($code) {
            $dict = self::query()->with(['children'])->whereNull('parent_id')
                ->where('code', '=', $code)
                ->where('status', StatusSwitch::ENABLED->value)
                ->first();
            if ($dict && $dict->children) {
                return $dict->children->pluck('name', 'code')->toArray();
            }

            return [];
        });
    }

    /**
     * 通过 Code 获取名称
     *
     * @param  string  $type  字典类型
     * @param  string|int|null  $code  字典编码
     */
    public static function getNameByCode(string $type, string|int|null $code = null): string
    {
        if (! $code) {
            return '';
        }
        $items = self::getOptions($type);

        return $items[$code] ?? '';
    }
}
