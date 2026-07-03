<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Http\Controllers\Api\V1\User\NotificationController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 通知控制器测试类
 */
#[CoversClass(NotificationController::class)]
#[TestDox('通知控制器测试')]
class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * 创建一个通知记录（可选是否已读）
     */
    protected function createNotification(bool $read = false, array $data = ['message' => 'test']): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => $this->user->getMorphClass(),
            'notifiable_id' => $this->user->id,
            'data' => $data,
            'read_at' => $read ? Carbon::now() : null,
        ]);
    }

    #[Test]
    #[TestDox('测试未认证用户无法访问通知端点')]
    public function test_authentication_required()
    {
        $this->getJson('/api/v1/user/notifications')->assertUnauthorized();
        $this->getJson('/api/v1/user/notifications/unread')->assertUnauthorized();
        $this->postJson('/api/v1/user/notifications/mark-all-read')->assertUnauthorized();
    }

    #[Test]
    #[TestDox('测试获取通知列表')]
    public function test_index_returns_all_notifications()
    {
        $this->createNotification(false);
        $this->createNotification(true);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/notifications');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'data', 'read_at', 'send_at'],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('测试获取未读通知列表')]
    public function test_unread_returns_only_unread_notifications()
    {
        $this->createNotification(false);
        $this->createNotification(false);
        $this->createNotification(true);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/notifications/unread');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        foreach ($response->json('data') as $item) {
            $this->assertNull($item['read_at']);
        }
    }

    #[Test]
    #[TestDox('测试将所有未读通知标记为已读')]
    public function test_mark_all_as_read()
    {
        $this->createNotification(false);
        $this->createNotification(false);
        $this->createNotification(true);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user/notifications/mark-all-read');

        $response->assertOk();
        $response->assertJson(['message' => trans('system.successful_operation')]);
        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }
}
