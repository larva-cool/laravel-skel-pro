<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enum\SocialProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 社交账号提供商枚举测试
 */
class SocialProviderTest extends TestCase
{
    /**
     * 测试所有枚举值是否正确定义
     */
    #[Test]
    #[TestDox('测试所有枚举值是否正确定义')]
    public function test_all_enum_values_are_correctly_defined(): void
    {
        $this->assertSame('wechat_mp', SocialProvider::WECHAT_MP->value);
        $this->assertSame('wechat_app', SocialProvider::WECHAT_APP->value);
        $this->assertSame('wechat_mini_program', SocialProvider::WECHAT_MINI_PROGRAM->value);
        $this->assertSame('apple', SocialProvider::APPLE->value);
        $this->assertSame('douyin', SocialProvider::DOUYIN->value);
        $this->assertSame('kuaishou', SocialProvider::KUAISHOU->value);
        $this->assertSame('xiaohongshu', SocialProvider::XIAOHONGSHU->value);
    }

    /**
     * 测试 label 方法是否返回正确的中文标签
     */
    #[Test]
    #[TestDox('测试 label 方法是否返回正确的中文标签')]
    public function test_label_method_returns_correct_chinese_labels(): void
    {
        $this->assertSame('微信公众号', SocialProvider::WECHAT_MP->label());
        $this->assertSame('微信应用', SocialProvider::WECHAT_APP->label());
        $this->assertSame('微信小程序', SocialProvider::WECHAT_MINI_PROGRAM->label());
        $this->assertSame('Apple ID', SocialProvider::APPLE->label());
        $this->assertSame('抖音', SocialProvider::DOUYIN->label());
        $this->assertSame('快手', SocialProvider::KUAISHOU->label());
        $this->assertSame('小红书', SocialProvider::XIAOHONGSHU->label());
    }

    /**
     * 测试 JsonSerializable 接口是否正确实现
     */
    #[Test]
    #[TestDox('测试 JsonSerializable 接口是否正确实现')]
    public function test_json_serializable_interface_is_correctly_implemented(): void
    {
        foreach (SocialProvider::cases() as $case) {
            $result = $case->jsonSerialize();
            $this->assertIsArray($result);
            $this->assertArrayHasKey('value', $result);
            $this->assertArrayHasKey('label', $result);
            $this->assertSame($case->value, $result['value']);
            $this->assertSame($case->label(), $result['label']);
        }
    }

    /**
     * 测试 from 方法是否能正确从字符串创建枚举实例
     */
    #[Test]
    #[TestDox('测试 from 方法是否能正确从字符串创建枚举实例')]
    public function test_from_method_can_correctly_create_enum_from_string(): void
    {
        $this->assertSame(SocialProvider::WECHAT_MP, SocialProvider::from('wechat_mp'));
        $this->assertSame(SocialProvider::WECHAT_APP, SocialProvider::from('wechat_app'));
        $this->assertSame(SocialProvider::WECHAT_MINI_PROGRAM, SocialProvider::from('wechat_mini_program'));
        $this->assertSame(SocialProvider::APPLE, SocialProvider::from('apple'));
        $this->assertSame(SocialProvider::DOUYIN, SocialProvider::from('douyin'));
        $this->assertSame(SocialProvider::KUAISHOU, SocialProvider::from('kuaishou'));
        $this->assertSame(SocialProvider::XIAOHONGSHU, SocialProvider::from('xiaohongshu'));
    }

    /**
     * 测试 tryFrom 方法是否能正确处理有效和无效的字符串
     */
    #[Test]
    #[TestDox('测试 tryFrom 方法是否能正确处理有效和无效的字符串')]
    public function test_try_from_method_can_correctly_handle_valid_and_invalid_strings(): void
    {
        // 测试有效字符串
        $this->assertSame(SocialProvider::WECHAT_MP, SocialProvider::tryFrom('wechat_mp'));
        $this->assertSame(SocialProvider::WECHAT_APP, SocialProvider::tryFrom('wechat_app'));

        // 测试无效字符串
        $this->assertNull(SocialProvider::tryFrom('invalid_provider'));
        $this->assertNull(SocialProvider::tryFrom(''));
    }

    /**
     * 测试 cases 方法是否返回所有枚举值
     */
    #[Test]
    #[TestDox('测试 cases 方法是否返回所有枚举值')]
    public function test_cases_method_returns_all_enum_values(): void
    {
        $cases = SocialProvider::cases();
        $this->assertCount(7, $cases);

        $caseValues = array_map(function ($case) {
            return $case->value;
        }, $cases);
        $expectedValues = [
            'wechat_mp',
            'wechat_app',
            'wechat_mini_program',
            'apple',
            'douyin',
            'kuaishou',
            'xiaohongshu',
        ];

        foreach ($expectedValues as $expectedValue) {
            $this->assertContains($expectedValue, $caseValues);
        }
    }
}
