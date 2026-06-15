<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\PointTradeResource;
use App\Models\Point\PointTrade;
use Illuminate\Http\Request;

/**
 * 积分交易
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PointTradeController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:point_trades.index')->only(['index']);
    }

    /**
     * 交易记录
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = PointTrade::query()
                ->with(['user'])
                ->orderByDesc('id');
            $items = $query->paginate(per_page($request));

            return PointTradeResource::collection($items);
        }

        return view('admin.point_trade.index');
    }
}
