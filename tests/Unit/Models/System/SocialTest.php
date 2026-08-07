<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Enums\SocialProvider;
use App\Models\Admin\Admin;
use App\Models\System\Social;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Social 模型单元测试
 */
#[CoversClass(Social::class)]
#[Group('models')]
#[Group('social')]
class SocialTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('provider 属性正确 cast 为 SocialProvider 枚举')]
    public function provider_is_cast_to_enum(): void
    {
        $user = User::factory()->create();
        $social = Social::create([
            'user_id' => $user->id,
            'user_type' => User::class,
            'provider' => SocialProvider::WECHAT_MP,
            'openid' => 'wx_openid_123',
        ]);

        $this->assertInstanceOf(SocialProvider::class, $social->provider);
        $this->assertSame(SocialProvider::WECHAT_MP, $social->provider);
    }

    #[Test]
    #[TestDox('user 关系返回多态关联的用户')]
    public function user_returns_morph_to_relation(): void
    {
        $user = User::factory()->create();
        $social = Social::create([
            'user_id' => $user->id,
            'user_type' => User::class,
            'provider' => SocialProvider::APPLE,
            'openid' => 'apple_id_456',
        ]);

        $this->assertInstanceOf(User::class, $social->user);
        $this->assertSame($user->id, $social->user->id);
    }

    #[Test]
    #[TestDox('user 关系也支持 Admin 模型')]
    public function user_supports_admin_model(): void
    {
        $admin = Admin::query()->where('username', 'admin')->first();
        $social = Social::create([
            'user_id' => $admin->id,
            'user_type' => Admin::class,
            'provider' => SocialProvider::DOUYIN,
            'openid' => 'douyin_789',
        ]);

        $this->assertInstanceOf(Admin::class, $social->user);
        $this->assertSame($admin->id, $social->user->id);
    }

    #[Test]
    #[TestDox('hidden 属性隐藏 user_id 和 user_type')]
    public function hidden_attributes(): void
    {
        $user = User::factory()->create();
        $social = Social::create([
            'user_id' => $user->id,
            'user_type' => User::class,
            'provider' => SocialProvider::WECHAT_MP,
            'openid' => 'test',
        ]);

        $array = $social->toArray();

        $this->assertArrayNotHasKey('user_id', $array);
        $this->assertArrayNotHasKey('user_type', $array);
    }

    #[Test]
    #[TestDox('expiry_at 正确 cast 为 datetime')]
    public function expiry_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create();
        $social = Social::create([
            'user_id' => $user->id,
            'user_type' => User::class,
            'provider' => SocialProvider::WECHAT_MP,
            'openid' => 'test',
            'expiry_at' => '2025-12-31 23:59:59',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $social->expiry_at);
    }
}
