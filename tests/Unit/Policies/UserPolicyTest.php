<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\SettingManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(UserPolicy::class)]
#[TestDox('用户策略测试')]
class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected ?User $user;

    protected ?User $guest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy;
        $this->user = User::factory()->create();
        $this->guest = null;
    }

    #[Test]
    #[TestDox('测试用户注册策略 - $name')]
    #[DataProvider('registerPolicyDataProvider')]
    public function test_register_policy(string $name, bool $settingValue, bool $expectedResult)
    {
        $this->mockSettingService('user.enable_register', $settingValue);

        $result = $this->policy->register($this->user);
        $resultGuest = $this->policy->register($this->guest);

        $this->assertEquals($expectedResult, $result->allowed());
        $this->assertEquals($expectedResult, $resultGuest->allowed());
    }

    #[Test]
    #[TestDox('测试手机号注册策略 - $name')]
    #[DataProvider('registerPolicyDataProvider')]
    public function test_phone_register_policy(string $name, bool $settingValue, bool $expectedResult)
    {
        $this->mockSettingService('user.enable_phone_register', $settingValue);

        $result = $this->policy->phoneRegister($this->user);
        $resultGuest = $this->policy->phoneRegister($this->guest);

        $this->assertEquals($expectedResult, $result->allowed());
        $this->assertEquals($expectedResult, $resultGuest->allowed());
    }

    #[Test]
    #[TestDox('测试邮箱注册策略 - $name')]
    #[DataProvider('registerPolicyDataProvider')]
    public function test_email_register_policy(string $name, bool $settingValue, bool $expectedResult)
    {
        $this->mockSettingService('user.enable_email_register', $settingValue);

        $result = $this->policy->emailRegister($this->user);
        $resultGuest = $this->policy->emailRegister($this->guest);

        $this->assertEquals($expectedResult, $result->allowed());
        $this->assertEquals($expectedResult, $resultGuest->allowed());
    }

    #[Test]
    #[TestDox('测试微信登录策略 - $name')]
    #[DataProvider('loginPolicyDataProvider')]
    public function test_wechat_login_policy(string $name, bool $settingValue, bool $expectedResult)
    {
        $this->mockSettingService('user.enable_wechat_login', $settingValue);

        $result = $this->policy->wechatLogin($this->user);
        $resultGuest = $this->policy->wechatLogin($this->guest);

        $this->assertEquals($expectedResult, $result->allowed());
        $this->assertEquals($expectedResult, $resultGuest->allowed());
    }

    #[Test]
    #[TestDox('测试手机号登录策略 - $name')]
    #[DataProvider('loginPolicyDataProvider')]
    public function test_phone_login_policy(string $name, bool $settingValue, bool $expectedResult)
    {
        $this->mockSettingService('user.enable_phone_login', $settingValue);

        $result = $this->policy->phoneLogin($this->user);
        $resultGuest = $this->policy->phoneLogin($this->guest);

        $this->assertEquals($expectedResult, $result->allowed());
        $this->assertEquals($expectedResult, $resultGuest->allowed());
    }

    #[Test]
    #[TestDox('测试密码登录策略 - $name')]
    #[DataProvider('loginPolicyDataProvider')]
    public function test_password_login_policy(string $name, bool $settingValue, bool $expectedResult)
    {
        $this->mockSettingService('user.enable_password_login', $settingValue);

        $result = $this->policy->passwordLogin($this->user);
        $resultGuest = $this->policy->passwordLogin($this->guest);

        $this->assertEquals($expectedResult, $result->allowed());
        $this->assertEquals($expectedResult, $resultGuest->allowed());
    }

    /**
     * 模拟设置服务
     */
    protected function mockSettingService(string $key, mixed $value): void
    {
        $settingService = Mockery::mock(SettingManagerService::class);
        $settingService->shouldReceive('get')
            ->with($key, true)
            ->andReturn($value);

        $this->app->instance(SettingManagerService::class, $settingService);
    }

    /**
     * 注册策略数据提供者
     */
    public static function registerPolicyDataProvider(): array
    {
        return [
            ['允许注册', true, true],
            ['不允许注册', false, false],
        ];
    }

    /**
     * 登录策略数据提供者
     */
    public static function loginPolicyDataProvider(): array
    {
        return [
            ['允许登录', true, true],
            ['不允许登录', false, false],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
