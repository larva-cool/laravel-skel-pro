<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * 以JSON 格式存储数组
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class AsJson implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        try {
            if (! is_array($value)) {
                $value = (array) $value;
            }

            return $value ? json_encode($value, JSON_THROW_ON_ERROR) : null;
        } catch (\JsonException $e) {
            return null;
        }
    }
}
