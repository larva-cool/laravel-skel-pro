<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Casts\AsJson;
use App\Models\Model;
use App\Support\AdminHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * 管理员权限表
 *
 * @property int $id ID
 * @property int $parent_id 父权限
 * @property string $name 权限名称
 * @property string $slug 权限标识
 * @property string $http_method HTTP 方法
 * @property string $http_path HTTP 路径
 * @property int $order 排序
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AdminPermission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id', 'name', 'slug', 'http_method', 'http_path', 'order',
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
            'slug' => 'string',
            'http_method' => AsJson::class,
            'http_path' => AsJson::class,
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Detach models from the relationship.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::deleting(function ($model) {
            $model->roles()->detach();
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
     * 权限角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permissions', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * 权限菜单
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(AdminMenu::class, 'admin_permission_menus', 'permission_id', 'menu_id')
            ->withTimestamps();
    }

    /**
     * 创建权限配置
     *
     * @return array[]
     */
    public static function makePermission(string $key): array
    {
        return [
            [
                'name' => '列表',
                'slug' => $key.'.index',
                'order' => 1000,
            ],
            [
                'name' => '创建',
                'slug' => $key.'.create',
                'order' => 1000,
            ],
            [
                'title' => '修改',
                'slug' => $key.'.edit',
                'order' => 1000,
            ],
            [
                'title' => '删除',
                'slug' => $key.'.destroy',
                'order' => 1000,
            ],
        ];
    }

    /**
     * If request should pass through the current permission.
     */
    public function shouldPassThrough(Request $request): bool
    {
        if (! $this->http_path) {
            return false;
        }

        $method = $this->http_method;

        $matches = array_map(function ($path) use ($method) {
            if (Str::contains($path, ':')) {
                [$method, $path] = explode(':', $path);
                $method = explode(',', $method);
            }

            $path = Str::contains($path, '.') ? $path : ltrim(config('admin.route.prefix'), '/');

            return compact('method', 'path');
        }, $this->http_path);

        foreach ($matches as $match) {
            if ($this->matchRequest($match, $request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 请求是否与特定的HTTP方法和路径相匹配。
     */
    protected function matchRequest(array $match, Request $request): bool
    {
        if (! $path = trim($match['path'], '/')) {
            return false;
        }

        if (! AdminHelper::matchRequestPath($path, $request->decodedPath())) {
            return false;
        }

        $method = collect($match['method'])->filter()->map(function ($method) {
            return strtoupper($method);
        });

        return $method->isEmpty() || $method->contains($request->method());
    }

    /**
     * 获取权限树（兼容xm-select格式）
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
            ->orderBy('order')
            ->orderBy('id');

        // 选择字段
        if ($options['includeAllFields']) {
            $query->select('*');
        } else {
            $query->select('id as value', 'name', 'order');
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
