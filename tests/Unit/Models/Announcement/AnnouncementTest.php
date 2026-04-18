<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Announcement;

use App\Enum\StatusSwitch;
use App\Models\Announcement\Announcement;
use App\Models\Announcement\AnnouncementRead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 公告模型测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(Announcement::class)]
class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $announcement = new Announcement;

        // 测试表名
        $this->assertEquals('announcements', $announcement->getTable());

        // 测试可填充字段
        $expectedFillable = [
            'coverage', 'title', 'content', 'image', 'jump_url', 'status', 'admin_id', 'read_count', 'effective_time_type', 'effective_start_time', 'effective_end_time',
        ];
        $this->assertEquals($expectedFillable, $announcement->getFillable());

        // 测试默认属性
        $this->assertEquals(0, $announcement->getAttributes()['read_count']);
        $this->assertEquals(0, $announcement->getAttributes()['effective_time_type']);
        $this->assertEquals(StatusSwitch::ENABLED->value, $announcement->getAttributes()['status']);
    }

    /**
     * 测试模型属性转换
     */
    #[Test]
    #[TestDox('测试模型属性转换')]
    public function test_model_attribute_casting(): void
    {
        $announcement = Announcement::create([
            'coverage' => ['user', 'admin'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'image' => 'test.jpg',
            'jump_url' => 'https://example.com',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
            'effective_time_type' => 0,
        ]);

        // 测试 coverage 字段转换为数组
        $this->assertIsArray($announcement->coverage);
        $this->assertEquals(['user', 'admin'], $announcement->coverage);

        // 测试 status 字段转换为枚举
        $this->assertInstanceOf(StatusSwitch::class, $announcement->status);
        $this->assertEquals(StatusSwitch::ENABLED, $announcement->status);

        // 测试其他字段类型
        $this->assertIsString($announcement->title);
        $this->assertIsString($announcement->content);
        $this->assertIsString($announcement->jump_url);
        $this->assertIsInt($announcement->admin_id);
        $this->assertIsInt($announcement->effective_time_type);
    }

    /**
     * 测试 reads 关系
     */
    #[Test]
    #[TestDox('测试 reads 关系')]
    public function test_reads_relation(): void
    {
        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'admin_id' => 1,
        ]);

        // 创建已读记录
        $announcement->reads()->create([
            'user_id' => 1,
            'user_type' => 'user',
        ]);

        // 测试关系
        $this->assertInstanceOf(Model::class, $announcement->reads->first());
        $this->assertInstanceOf(AnnouncementRead::class, $announcement->reads->first());
        $this->assertEquals(1, $announcement->reads->count());
    }

    /**
     * 测试 active 作用域
     */
    #[Test]
    #[TestDox('测试 active 作用域')]
    public function test_active_scope(): void
    {
        // 创建有效的公告（立即生效）
        $activeAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '有效的公告',
            'content' => '有效的公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
            'effective_time_type' => 0,
        ]);

        // 创建有效的公告（定时生效）
        $scheduledAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '定时生效的公告',
            'content' => '定时生效的公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
            'effective_time_type' => 1,
            'effective_start_time' => Carbon::now()->subHour(),
            'effective_end_time' => Carbon::now()->addHour(),
        ]);

        // 创建无效的公告（禁用）
        $disabledAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '禁用的公告',
            'content' => '禁用的公告内容',
            'status' => StatusSwitch::DISABLED,
            'admin_id' => 1,
        ]);

        // 创建无效的公告（未到生效时间）
        $futureAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '未来的公告',
            'content' => '未来的公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
            'effective_time_type' => 1,
            'effective_start_time' => Carbon::now()->addHour(),
            'effective_end_time' => Carbon::now()->addHours(2),
        ]);

        // 测试 active 作用域
        $activeAnnouncements = Announcement::query()->active('user')->get();
        $this->assertCount(2, $activeAnnouncements);
        $this->assertTrue($activeAnnouncements->contains($activeAnnouncement));
        $this->assertTrue($activeAnnouncements->contains($scheduledAnnouncement));
        $this->assertFalse($activeAnnouncements->contains($disabledAnnouncement));
        $this->assertFalse($activeAnnouncements->contains($futureAnnouncement));
    }

    /**
     * 测试 markAsRead 方法
     */
    #[Test]
    #[TestDox('测试 markAsRead 方法')]
    public function test_mark_as_read(): void
    {
        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'admin_id' => 1,
        ]);

        // 测试标记为已读
        $result = $announcement->markAsRead(1, 'user');
        $this->assertTrue($result);

        // 测试已读记录是否创建
        $readRecord = AnnouncementRead::where('announcement_id', $announcement->id)->where('user_id', 1)->where('user_type', 'user')->first();
        $this->assertNotNull($readRecord);

        // 测试重复标记为已读
        $result = $announcement->markAsRead(1, 'user');
        $this->assertTrue($result);

        // 测试已读记录数量（应该仍然是1）
        $readCount = AnnouncementRead::where('announcement_id', $announcement->id)->count();
        $this->assertEquals(1, $readCount);
    }

    /**
     * 测试 getUnreadCount 方法
     */
    #[Test]
    #[TestDox('测试 getUnreadCount 方法')]
    public function test_get_unread_count(): void
    {
        // 创建两个公告
        Announcement::create([
            'coverage' => ['user'],
            'title' => '公告1',
            'content' => '公告1内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        Announcement::create([
            'coverage' => ['user'],
            'title' => '公告2',
            'content' => '公告2内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 测试未读公告数量（应该是2）
        $unreadCount = Announcement::getUnreadCount(1, 'user');
        $this->assertEquals(2, $unreadCount);

        // 标记一个公告为已读
        $announcement = Announcement::first();
        $announcement->markAsRead(1, 'user');

        // 测试未读公告数量（应该是1）
        $unreadCount = Announcement::getUnreadCount(1, 'user');
        $this->assertEquals(1, $unreadCount);

        // 标记所有公告为已读
        $announcement = Announcement::skip(1)->first();
        $announcement->markAsRead(1, 'user');

        // 测试未读公告数量（应该是0）
        $unreadCount = Announcement::getUnreadCount(1, 'user');
        $this->assertEquals(0, $unreadCount);
    }

    /**
     * 测试 getLastNotice 方法
     */
    #[Test]
    #[TestDox('测试 getLastNotice 方法')]
    public function test_get_last_notice(): void
    {
        // 创建两个公告
        Announcement::create([
            'coverage' => ['user'],
            'title' => '公告1',
            'content' => '公告1内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        $latestAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '公告2',
            'content' => '公告2内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 测试获取最后一条公告
        $lastNotice = Announcement::getLastNotice('user');
        $this->assertNotNull($lastNotice);
        $this->assertEquals($latestAnnouncement->id, $lastNotice->id);

        // 创建一个禁用的公告
        Announcement::create([
            'coverage' => ['user'],
            'title' => '禁用的公告',
            'content' => '禁用的公告内容',
            'status' => StatusSwitch::DISABLED,
            'admin_id' => 1,
        ]);

        // 测试获取最后一条公告（应该仍然是之前的最新公告）
        $lastNotice = Announcement::getLastNotice('user');
        $this->assertNotNull($lastNotice);
        $this->assertEquals($latestAnnouncement->id, $lastNotice->id);
    }

    /**
     * 测试 getUnreadLastNotice 方法
     */
    #[Test]
    #[TestDox('测试 getUnreadLastNotice 方法')]
    public function test_get_unread_last_notice(): void
    {
        // 创建两个公告
        $firstAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '公告1',
            'content' => '公告1内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        $secondAnnouncement = Announcement::create([
            'coverage' => ['user'],
            'title' => '公告2',
            'content' => '公告2内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 测试获取未读的最后一条公告（应该是公告2）
        $unreadLastNotice = Announcement::getUnreadLastNotice(1, 'user');
        $this->assertNotNull($unreadLastNotice);
        $this->assertEquals($secondAnnouncement->id, $unreadLastNotice->id);

        // 标记公告2为已读
        $secondAnnouncement->markAsRead(1, 'user');

        // 测试获取未读的最后一条公告（应该是公告1）
        $unreadLastNotice = Announcement::getUnreadLastNotice(1, 'user');
        $this->assertNotNull($unreadLastNotice);
        $this->assertEquals($firstAnnouncement->id, $unreadLastNotice->id);

        // 标记公告1为已读
        $firstAnnouncement->markAsRead(1, 'user');

        // 测试获取未读的最后一条公告（应该返回最新的公告，即使已读）
        $unreadLastNotice = Announcement::getUnreadLastNotice(1, 'user');
        $this->assertNotNull($unreadLastNotice);
        $this->assertEquals($secondAnnouncement->id, $unreadLastNotice->id);
    }
}
