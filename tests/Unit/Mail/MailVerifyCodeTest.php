<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\MailVerifyCode;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * MailVerifyCode 测试
 */
#[TestDox('MailVerifyCode 测试')]
class MailVerifyCodeTest extends TestCase
{
    /**
     * 测试构造函数
     */
    #[Test]
    #[TestDox('测试构造函数')]
    public function test_constructor()
    {
        // 测试验证码
        $verifyCode = '123456';

        // 创建 MailVerifyCode 实例
        $mailVerifyCode = new MailVerifyCode($verifyCode);

        // 使用反射获取私有属性
        $reflection = new \ReflectionClass($mailVerifyCode);
        $verifyCodeProperty = $reflection->getProperty('verifyCode');
        $verifyCodeProperty->setAccessible(true);

        // 验证验证码是否正确设置
        $this->assertEquals($verifyCode, $verifyCodeProperty->getValue($mailVerifyCode));
    }

    /**
     * 测试 envelope 方法
     */
    #[Test]
    #[TestDox('测试 envelope 方法')]
    public function test_envelope()
    {
        // 设置应用名称
        Config::set('app.name', 'Test App');

        // 创建 MailVerifyCode 实例
        $mailVerifyCode = new MailVerifyCode('123456');

        // 测试 envelope 方法
        $envelope = $mailVerifyCode->envelope();

        // 验证返回值类型
        $this->assertInstanceOf(Envelope::class, $envelope);

        // 使用反射获取 envelope 的属性
        $reflection = new \ReflectionClass($envelope);
        $subjectProperty = $reflection->getProperty('subject');
        $subjectProperty->setAccessible(true);

        // 验证主题是否正确
        $expectedSubject = 'Email verification code :appName';
        $this->assertStringContainsString('Test App', $subjectProperty->getValue($envelope));
    }

    /**
     * 测试 content 方法
     */
    #[Test]
    #[TestDox('测试 content 方法')]
    public function test_content()
    {
        // 测试验证码
        $verifyCode = '123456';

        // 创建 MailVerifyCode 实例
        $mailVerifyCode = new MailVerifyCode($verifyCode);

        // 测试 content 方法
        $content = $mailVerifyCode->content();

        // 验证返回值类型
        $this->assertInstanceOf(Content::class, $content);

        // 使用反射获取 content 的属性
        $reflection = new \ReflectionClass($content);
        $markdownProperty = $reflection->getProperty('markdown');
        $markdownProperty->setAccessible(true);

        $withProperty = $reflection->getProperty('with');
        $withProperty->setAccessible(true);

        // 验证 markdown 模板是否正确
        $this->assertEquals('emails.verify_code', $markdownProperty->getValue($content));

        // 验证传递的数据是否正确
        $withData = $withProperty->getValue($content);
        $this->assertArrayHasKey('verifyCode', $withData);
        $this->assertEquals($verifyCode, $withData['verifyCode']);
    }

    /**
     * 测试 attachments 方法
     */
    #[Test]
    #[TestDox('测试 attachments 方法')]
    public function test_attachments()
    {
        // 创建 MailVerifyCode 实例
        $mailVerifyCode = new MailVerifyCode('123456');

        // 测试 attachments 方法
        $attachments = $mailVerifyCode->attachments();

        // 验证返回值是否为空数组
        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }
}
