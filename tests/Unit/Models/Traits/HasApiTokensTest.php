<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Traits;

use App\Models\Traits\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 测试类，用于测试 HasApiTokens 特征
 */
class TestModel
{
    use HasApiTokens;

    /**
     * 模拟 tokens 方法
     */
    public function tokens()
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('where')->andReturnSelf();
        $mock->shouldReceive('delete')->andReturn(1);

        return $mock;
    }

    /**
     * 模拟父类的 createBaseToken 方法
     */
    public function createBaseToken(string $name, array $abilities = ['*'], $expiresAt = null): NewAccessToken
    {
        // Create a mock for PersonalAccessToken that handles setAttribute and getAttribute
        $tokenMock = Mockery::mock(PersonalAccessToken::class);
        $tokenMock->shouldReceive('setAttribute')->andReturnNull();
        $tokenMock->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $tokenMock->shouldReceive('getAttribute')->with('expires_at')->andReturn($expiresAt);
        $tokenMock->shouldReceive('__get')->with('id')->andReturn(1);
        $tokenMock->shouldReceive('__get')->with('expires_at')->andReturn($expiresAt);

        // Create a simple implementation of NewAccessToken
        $newTokenMock = new class($tokenMock, 'test-token') extends NewAccessToken
        {
            public function __construct($accessToken, $plainTextToken)
            {
                $this->accessToken = $accessToken;
                $this->plainTextToken = $plainTextToken;
            }
        };

        return $newTokenMock;
    }
}

/**
 * API 令牌管理特征测试
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[CoversClass(HasApiTokens::class)]
class HasApiTokensTest extends TestCase
{
    /**
     * 测试 flushTokenByName 方法
     */
    #[Test]
    public function test_flush_token_by_name()
    {
        // 创建测试模型实例
        $model = new TestModel;

        // 测试方法
        $result = $model->flushTokenByName('test-device');
        $this->assertTrue($result);
    }

    /**
     * 测试 flushTokens 方法
     */
    #[Test]
    public function test_flush_tokens()
    {
        // 创建测试模型实例
        $model = new TestModel;

        // 测试方法
        $result = $model->flushTokens();
        $this->assertTrue($result);
    }

    /**
     * 测试 createDeviceToken 方法
     */
    #[Test]
    public function test_create_device_token()
    {
        // 模拟 SettingManagerService
        $settingServiceMock = Mockery::mock('App\Services\SettingManagerService');
        $settingServiceMock->shouldReceive('get')->with('user.only_one_device_login', false)->andReturn(false);
        $settingServiceMock->shouldReceive('get')->with('user.token_expiration', 525600)->andReturn(1440);

        // 绑定到服务容器
        $this->app->instance('App\Services\SettingManagerService', $settingServiceMock);

        // 创建测试模型实例
        $model = new TestModel;

        // 测试方法
        $result = $model->createDeviceToken('test-device', ['*']);

        // 验证返回值结构
        $this->assertIsArray($result);
        $this->assertArrayHasKey('token_id', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals('test-token', $result['access_token']);
    }

    /**
     * 清理模拟的对象
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
