<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Enum;

/**
 * 系统配置值类型枚举类
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class SettingType
{
    // 值类型
    public const CAST_TYPE_INT = 'int';
    public const CAST_TYPE_FLOAT = 'float';
    public const CAST_TYPE_BOOL = 'bool';
    public const CAST_TYPE_STRING = 'string';
}
