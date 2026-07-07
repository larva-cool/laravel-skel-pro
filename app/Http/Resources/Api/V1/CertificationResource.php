<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 实名认证资源
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CertificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [
                'id' => null,
                'type' => null,
                'status' => 'unsubmitted',
                'status_label' => '未提交',
                'real_name' => null,
                'id_card_no' => null,
                'id_card_front' => null,
                'id_card_back' => null,
                'id_card_in_hand' => null,
                'license' => null,
                'contact_person' => null,
                'contact_phone' => null,
                'contact_email' => null,
                'failed_reason' => null,
                'submitted_at' => null,
                'verified_at' => null,
            ];
        }

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'real_name' => $this->real_name,
            'id_card_no' => $this->id_card_no,
            'id_card_front' => $this->id_card_front,
            'id_card_back' => $this->id_card_back,
            'id_card_in_hand' => $this->id_card_in_hand,
            'license' => $this->license,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'failed_reason' => $this->failed_reason,
            'submitted_at' => $this->submitted_at?->toDateTimeString(),
            'verified_at' => $this->verified_at?->toDateTimeString(),
        ];
    }
}
