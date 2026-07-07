<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * 实名认证关系
 *
 * @property Certification|null $certification 实名认证
 * @property-read bool $is_certified 是否已认证
 *
 * @mixin Model
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
trait HasCertification
{
    /**
     * 实名认证（一对一多态）
     */
    public function certification(): MorphOne
    {
        return $this->morphOne(Certification::class, 'certifiable');
    }

    /**
     * 是否已实名认证
     */
    public function getIsCertifiedAttribute(): bool
    {
        return $this->certification?->is_approved ?? false;
    }
}
