<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Enum\Gender;
use App\Models\User;
use App\Models\User\UserExtra;
use App\Rules\PhoneRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 修改用户请求
 *
 * @property-read array $profile
 * @property-read array $extra
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'username' => ['nullable', 'string', 'max:255', Rule::unique(User::class, 'username')->ignore($user)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user)],
            'phone' => ['required', new PhoneRule, Rule::unique(User::class, 'phone')->ignore($user)],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            // 个人资料
            'profile' => ['required', 'array'],
            'profile.birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
            'profile.gender' => ['nullable', Rule::enum(Gender::class)],
            'profile.province_id' => ['nullable', 'integer'],
            'profile.city_id' => ['nullable', 'integer'],
            'profile.district_id' => ['nullable', 'integer'],
            'profile.intro' => ['nullable', 'string'],
            'profile.bio' => ['nullable', 'string'],
            // 扩展信息
            'extra' => ['required', 'array'],
            'extra.invite_code' => ['required', 'string', Rule::unique(UserExtra::class, 'invite_code')->ignore($user->id, 'user_id')],
            'extra.username_change_count' => ['required', 'integer'],
        ];
    }
}
