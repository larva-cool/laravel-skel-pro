<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AgreementResource;
use App\Models\Agreement\Agreement;
use App\Models\System\Dict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 协议
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AgreementController extends Controller
{
    /**
     * 协议类型
     */
    public function types()
    {
        $items = Dict::getOptions('AGREEMENT_TYPE');

        return response()->json($items);
    }

    /**
     * 按类型获取最新的一个协议
     *
     * @return AgreementResource
     */
    public function show($type)
    {
        $user = Auth::guard('sanctum')->user();
        $query = Agreement::query()->active($type);
        if ($user) {
            $query->with([
                'agree' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
            ]);
        }
        $item = $query->orderBy('id', 'desc')
            ->firstOrFail();

        return new AgreementResource($item);
    }

    /**
     * 同意协议
     *
     * @return AgreementResource
     */
    public function agree(Request $request)
    {
        $item = Agreement::query()->where('id', $request->id)->firstOrFail();
        $item->markAsAgree($request->user()->id);

        return new AgreementResource($item);
    }
}
