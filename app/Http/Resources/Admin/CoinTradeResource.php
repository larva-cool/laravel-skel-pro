<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Coin\CoinTrade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 金币记录
 *
 * @mixin CoinTrade
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CoinTradeResource extends JsonResource
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
            'user_name' => $this->user?->name,
            'user_avatar' => $this->user?->avatar,
            'coins' => $this->coins,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
