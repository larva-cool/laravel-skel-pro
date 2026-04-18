<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 缓存 Key 常量
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CacheKey
{
    public const SETTINGS = 'system:settings'; // 系统配置缓存 Key
    public const DICT_TYPE = 'system:dicts:%s'; // 数据字典类型缓存 Key

    /**
     * 生成数据字典类型缓存 Key
     */
    public static function key(string $key, mixed ...$values): string
    {
        return sprintf($key, ...$values);
    }
}
