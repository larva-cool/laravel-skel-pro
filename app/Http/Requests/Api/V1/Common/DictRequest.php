<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Common;

use App\Models\System\Dict;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 字典请求
 *
 * @property string $type 字典类型
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class DictRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required', 'string', Rule::exists(Dict::class, 'code'),
            ],
        ];
    }
}
