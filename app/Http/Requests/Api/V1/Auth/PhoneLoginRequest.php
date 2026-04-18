<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use App\Rules\PhoneRule;
use App\Rules\SmsCaptchaRule;
use App\Support\UserHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * 短信验证码登录
 *
 * @property string $phone 手机号码
 * @property string $device 登录设备
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class PhoneLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'device' => ['required', 'string'],
            'phone' => ['required', new PhoneRule],
            'verify_code' => ['required', 'min:4', 'max:6', new SmsCaptchaRule('phone', $this->ip())],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate()
    {
        $user = UserHelper::findOrCreatePhone($this->string('phone')->toString());
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
