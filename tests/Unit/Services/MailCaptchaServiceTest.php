<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\MailCaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * MailCaptchaService 测试
 */
#[TestDox('MailCaptchaService 测试')]
class MailCaptchaServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试 setUp
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 测试 make 静态方法
     */
    #[Test]
    #[TestDox('测试 make 静态方法')]
    public function test_make()
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';

        // 测试 make 方法
        $mailCaptchaService = MailCaptchaService::make($email, $ip);
        $this->assertInstanceOf(MailCaptchaService::class, $mailCaptchaService);
    }

    /**
     * 测试 getVerifyCode 方法
     */
    #[Test]
    #[TestDox('测试 getVerifyCode 方法')]
    public function test_get_verify_code()
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';

        // 创建 MailCaptchaService 实例
        $mailCaptchaService = MailCaptchaService::make($email, $ip);

        // 测试固定验证码
        $fixedCode = '123456';
        $mailCaptchaService->setFixedVerifyCode($fixedCode);
        $this->assertEquals($fixedCode, $mailCaptchaService->getVerifyCode());

        // 测试生成新验证码
        // 注意：这里我们不能测试生成新验证码的情况，因为它依赖于 MailCode 模型
        // 我们只能测试固定验证码的情况
    }

    /**
     * 测试 generateValidationHash 方法
     */
    #[Test]
    #[TestDox('测试 generateValidationHash 方法')]
    public function test_generate_validation_hash()
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';

        // 创建 MailCaptchaService 实例
        $mailCaptchaService = MailCaptchaService::make($email, $ip);

        // 测试生成验证哈希
        $code = '123456';
        $hash = $mailCaptchaService->generateValidationHash($code);
        $this->assertEquals('21', $hash); // 1+2+3+4+5+6 = 21
    }

    /**
     * 测试 validate 方法 - 使用固定验证码
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 使用固定验证码')]
    public function test_validate_with_fixed_code()
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';

        // 创建 MailCaptchaService 实例
        $mailCaptchaService = MailCaptchaService::make($email, $ip);

        // 设置固定验证码
        $fixedCode = '123456';
        $mailCaptchaService->setFixedVerifyCode($fixedCode);

        // 测试验证成功
        $result = $mailCaptchaService->validate($fixedCode, true);
        $this->assertTrue($result);

        // 测试验证失败
        $result = $mailCaptchaService->validate('654321', true);
        $this->assertFalse($result);

        // 测试不区分大小写
        $mailCaptchaService->setFixedVerifyCode('ABC123');
        $result = $mailCaptchaService->validate('abc123', false);
        $this->assertTrue($result);
    }

    /**
     * 测试 setter 和 getter 方法
     */
    #[Test]
    #[TestDox('测试 setter 和 getter 方法')]
    public function test_setter_and_getter_methods()
    {
        $email = 'test@example.com';
        $ip = '127.0.0.1';

        // 创建 MailCaptchaService 实例
        $mailCaptchaService = MailCaptchaService::make($email, $ip);

        // 测试 testLimit
        $mailCaptchaService->setTestLimit(5);
        $this->assertEquals(5, $mailCaptchaService->getTestLimit());

        // 测试 waitTime
        $mailCaptchaService->setWaitTime(120);
        $this->assertEquals(120, $mailCaptchaService->getWaitTime());

        // 测试 duration
        $mailCaptchaService->setDuration(20);
        $this->assertEquals(20, $mailCaptchaService->getDuration());

        // 测试 length
        $mailCaptchaService->setLength(8);
        $this->assertEquals(8, $mailCaptchaService->getLength());

        // 测试 ip
        $newIp = '192.168.1.1';
        $mailCaptchaService->setIp($newIp);
        $this->assertEquals($newIp, $mailCaptchaService->getIp());

        // 测试 fixedVerifyCode
        $fixedCode = '123456';
        $mailCaptchaService->setFixedVerifyCode($fixedCode);
        $this->assertEquals($fixedCode, $mailCaptchaService->getFixedVerifyCode());
    }
}
