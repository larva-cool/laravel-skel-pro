<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * 支付密码验证规则
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class PayPasswordRule implements ValidationRule
{
    /**
     * The user.
     */
    public User $user;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 验证支付密码是否正确
        if (! $this->user->verifyPayPassword($value)) {
            $fail(__('user.pay_password'));
        }
    }
}
