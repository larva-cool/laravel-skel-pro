<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Report\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 举报资源（后台）
 *
 * @mixin Report
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class ReportResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? '',
            'reportable_type' => $this->reportable_type,
            'reportable_id' => $this->reportable_id,
            'reason' => $this->reason,
            'content' => $this->content,
            'evidence' => $this->evidence ?: [],
            'status' => $this->status,
            'remark' => $this->remark,
            'handled_by' => $this->handled_by,
            'handled_at' => $this->handled_at?->toDateTimeString(),
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toDateTimeString(),
            'edit_url' => route('admin.reports.edit', ['report' => $this->id]),
            'update_url' => route('admin.reports.update', ['report' => $this->id]),
            'delete_url' => route('admin.reports.destroy', ['report' => $this->id]),
        ];
    }
}
