<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\MailCode;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * MailCode 模型测试
 */
#[TestDox('MailCode 模型测试')]
class MailCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试创建邮件验证码记录
     */
    #[Test]
    #[TestDox('测试创建邮件验证码记录')]
    public function test_create_mail_code()
    {
        // 创建邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
        ]);

        // 验证记录是否成功创建
        $this->assertDatabaseHas('mail_codes', [
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'state' => 0,
            'verify_count' => 0,
        ]);
    }

    /**
     * 测试 build 静态方法
     */
    #[Test]
    #[TestDox('测试 build 静态方法')]
    public function test_build()
    {
        // 使用 build 方法创建邮件验证码
        $mailCode = MailCode::build('test@example.com', '127.0.0.1', '654321');

        // 验证记录是否成功创建
        $this->assertInstanceOf(MailCode::class, $mailCode);
        $this->assertDatabaseHas('mail_codes', [
            'email' => 'test@example.com',
            'code' => '654321',
            'ip' => '127.0.0.1',
        ]);
    }

    /**
     * 测试 validate 方法 - 验证成功
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 验证成功')]
    public function test_validate_success()
    {
        // 创建邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
        ]);

        // 测试验证成功的情况
        $result = $mailCode->validate('123456', true);
        $this->assertTrue($result);

        // 验证状态是否已更新为已使用
        $mailCode->refresh();
        $this->assertEquals(MailCode::USED_STATE, $mailCode->state);
        $this->assertNotNull($mailCode->usage_at);
    }

    /**
     * 测试 validate 方法 - 验证失败
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 验证失败')]
    public function test_validate_failure()
    {
        // 创建邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
        ]);

        // 测试验证失败的情况
        $result = $mailCode->validate('654321', true);
        $this->assertFalse($result);

        // 验证验证次数是否已增加
        $mailCode->refresh();
        $this->assertEquals(1, $mailCode->verify_count);
        $this->assertEquals(0, $mailCode->state);
    }

    /**
     * 测试 validate 方法 - 不区分大小写
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 不区分大小写')]
    public function test_validate_case_insensitive()
    {
        // 创建邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => 'ABC123',
            'ip' => '127.0.0.1',
        ]);

        // 测试不区分大小写的验证
        $result = $mailCode->validate('abc123', false);
        $this->assertTrue($result);

        // 验证状态是否已更新为已使用
        $mailCode->refresh();
        $this->assertEquals(MailCode::USED_STATE, $mailCode->state);
    }

    /**
     * 测试 validate 方法 - 已使用的验证码
     */
    #[Test]
    #[TestDox('测试 validate 方法 - 已使用的验证码')]
    public function test_validate_used_code()
    {
        // 创建已使用的邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'state' => MailCode::USED_STATE,
        ]);

        // 测试已使用的验证码
        $result = $mailCode->validate('123456', true);
        $this->assertFalse($result);
    }

    /**
     * 测试 makeUsed 方法
     */
    #[Test]
    #[TestDox('测试 makeUsed 方法')]
    public function test_make_used()
    {
        // 创建邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
        ]);

        // 测试标记为已使用
        $result = $mailCode->makeUsed();
        $this->assertTrue($result);

        // 验证状态是否已更新
        $mailCode->refresh();
        $this->assertEquals(MailCode::USED_STATE, $mailCode->state);
        $this->assertNotNull($mailCode->usage_at);
    }

    /**
     * 测试 getCode 静态方法
     */
    #[Test]
    #[TestDox('测试 getCode 静态方法')]
    public function test_get_code()
    {
        // 创建两个邮件验证码记录，一个已使用，一个未使用
        MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'state' => MailCode::USED_STATE,
            'send_at' => Carbon::now()->subMinutes(10),
        ]);

        $unUsedCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '654321',
            'ip' => '127.0.0.1',
            'send_at' => Carbon::now(),
        ]);

        // 测试获取未使用的验证码
        $result = MailCode::getCode('test@example.com');
        $this->assertInstanceOf(MailCode::class, $result);
        $this->assertEquals('654321', $result->code);

        // 测试获取不存在的邮箱的验证码
        $nonExistentResult = MailCode::getCode('non-existent@example.com');
        $this->assertNull($nonExistentResult);
    }

    /**
     * 测试 getIpTodayCount 静态方法
     */
    #[Test]
    #[TestDox('测试 getIpTodayCount 静态方法')]
    public function test_get_ip_today_count()
    {
        // 创建今天的邮件验证码记录
        for ($i = 0; $i < 3; $i++) {
            MailCode::create([
                'email' => "test{$i}@example.com",
                'code' => '123456',
                'ip' => '127.0.0.1',
                'send_at' => Carbon::now(),
            ]);
        }

        // 创建昨天的邮件验证码记录
        MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'send_at' => Carbon::yesterday(),
        ]);

        // 测试获取今日 IP 发送次数
        $count = MailCode::getIpTodayCount('127.0.0.1');
        $this->assertEquals(3, $count);

        // 测试获取不存在的 IP 的发送次数
        $nonExistentCount = MailCode::getIpTodayCount('192.168.1.1');
        $this->assertEquals(0, $nonExistentCount);
    }

    /**
     * 测试 getMailTodayCount 静态方法
     */
    #[Test]
    #[TestDox('测试 getMailTodayCount 静态方法')]
    public function test_get_mail_today_count()
    {
        // 创建今天的邮件验证码记录
        for ($i = 0; $i < 2; $i++) {
            MailCode::create([
                'email' => 'test@example.com',
                'code' => "12345{$i}",
                'ip' => '127.0.0.1',
                'send_at' => Carbon::now(),
            ]);
        }

        // 创建昨天的邮件验证码记录
        MailCode::create([
            'email' => 'test@example.com',
            'code' => '654321',
            'ip' => '127.0.0.1',
            'send_at' => Carbon::yesterday(),
        ]);

        // 测试获取今日邮箱发送次数
        $count = MailCode::getMailTodayCount('test@example.com');
        $this->assertEquals(2, $count);

        // 测试获取不存在的邮箱的发送次数
        $nonExistentCount = MailCode::getMailTodayCount('non-existent@example.com');
        $this->assertEquals(0, $nonExistentCount);
    }

    /**
     * 测试 getTodayCount 静态方法
     */
    #[Test]
    #[TestDox('测试 getTodayCount 静态方法')]
    public function test_get_today_count()
    {
        // 创建今天的邮件验证码记录 - 同一IP不同邮箱
        MailCode::create([
            'email' => 'test1@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'send_at' => Carbon::now(),
        ]);

        MailCode::create([
            'email' => 'test2@example.com',
            'code' => '654321',
            'ip' => '127.0.0.1',
            'send_at' => Carbon::now(),
        ]);

        // 创建今天的邮件验证码记录 - 同一邮箱不同IP
        MailCode::create([
            'email' => 'test1@example.com',
            'code' => '987654',
            'ip' => '192.168.1.1',
            'send_at' => Carbon::now(),
        ]);

        // 测试获取今日总发送次数
        $count = MailCode::getTodayCount('test1@example.com', '127.0.0.1');
        $this->assertEquals(4, $count); // 2 from IP + 2 from email
    }

    /**
     * 测试 prunable 方法
     */
    #[Test]
    #[TestDox('测试 prunable 方法')]
    public function test_prunable()
    {
        // 创建邮件验证码记录
        $mailCode = MailCode::create([
            'email' => 'test@example.com',
            'code' => '123456',
            'ip' => '127.0.0.1',
            'send_at' => Carbon::now()->subDays(181), // 超过180天
        ]);

        // 测试 prunable 方法
        $prunableQuery = $mailCode->prunable();
        $this->assertDatabaseHas('mail_codes', ['id' => $mailCode->id]);

        // 执行修剪
        $prunableQuery->delete();
        $this->assertDatabaseMissing('mail_codes', ['id' => $mailCode->id]);
    }
}
