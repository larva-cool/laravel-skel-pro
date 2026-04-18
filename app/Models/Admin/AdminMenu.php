<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Models\Model;
use App\Support\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * 管理员菜单规则
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminMenu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id', 'parent_id', 'title', 'icon', 'key', 'href', 'type', 'order',
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
            'title' => 'string',
            'icon' => 'string',
            'key' => 'string',
            'href' => 'string',
            'type' => 'integer',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::deleting(function (AdminMenu $model) {
            $model->roles()->detach();
            $model->permissions()->detach();
        });
    }

    /**
     * 父菜单
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 子菜单
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * 角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_menus', 'menu_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * 权限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_permission_menus', 'menu_id', 'permission_id')
            ->withTimestamps();
    }

    /**
     * 批量添加菜单
     */
    public static function batchAdd(array $menus): void
    {
        // 终止条件：如果没有菜单数据，则退出递归
        if (empty($menus)) {
            return;
        }
        // 子菜单
        $childMenus = [];
        foreach ($menus as $currentMenu) {
            $children = [];
            // 分离父子菜单
            if (isset($currentMenu['children'])) {
                $children = $currentMenu['children'];
                unset($currentMenu['children']);
            }
            $parent = self::create($currentMenu);
            foreach ($children as $child) {
                $child['parent_id'] = $parent->id;
                $childMenus[] = $child;
            }
        }
        self::batchAdd($childMenus);
    }

    /**
     * 获取默认菜单
     */
    public static function getDefaultMenus(): array
    {
        if (app()->environment('local')) {
            return FileHelper::json(public_path('admin/admin/data/menu.json'));
        }

        return [];
    }

    /**
     * 通过ID加载Keys
     */
    public static function getKeys(array $ids): array
    {
        $cacheKey = 'admin_menu_keys:'.md5(implode(',', $ids));
        if (($keys = Cache::get($cacheKey)) == null) {
            $keys = self::query()->whereIn('id', $ids)->pluck('key')->toArray();
            Cache::set($cacheKey, $keys, 3600);
        }

        return $keys;
    }

    /**
     * 创建权限配置
     *
     * @return array[]
     */
    public static function makePermission(string $key, string $href): array
    {
        return [
            [
                'title' => '列表',
                'icon' => 'layui-icon layui-icon-username',
                'key' => $key.'.index',
                'href' => $href.'/index',
                'type' => 2,
                'order' => 1000,
            ],
            [
                'title' => '创建',
                'icon' => 'layui-icon layui-icon-username',
                'key' => $key.'.create',
                'href' => $href.'/create',
                'type' => 2,
                'order' => 1000,
            ],
            [
                'title' => '修改',
                'icon' => 'layui-icon layui-icon-username',
                'key' => $key.'.edit',
                'href' => $href.'/edit',
                'type' => 2,
                'order' => 1000,
            ],
            [
                'title' => '删除',
                'icon' => 'layui-icon layui-icon-username',
                'key' => $key.'.destroy',
                'href' => $href.'/destroy',
                'type' => 2,
                'order' => 1000,
            ],
        ];
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
            'includeAllFields' => false,
            'selectedValues' => [],
        ], $options);

        // 构建查询
        $query = self::query()
            ->withCount('children')
            ->where('parent_id', $parentId)
            ->whereIn('type', [0, 1, 2])
            ->orderBy('order')
            ->orderBy('id');

        // 选择字段
        if ($options['includeAllFields']) {
            $query->select('*');
        } else {
            $query->select('id as value', 'title as name', 'icon', 'order');
        }

        // 获取当前层级菜单
        $items = $query->get()->toArray();

        // 递归获取子菜单，构建树形结构
        foreach ($items as &$item) {
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
