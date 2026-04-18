<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Enum\SocialProvider;
use App\Models\User\UserSocial;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 用户社交账号模型测试
 */
class UserSocialTest extends TestCase
{
    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $userSocial = new UserSocial;

        // 测试表名
        $this->assertEquals('user_socials', $userSocial->getTable());

        // 测试可填充字段
        $expectedFillable = [
            'user_id', 'provider', 'openid', 'unionid', 'access_token', 'refresh_token', 'expiry_at', 'identity_token',
        ];
        $this->assertEquals($expectedFillable, $userSocial->getFillable());

        // 测试隐藏字段
        $this->assertEquals(['user_id'], $userSocial->getHidden());
    }

    /**
     * 测试字段类型转换
     */
    #[Test]
    #[TestDox('测试字段类型转换')]
    public function test_field_casts(): void
    {
        $userSocial = new UserSocial;
        $casts = $userSocial->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals(SocialProvider::class, $casts['provider']);
        $this->assertEquals('string', $casts['openid']);
        $this->assertEquals('string', $casts['unionid']);
        $this->assertEquals('string', $casts['access_token']);
        $this->assertEquals('string', $casts['refresh_token']);
        $this->assertEquals('datetime', $casts['expiry_at']);
        $this->assertEquals('string', $casts['identity_token']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    /**
     * 测试 provider 字段的枚举类型
     */
    #[Test]
    #[TestDox('测试 provider 字段的枚举类型')]
    public function test_provider_enum_type(): void
    {
        $userSocial = new UserSocial;
        $userSocial->provider = SocialProvider::WECHAT_MP;

        $this->assertInstanceOf(SocialProvider::class, $userSocial->provider);
        $this->assertEquals(SocialProvider::WECHAT_MP, $userSocial->provider);
        $this->assertEquals('wechat_mp', $userSocial->provider->value);
    }

    /**
     * 测试 booted 方法
     */
    #[Test]
    #[TestDox('测试 booted 方法')]
    public function test_booted_method(): void
    {
        // 测试 booted 方法是否能正常执行
        $userSocial = new UserSocial;
        $this->assertTrue(true); // 如果方法执行失败会抛出异常
    }
}
