<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * 任务达成条件
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AsTaskCondition implements CastsAttributes
{
    // 默认设置
    const DEFAULT_SETTINGS = [
        'played_time' => 0,
        'serial_days' => 0,
    ];

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return \array_replace_recursive(static::DEFAULT_SETTINGS, \json_decode($value ?? '{}', true) ?? []);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_encode($value);
    }
}
