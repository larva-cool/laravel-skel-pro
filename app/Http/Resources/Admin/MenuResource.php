<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Admin\AdminMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 管理员菜单资源
 *
 * @mixin AdminMenu
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = match ($this->type) {
            0 => '目录',
            1 => '菜单',
            2 => '权限',
            default => '未知',
        };

        return [
            'id' => $this->id,
            'value' => $this->id,
            'name' => $this->title,
            'title' => $this->title,
            'icon' => 'layui-icon '.$this->icon,
            'key' => $this->key,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'href' => $this->href,
            'type' => $type,
            'is_parent' => $this->children_count > 0,
            'show_toolbar' => $this->id != 10000000, // 是否显示编辑菜单
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.menus.edit', ['menu' => $this]),
            'update_url' => route('admin.menus.update', ['menu' => $this]),
            'delete_url' => route('admin.menus.destroy', ['menu' => $this]),
        ];
    }
}
