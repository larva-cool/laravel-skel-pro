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
use Illuminate\Validation\ValidationException;

/**
 * 微信公众号登录请求
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
    public function authenticate(): User
    {
        $oauth = (new WechatService)->getOAuth();
        $wUser = $oauth->userFromCode($this->code);
        $user = UserHelper::findByOpenid(SocialProvider::WECHAT_MP, $wUser->getId());
        if (! $user) {
            validation_exception('phone', trans('auth.account_does_not_exist'));
        }
        if ($user->isFrozen()) {// 禁止掉的用户不允许登录
            $user->tokens()->delete();
            validation_exception('account', trans('user.blocked'));
        }

        return $user;
    }
}
