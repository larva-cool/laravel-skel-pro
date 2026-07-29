<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;

/**
 * 后台控制器基类
 *
 * 提供统一的 JSON 响应格式
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
abstract class Controller extends \App\Http\Controllers\Controller
{
    /**
     * 成功响应
     *
     * @param  mixed  $data  响应数据
     * @param  string  $message  响应消息
     * @param  int  $code  状态码
     */
    protected function success(mixed $data = null, string $message = 'success', int $code = 200): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * 错误响应
     *
     * 统一返回 HTTP 200，业务状态通过 body 中的 code 字段标识，
     * 便于前端拦截器读取 msg 展示具体错误信息。
     *
     * @param  string  $message  错误消息
     * @param  int  $code  业务状态码
     * @param  mixed  $data  附加数据
     */
    protected function error(string $message = 'error', int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
