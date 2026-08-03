<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 更新当前管理员资料请求
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminProfileRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\Admin\Admin $admin */
        $admin = $this->user();

        return [
            'name' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:admin_users,email,'.$admin->id],
            'phone' => ['nullable', 'string', 'size:11', 'unique:admin_users,phone,'.$admin->id],
        ];
    }
}
