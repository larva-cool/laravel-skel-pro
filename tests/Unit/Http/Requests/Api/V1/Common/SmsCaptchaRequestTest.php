<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Api\V1\Common;

use App\Http\Requests\Api\V1\Common\SmsCaptchaRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 手机验证码请求验证测试
 */
#[CoversClass(SmsCaptchaRequest::class)]
class SmsCaptchaRequestTest extends TestCase
{
    /**
     * 测试验证规则
     */
    #[Test]
    #[TestDox('测试验证规则')]
    public function test_rules()
    {
        // 创建 SmsCaptchaRequest 实例
        $request = new class extends SmsCaptchaRequest
        {
            /**
             * Get the client IP address.
             *
             * @return string
             */
            public function ip()
            {
                return '127.0.0.1';
            }
        };

        $rules = $request->rules();

        // 验证规则数组结构
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('phone', $rules);
        $this->assertArrayHasKey('scene', $rules);

        // 验证 phone 字段的规则
        $phoneRules = $rules['phone'];
        $this->assertIsArray($phoneRules);
        $this->assertContains('required', $phoneRules);

        // 验证 scene 字段的规则
        $sceneRules = $rules['scene'];
        $this->assertIsArray($sceneRules);
        $this->assertContains('string', $sceneRules);
    }
}
