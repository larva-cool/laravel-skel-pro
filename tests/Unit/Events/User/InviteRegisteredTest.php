<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Events\User;

use App\Events\User\InviteRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 邀请注册事件测试
 */
#[CoversClass(InviteRegistered::class)]
class InviteRegisteredTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试事件实例化
     */
    #[Test]
    public function test_event_instantiation()
    {
        // 创建用户模型模拟
        $userMock = Mockery::mock(User::class);
        $inviteCode = 'TEST_INVITE_CODE';

        // 实例化事件
        $event = new InviteRegistered($userMock, $inviteCode);

        // 验证属性设置正确
        $this->assertSame($userMock, $event->user);
        $this->assertSame($inviteCode, $event->inviteCode);
    }

    /**
     * 测试事件是否可分发
     */
    #[Test]
    public function test_event_is_dispatchable()
    {
        // 创建用户模型模拟
        $userMock = Mockery::mock(User::class);
        $inviteCode = 'TEST_INVITE_CODE';

        // 验证事件可以被分发（不会抛出异常）
        $result = InviteRegistered::dispatch($userMock, $inviteCode);
        $this->assertIsArray($result);
    }

    /**
     * 清理模拟对象
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
