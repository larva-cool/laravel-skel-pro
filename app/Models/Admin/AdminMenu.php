<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Models\Admin;

use App\Enums\MenuType;
use App\Models\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * 后台菜单模型
 *
 * @property int $id 菜单ID
 * @property int $parent_id 父级菜单ID
 * @property string|null $path 路由路径
 * @property string|null $name 路由名称
 * @property string|null $component 前端组件路径
 * @property string|null $redirect 重定向路径
 * @property string $title 菜单标题
 * @property string|null $icon 菜单图标
 * @property string|null $link 外部链接地址
 * @property MenuType $type 菜单类型
 * @property int $sort 排序
 * @property bool $is_enable 是否启用
 * @property bool $is_hide 是否在菜单中隐藏
 * @property bool $is_hide_tab 是否在标签页中隐藏
 * @property bool $is_iframe 是否以 iframe 方式内嵌
 * @property bool $keep_alive 是否开启页面缓存
 * @property bool $is_full_page 是否全屏页面
 * @property bool $fixed_tab 是否固定标签页
 * @property bool $show_badge 是否显示红点徽章
 * @property string|null $show_text_badge 文本徽章内容
 * @property string|null $active_path 激活菜单高亮路径
 * @property string|null $permission 按钮权限标识
 * @property array|null $roles 可访问角色列表
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon|null $deleted_at 删除时间
 * @property-read AdminMenu|null $parent 父级菜单
 * @property-read Collection<int, AdminMenu> $children 子菜单集合
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[Table('admin_menus')]
#[Fillable([
    'parent_id', 'path', 'name', 'component', 'redirect',
    'title', 'icon', 'link', 'type', 'sort',
    'is_enable', 'is_hide', 'is_hide_tab', 'is_iframe',
    'keep_alive', 'is_full_page', 'fixed_tab', 'show_badge',
    'show_text_badge', 'active_path', 'permission', 'roles',
])]
class AdminMenu extends Model
{
    /** @var string Spatie 权限使用的 guard 名称 */
    public const string GUARD_NAME = 'admin';

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
            'path' => 'string',
            'name' => 'string',
            'component' => 'string',
            'redirect' => 'string',
            'title' => 'string',
            'icon' => 'string',
            'link' => 'string',
            'type' => MenuType::class,
            'sort' => 'integer',
            'is_enable' => 'boolean',
            'is_hide' => 'boolean',
            'is_hide_tab' => 'boolean',
            'is_iframe' => 'boolean',
            'keep_alive' => 'boolean',
            'is_full_page' => 'boolean',
            'fixed_tab' => 'boolean',
            'show_badge' => 'boolean',
            'show_text_badge' => 'string',
            'active_path' => 'string',
            'permission' => 'string',
            'roles' => 'json',
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

        // 保存菜单时，按钮类型自动同步到 permissions 表
        static::saved(function (self $menu): void {
            $menu->syncPermission();
        });

        // 删除菜单时，按钮类型同步删除对应 permission
        static::deleted(function (self $menu): void {
            $menu->deletePermission();
        });
    }

    /**
     * 父级菜单
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    /**
     * 子菜单
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort')
            ->with('children');
    }

    /**
     * 作用域：仅启用的菜单
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enable', true);
    }

    /**
     * 作用域：按 parent_id + sort 排序
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('parent_id')->orderBy('sort');
    }

    /**
     * 作用域：顶级菜单
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->where('parent_id', 0);
    }

    /**
     * 判断是否为顶级菜单
     */
    public function isRoot(): bool
    {
        return $this->parent_id === 0;
    }

    /**
     * 判断是否为目录类型
     */
    public function isDirectory(): bool
    {
        return $this->type === MenuType::DIRECTORY;
    }

    /**
     * 判断是否为按钮类型
     */
    public function isButton(): bool
    {
        return $this->type === MenuType::BUTTON;
    }

    /**
     * 获取所有菜单树形结构
     *
     * @return Collection<int, AdminMenu>
     */
    public static function tree(bool $onlyEnabled = false): Collection
    {
        $query = static::query()->with('children')->ordered();

        if ($onlyEnabled) {
            $query->enabled();
        }

        return $query->root()->get();
    }

    /**
     * 转换为前端路由配置格式（AppRouteRecord）
     *
     * @return array<string, mixed>
     */
    public function toRouteRecord(): array
    {
        $record = [
            'path' => $this->path,
            'name' => $this->name,
            'component' => $this->component,
            'redirect' => $this->redirect,
            'meta' => array_filter([
                'title' => $this->title,
                'icon' => $this->icon,
                'link' => $this->link,
                'isHide' => $this->is_hide ?: null,
                'isHideTab' => $this->is_hide_tab ?: null,
                'isIframe' => $this->is_iframe ?: null,
                'keepAlive' => $this->keep_alive ?: null,
                'isFullPage' => $this->is_full_page ?: null,
                'fixedTab' => $this->fixed_tab ?: null,
                'showBadge' => $this->show_badge ?: null,
                'showTextBadge' => $this->show_text_badge,
                'activePath' => $this->active_path,
                'roles' => $this->roles,
                'authMark' => $this->isButton() ? $this->permission : null,
                'isAuthButton' => $this->isButton() ?: null,
            ], fn ($value): bool => ! is_null($value)),
        ];

        if ($this->children->isNotEmpty()) {
            $record['children'] = $this->children
                ->map(fn (self $child): array => $child->toRouteRecord())
                ->values()
                ->all();
        }

        // 过滤 null 值字段
        return array_filter($record, fn ($value): bool => ! is_null($value));
    }

    /**
     * 将按钮菜单同步到 permissions 表
     */
    protected function syncPermission(): void
    {
        // 非按钮类型或权限标识为空：删除历史 permission（如果存在）
        if (! $this->isButton() || blank($this->permission)) {
            $this->deletePermission();

            return;
        }

        Permission::findOrCreate($this->permission, self::GUARD_NAME)->update([
            'display_name' => $this->title,
        ]);
    }

    /**
     * 删除对应 permission（仅删除孤立的、未被其他菜单引用且未分配给角色的 permission）
     */
    protected function deletePermission(): void
    {
        if (blank($this->getOriginal('permission'))) {
            return;
        }

        $oldPermission = $this->getOriginal('permission');

        // 其他菜单仍在使用该权限标识时不删除
        $stillUsed = static::query()
            ->where('id', '!=', $this->id)
            ->where('type', MenuType::BUTTON->value)
            ->where('permission', $oldPermission)
            ->exists();

        if ($stillUsed) {
            return;
        }

        $permission = Permission::findByName($oldPermission, self::GUARD_NAME);
        $permission?->delete();
    }
}
