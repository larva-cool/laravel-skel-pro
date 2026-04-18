<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Events\User;

use App\Events\User\EmailReset;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * 邮箱重置事件测试
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
#[CoversClass(EmailReset::class)]
class EmailResetTest extends TestCase
{
    #[Test]
    public function test_implements_correct_interfaces()
    {
        $user = Mockery::mock(User::class);
        $event = new EmailReset($user);

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
        $this->assertInstanceOf(ShouldHandleEventsAfterCommit::class, $event);
        $this->assertInstanceOf(ShouldQueue::class, $event);
    }

    #[Test]
    public function test_constructor_sets_user_property()
    {
        $user = Mockery::mock(User::class);
        $event = new EmailReset($user);

        $this->assertSame($user, $event->user);
    }

    #[Test]
    public function test_broadcast_on_returns_private_channel()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(123);

        $event = new EmailReset($user);
        $channels = $event->broadcastOn();

        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
    }

    #[Test]
    public function test_broadcast_on_returns_channel_with_correct_user_id()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(456);

        $event = new EmailReset($user);
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
