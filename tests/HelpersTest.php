<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * 辅助函数测试
 */
#[TestDox('测试辅助函数')]
class HelpersTest extends TestCase
{
    /**
     * 测试手机号替换函数 - 空值
     */
    #[Test]
    #[TestDox('测试手机号替换函数 - 空值')]
    public function test_mobile_replace_with_null(): void
    {
        $result = mobile_replace(null);
        $this->assertEquals('', $result);
    }

    /**
     * 测试手机号替换函数 - 空字符串
     */
    #[Test]
    #[TestDox('测试手机号替换函数 - 空字符串')]
    public function test_mobile_replace_with_empty_string(): void
    {
        $result = mobile_replace('');
        $this->assertEquals('', $result);
    }

    /**
     * 测试手机号替换函数 - 有效的手机号
     */
    #[Test]
    #[TestDox('测试手机号替换函数 - 有效的手机号')]
    public function test_mobile_replace_with_valid_phone(): void
    {
        $result = mobile_replace('13812345678');
        $this->assertEquals('138****5678', $result);
    }

    /**
     * 测试手机号替换函数 - 短手机号
     */
    #[Test]
    #[TestDox('测试手机号替换函数 - 短手机号')]
    public function test_mobile_replace_with_short_phone(): void
    {
        $result = mobile_replace('138123');
        $this->assertEquals('138****', $result);
    }

    /**
     * 测试手机号替换函数 - 长手机号
     */
    #[Test]
    #[TestDox('测试手机号替换函数 - 长手机号')]
    public function test_mobile_replace_with_long_phone(): void
    {
        $result = mobile_replace('1381234567890');
        $this->assertEquals('138****567890', $result);
    }

    /**
     * 测试用户代理解析函数
     */
    #[Test]
    #[TestDox('测试用户代理解析函数')]
    public function test_parse_user_agent(): void
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15';
        $result = parse_user_agent($userAgent);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('platform', $result);
        $this->assertArrayHasKey('device', $result);
        $this->assertArrayHasKey('browser', $result);
        $this->assertArrayHasKey('isMobile', $result);
        $this->assertArrayHasKey('isTablet', $result);
        $this->assertArrayHasKey('isDesktop', $result);
        $this->assertArrayHasKey('isPhone', $result);
    }

    /**
     * 测试 IP 地址函数
     */
    #[Test]
    #[TestDox('测试 IP 地址函数')]
    public function test_ip_address(): void
    {
        // 使用本地回环地址进行测试，确保函数能够正常执行
        $result = ip_address('127.0.0.1');
        $this->assertIsString($result);
    }
}
