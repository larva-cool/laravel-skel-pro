<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\MailCodeResource;
use App\Models\System\MailCode;
use Illuminate\Http\Request;

/**
 * 邮件验证码管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class MailCodeController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:mail_codes.index')->only(['index']);
    }

    /**
     * 邮件验证码列表
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = MailCode::query()->orderByDesc('id');

            if ($keyword = $request->query('email')) {
                $query->where('email', 'like', '%'.$keyword.'%');
            }
            if ($request->filled('state')) {
                $query->where('state', (int) $request->query('state'));
            }

            $items = $query->paginate(per_page($request));

            return MailCodeResource::collection($items);
        }

        return view('admin.mail_code.index');
    }
}
