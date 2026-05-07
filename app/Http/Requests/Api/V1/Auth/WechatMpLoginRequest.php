<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Enum\SocialProvider;
use App\Models\User;
use App\Services\WechatService;
use App\Support\UserHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * 微信公众号登录请求
 *
 * @property-read string $device 设备名称
 * @property-read string $code 微信回调的Code
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class WechatMpLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'device' => ['required', 'string'],
            'code' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): ?User
    {
        $oauth = (new WechatService)->getOAuth();
        try {
            $wUser = $oauth->userFromCode($this->code);
        } catch (\Exception  $e) {
            Log::warning($e->getMessage(), $e->getTrace());
            validation_exception('code', trans('system.server_busy'));
        }
        $user = UserHelper::findByOpenid(SocialProvider::WECHAT_MP, $wUser->getId());
        if (! $user) {
            Log::warning('登录失败，该用户未注册：'.$wUser->getId());
            validation_exception('code', trans('auth.account_does_not_exist'));
        }
        if ($user->isFrozen()) {// 禁止掉的用户不允许登录
            $user->tokens()->delete();
            validation_exception('code', trans('user.blocked'));
        }

        return $user;
    }
}
