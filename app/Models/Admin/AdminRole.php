<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * 管理员角色表
 *
 * @property int $id ID
 * @property string $name 角色名
 * @property string $desc 描述
 * @property string $slug 标识
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminRole extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id', 'name', 'slug', 'desc',
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
            'name' => 'string',
            'desc' => 'string',
            'slug' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 管理员
     */
    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_role_users', 'role_id', 'user_id');
    }

    /**
     * 权限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permissions', 'role_id', 'permission_id')->withTimestamps();
    }

    /**
     * 菜单
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(AdminMenu::class, 'admin_role_menus', 'role_id', 'menu_id')->withTimestamps();
    }

    /**
     * 获取角色树（兼容xm-select格式）
     *
     * @param  array  $options  配置选项
     * @return array 树形结构数组
     */
    public static function getTreeForXmSelect(array $options = []): array
    {
        // 合并默认选项
        $options = array_merge([
            'includeAllFields' => false,
            'selectedValues' => [],
        ], $options);

        // 构建查询
        $query = self::query()->orderBy('id');

        // 选择字段
        if ($options['includeAllFields']) {
            $query->select('*');
        } else {
            $query->select('id as value', 'name');
        }

        // 获取角色
        return $query->get()->toArray();
    }
}
