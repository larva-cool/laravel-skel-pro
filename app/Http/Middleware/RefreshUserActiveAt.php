<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\User\RefreshUserLastActiveAtJob;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 刷新用户活动时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class RefreshUserActiveAt
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::hasUser()) {
            if ($request->user() instanceof User) {
                mt_rand(0, 9) > 5 || RefreshUserLastActiveAtJob::dispatchAfterResponse($request->user());
            }
        }

        return $next($request);
    }
}
