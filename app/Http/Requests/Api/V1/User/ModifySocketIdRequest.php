<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 修改 SocketID 请求
 *
 * @property-read string $socket_id SocketID
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ModifySocketIdRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'socket_id' => ['required', 'string'],
        ];
    }
}
