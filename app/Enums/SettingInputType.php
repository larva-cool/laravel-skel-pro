<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\HasLabel;

/**
 * 系统配置输入类型枚举
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
enum SettingInputType: string implements \JsonSerializable
{
    use HasLabel;

    case STRING = 'string'; // 单行文本
    case TEXTAREA = 'textarea'; // 多行文本
    case INT = 'int'; // 数字
    case BOOL = 'bool'; // 开关
    case SELECT = 'select'; // 下拉选择
    case RADIO = 'radio'; // 单选
    case CHECKBOX = 'checkbox'; // 多选
    case REMOTE_SELECT = 'remote_select'; // 远程下拉选择

    /**
     * 获取配置输入类型标签
     */
    public function label(): string
    {
        return match ($this) {
            self::STRING => '单行文本',
            self::TEXTAREA => '多行文本',
            self::INT => '数字',
            self::BOOL => '开关',
            self::SELECT => '下拉选择',
            self::RADIO => '单选',
            self::CHECKBOX => '多选',
            self::REMOTE_SELECT => '远程下拉选择',
        };
    }
}
