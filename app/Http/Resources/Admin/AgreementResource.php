<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\System\Agreement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 协议资源
 *
 * @mixin Agreement
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AgreementResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status->label(),
            'order' => $this->order,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'edit_url' => route('admin.agreements.edit', $this->id),
            'update_url' => route('admin.agreements.update', $this->id),
            'delete_url' => route('admin.agreements.destroy', $this->id),
        ];
    }
}
