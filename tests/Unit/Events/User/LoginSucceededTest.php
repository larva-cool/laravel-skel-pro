<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Events\User;

use App\Events\User\LoginSucceeded;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * 登录成功事件测试
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[CoversClass(LoginSucceeded::class)]
class LoginSucceededTest extends TestCase
{
    #[Test]
    public function test_implements_correct_interfaces()
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(123);

        $event = new LoginSucceeded($user, '127.0.0.1', 8080, 'Mozilla/5.0');

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
    }

    #[Test]
    public function test_constructor_sets_all_properties()
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(123);

        $ip = '192.168.1.1';
        $port = 9090;
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $event = new LoginSucceeded($user, $ip, $port, $userAgent);

        $this->assertSame($user, $event->user);
        $this->assertEquals($ip, $event->ip);
        $this->assertEquals($port, $event->port);
        $this->assertEquals($userAgent, $event->userAgent);
    }

    #[Test]
    public function test_broadcast_on_returns_private_channel()
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->id = 123;

        $event = new LoginSucceeded($user, '127.0.0.1', 8080, 'Mozilla/5.0');
        $channels = $event->broadcastOn();

        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }

    #[Test]
    public function test_broadcast_on_returns_channel_with_correct_user_id()
    {
        $user = Mockery::mock(Authenticatable::class);
        $user->id = 456;

        $event = new LoginSucceeded($user, '127.0.0.1', 8080, 'Mozilla/5.0');
        $channels = $event->broadcastOn();

        $channel = $channels[0];
        $this->assertEquals('private-User.456', $channel->name);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
