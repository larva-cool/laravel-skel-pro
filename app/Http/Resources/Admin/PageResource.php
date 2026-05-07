<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\System\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 单页管理资源
 *
 * @mixin Page
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'desc' => $this->desc,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.pages.edit', ['page' => $this]),
            'update_url' => route('admin.pages.update', ['page' => $this]),
            'delete_url' => route('admin.pages.destroy', ['page' => $this]),
        ];
    }
}
