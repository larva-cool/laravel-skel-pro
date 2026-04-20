<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Admin;

use App\Models\Admin\Admin;
use App\Rules\PhoneRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminPersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => [
                'nullable', new PhoneRule,
                Rule::unique(Admin::class)->ignore($this->user()->phone),
            ],
            'email' => [
                'required', 'string', 'email', Rule::unique(Admin::class)->ignore($this->user()->email),
            ],
        ];
    }
}
