<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\PhoneCodeResource;
use App\Models\System\PhoneCode;
use Illuminate\Http\Request;

/**
 * 短信验证码管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class PhoneCodeController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:phone_codes.index')->only(['index']);
    }

    /**
     * 短信验证码列表
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = PhoneCode::query()->orderByDesc('id');

            if ($keyword = $request->query('phone')) {
                $query->where('phone', 'like', '%'.$keyword.'%');
            }
            if ($scene = $request->query('scene')) {
                $query->where('scene', $scene);
            }
            if ($request->filled('state')) {
                $query->where('state', (int) $request->query('state'));
            }

            $items = $query->paginate(per_page($request));

            return PhoneCodeResource::collection($items);
        }

        return view('admin.phone_code.index');
    }
}
