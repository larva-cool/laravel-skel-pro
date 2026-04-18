<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Common;

use App\Http\Requests\Api\V1\Common\MailCaptchaRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 邮件验证码请求验证测试
 */
#[CoversClass(MailCaptchaRequest::class)]
class MailCaptchaRequestTest extends TestCase
{
    /**
     * 测试验证规则
     */
    #[Test]
    #[TestDox('测试验证规则')]
    public function test_rules()
    {
        $request = new MailCaptchaRequest;
        $rules = $request->rules();

        // 验证规则数组结构
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('email', $rules);

        // 验证 email 字段的规则
        $emailRules = $rules['email'];
        $this->assertIsArray($emailRules);
        $this->assertContains('required', $emailRules);
        $this->assertContains('string', $emailRules);
        $this->assertContains('email', $emailRules);
        $this->assertContains('max:254', $emailRules);
    }
}
