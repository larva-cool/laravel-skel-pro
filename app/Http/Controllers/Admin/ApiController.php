<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API 接口
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ApiController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }



    /**
     * 检查权限
     */
    public function permission(Request $request): JsonResponse
    {
        $permissions = PermissionHelper::getPermissions($request->user());

        return $this->success('ok', $permissions);
    }
}
