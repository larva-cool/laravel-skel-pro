<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Requests\Admin\Setting;

use App\Models\System\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * 批量保存配置请求
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // 从数据库获取所有已知配置项的 key 和 cast_type
        $settingKeys = Setting::query()->pluck('cast_type', 'key')->toArray();

        $rules = [];
        foreach ($settingKeys as $key => $castType) {
            // 使用点号转义规则（Laravel 验证支持点号嵌套）
            $ruleKey = str_replace('.', '.', $key);
            $rules[$ruleKey] = match ($castType) {
                'int', 'integer' => ['nullable', 'integer'],
                'float' => ['nullable', 'numeric'],
                'bool', 'boolean' => ['nullable', 'in:0,1,true,false,on,off'],
                default => ['nullable', 'string'],
            };
        }

        return $rules;
    }

    /**
     * 获取已验证的配置数据（只保留已知的配置项）
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $knownKeys = Setting::query()->pluck('key')->toArray();
        $dotted = Arr::dot($validated);

        // 只保留数据库中存在的 key
        $filtered = [];
        foreach ($dotted as $key => $value) {
            if (in_array($key, $knownKeys, true)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
