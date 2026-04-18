<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * 后台基类控制器.
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
abstract class AbstractController extends Controller
{
    /**
     * 返回格式化json数据
     */
    protected function json(int $code, string $msg = 'ok', $data = [], array $extra = []): JsonResponse
    {
        if ($data instanceof Model || $data instanceof Collection) {
            $data = $data->toArray();
        }
        $result = array_merge(['code' => $code, 'data' => $data, 'message' => $msg], $extra);

        return response()->json($result);
    }

    /**
     * 返回成功
     */
    protected function success(string $msg = '', $data = []): JsonResponse
    {
        return $this->json(0, $msg ?: __('system.successful_operation'), $data);
    }

    /**
     * 返回失败
     */
    protected function fail(string $msg = '', $data = []): JsonResponse
    {
        return $this->json(1, $msg ?: __('system.operation_failed'), $data);
    }
}
