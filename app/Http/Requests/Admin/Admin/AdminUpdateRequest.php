<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use App\Enums\AdminStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * 管理员更新请求
 *
 * @property-read string|null $email 邮箱
 * @property-read string|null $phone 手机号
 * @property-read string $name 昵称
 * @property-read string|null $password 密码（留空则不修改）
 * @property-read int $status 状态
 * @property-read string[]|null $roles 角色名称数组
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $adminId = (int) $this->route('id');

        return [
            'email' => [
                'nullable', 'email', 'max:100',
                Rule::unique('admin_users', 'email')->ignore($adminId),
            ],
            'phone' => [
                'nullable', 'string', 'regex:/^1[2-9]\d{9}$/',
                Rule::unique('admin_users', 'phone')->ignore($adminId),
            ],
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'password' => ['nullable', 'string', Password::defaults()],
            'status' => ['required', 'integer', Rule::enum(AdminStatus::class)],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'max:50', 'exists:roles,name'],
        ];
    }
}
