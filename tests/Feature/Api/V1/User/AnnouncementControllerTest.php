<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\StatusSwitch;
use App\Http\Controllers\Api\V1\User\AnnouncementController;
use App\Models\Announcement\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 用户公告控制器测试类
 */
#[CoversClass(AnnouncementController::class)]
#[TestDox('用户公告控制器测试')]
class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function createAnnouncement(array $overrides = []): Announcement
    {
        return Announcement::create(array_merge([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试内容',
            'status' => StatusSwitch::ENABLED->value,
            'admin_id' => 1,
            'effective_time_type' => 0,
        ], $overrides));
    }

    #[Test]
    #[TestDox('测试未认证用户无法访问公告端点')]
    public function test_authentication_required()
    {
        $this->getJson('/api/v1/user/announcement')->assertUnauthorized();
        $this->getJson('/api/v1/user/announcement/1')->assertUnauthorized();
    }

    #[Test]
    #[TestDox('测试获取公告列表')]
    public function test_index_returns_active_announcements()
    {
        $this->createAnnouncement(['title' => '公告1']);
        $this->createAnnouncement(['title' => '公告2']);
        // 禁用状态的公告
        $this->createAnnouncement([
            'title' => '禁用的公告',
            'status' => StatusSwitch::DISABLED->value,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/announcement');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'is_read', 'created_at'],
            ],
            'links',
            'meta',
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    #[TestDox('测试获取公告详情并标记为已读')]
    public function test_show_marks_as_read()
    {
        $announcement = $this->createAnnouncement();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/announcement/'.$announcement->id);

        $response->assertOk();
        $response->assertJsonStructure(['id', 'title', 'content', 'is_read']);
        $response->assertJson([
            'id' => $announcement->id,
            'is_read' => true,
        ]);
        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $this->user->id,
            'user_type' => 'user',
        ]);
    }

    #[Test]
    #[TestDox('测试重复访问详情不会创建多条已读记录')]
    public function test_show_does_not_duplicate_read_record()
    {
        $announcement = $this->createAnnouncement();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/announcement/'.$announcement->id);
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/announcement/'.$announcement->id);

        $this->assertEquals(1, $announcement->reads()->where('user_id', $this->user->id)->count());
    }

    #[Test]
    #[TestDox('测试查询不存在的公告返回404')]
    public function test_show_returns_404_when_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user/announcement/9999999');

        $response->assertNotFound();
    }
}
