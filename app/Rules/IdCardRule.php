<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * 身份证号码验证规则
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class IdCardRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value)) {
            $fail('身份证号码格式不正确')->translate();

            return;
        }

        $value = (string) $value;

        // 基本格式验证：15位或18位，最后一位可能是X
        if (! preg_match('/^\d{15}$|^\d{17}[\dXx]$/', $value)) {
            $fail('身份证号码格式不正确')->translate();

            return;
        }

        // 18位身份证号码校验
        if (strlen($value) === 18) {
            $this->validate18Digit($value, $fail);
        }
    }

    /**
     * 验证18位身份证号码
     */
    private function validate18Digit(string $idCard, Closure $fail): void
    {
        // 权重因子
        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        // 校验码对应值
        $checkCodes = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += (int) $idCard[$i] * $weights[$i];
        }

        $mod = $sum % 11;
        $lastChar = strtoupper($idCard[17]);

        if ($lastChar !== $checkCodes[$mod]) {
            $fail('身份证号码格式不正确')->translate();
        }
    }
}
