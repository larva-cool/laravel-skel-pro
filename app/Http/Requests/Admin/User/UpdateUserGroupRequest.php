<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Models\User\UserGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 更新用户组请求
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UpdateUserGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(UserGroup::class)->ignore($this->route('user_group'))],
            'desc' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('system.user_group'),
            'desc' => __('system.user_group_desc'),
        ];
    }
}
