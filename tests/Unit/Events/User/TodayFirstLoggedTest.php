<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Events\User;

use App\Events\User\TodayFirstLogged;
use App\Models\User\LoginHistory;
use Illuminate\Broadcasting\PrivateChannel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * 当日首次登录事件测试
 */
class TodayFirstLoggedTest extends TestCase
{
    /**
     * 测试构造函数是否正确设置了登录历史记录
     */
    #[Test]
    #[TestDox('测试构造函数是否正确设置了登录历史记录')]
    public function test_constructor_sets_login_history(): void
    {
        // 创建一个 LoginHistory 实例
        $loginHistory = new LoginHistory;
        $loginHistory->user_type = 'user';
        $loginHistory->user_id = 1;

        // 创建事件实例
        $event = new TodayFirstLogged($loginHistory);

        // 验证 loginHistory 属性是否正确设置
        $this->assertSame($loginHistory, $event->loginHistory);
    }

    /**
     * 测试 broadcastOn 方法是否返回正确的私有频道
     */
    #[Test]
    #[TestDox('测试 broadcastOn 方法是否返回正确的私有频道')]
    public function test_broadcast_on_returns_correct_private_channel(): void
    {
        // 创建一个 LoginHistory 实例
        $loginHistory = new LoginHistory;
        $loginHistory->user_type = 'user';
        $loginHistory->user_id = 1;

        // 创建事件实例
        $event = new TodayFirstLogged($loginHistory);

        // 调用 broadcastOn 方法
        $channels = $event->broadcastOn();

        // 验证返回值是否为数组
        $this->assertIsArray($channels);
        // 验证数组中是否只有一个元素
        $this->assertCount(1, $channels);
        // 验证返回的频道是否为 PrivateChannel 实例
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }

    /**
     * 测试 broadcastOn 方法是否正确处理用户类型的大小写
     */
    #[Test]
    #[TestDox('测试 broadcastOn 方法是否正确处理用户类型的大小写')]
    public function test_broadcast_on_handles_user_type_case_correctly(): void
    {
        // 创建一个 LoginHistory 实例，使用小写用户类型
        $loginHistory = new LoginHistory;
        $loginHistory->user_type = 'user';
        $loginHistory->user_id = 1;

        // 创建事件实例
        $event = new TodayFirstLogged($loginHistory);

        // 调用 broadcastOn 方法
        $channels = $event->broadcastOn();

        // 验证返回的频道是否为 PrivateChannel 实例
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }
}
