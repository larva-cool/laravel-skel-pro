<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

/**
 * @mixin Model
 *
 * @property Fluent $extra
 */
trait HasExtraProperty
{
    /**
     * Set extra.
     *
     * @param  array<string,mixed>  $extra
     */
    public function setExtraAttribute(array $extra): void
    {
        $this->attributes['extra'] = json_encode($extra);
    }

    /**
     * Get extra.
     */
    public function getExtraAttribute(): Fluent
    {
        return new Fluent($this->getExtra());
    }

    /**
     * Get extra.
     *
     * @return array<string,mixed>
     */
    public function getExtra(): array
    {
        return \array_replace_recursive(\defined('static::DEFAULT_EXTRA') ? \constant('static::DEFAULT_EXTRA') : [], \json_decode($this->attributes['extra'] ?? '{}', true) ?? []);
    }
}
