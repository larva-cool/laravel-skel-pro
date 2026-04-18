<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Models\Model;
use App\Support\AdminHelper;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            'http_method' => 'string',
            'http_path' => 'string',
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
     * @param  string  $path
     */
    public function getHttpPathAttribute($path): array
    {
        return explode(',', $path);
    }

    public function setHttpPathAttribute($path)
    {
        if (is_array($path)) {
            $path = implode(',', $path);
        }

        return $this->attributes['http_path'] = $path;
    }

    public function setHttpMethodAttribute($method): void
    {
        if (is_array($method)) {
            $this->attributes['http_method'] = implode(',', $method);
        }
    }

    public function getHttpMethodAttribute($method): array
    {
        if (is_string($method)) {
            return array_filter(explode(',', $method));
        }

        return $method;
    }

}
