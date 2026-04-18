<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Overtrue\EasySms\EasySms;

/**
 * SMS 服务
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EasySms::class;
    }
}
