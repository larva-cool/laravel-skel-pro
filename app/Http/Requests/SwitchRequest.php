<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\StatusSwitch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 切换状态请求
 *
 * @property int $id ID
 * @property StatusSwitch $status 状态
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class SwitchRequest extends FormRequest
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
        return [
            'id' => ['required', 'integer'],
            'status' => [
                'required', Rule::enum(StatusSwitch::class),
            ],
        ];
    }
}
