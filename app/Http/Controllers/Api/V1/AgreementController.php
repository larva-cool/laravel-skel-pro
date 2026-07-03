<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AgreementResource;
use App\Models\System\Agreement;

/**
 * 协议
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AgreementController extends Controller
{
    /**
     * 按类型获取最新的一个协议
     */
    public function show(string $type): AgreementResource
    {
        $query = Agreement::query()->active($type);
        $item = $query->orderBy('id', 'desc')
            ->firstOrFail();

        return new AgreementResource($item);
    }
}
