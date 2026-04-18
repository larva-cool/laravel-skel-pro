<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

/**
 * Add settings property to model.
 *
 * @mixin Model
 *
 * @property Fluent $settings
 */
trait HasSettingsProperty
{
    /**
     * Set settings.
     *
     * @param  array<string,mixed>  $settings
     */
    public function setSettingsAttribute(array $settings): void
    {
        $this->attributes['settings'] = json_encode($settings);
    }

    /**
     * Get settings.
     */
    public function getSettingsAttribute(): Fluent
    {
        return new Fluent($this->getSettings());
    }

    /**
     * Get settings.
     *
     * @return array<string,mixed>
     */
    public function getSettings(): array
    {
        return \array_replace_recursive(\defined('static::DEFAULT_SETTINGS') ? \constant('static::DEFAULT_SETTINGS') : [], \json_decode($this->attributes['settings'] ?? '{}', true) ?? []);
    }
}
