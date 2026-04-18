<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Announcement;

use App\Enum\StatusSwitch;
use App\Models\Announcement\Announcement;
use App\Models\Announcement\AnnouncementRead;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 公告已读模型测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(AnnouncementRead::class)]
class AnnouncementReadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试模型基本配置
     */
    #[Test]
    #[TestDox('测试模型基本配置')]
    public function test_model_basic_configuration(): void
    {
        $announcementRead = new AnnouncementRead;

        // 测试表名
        $this->assertEquals('announcement_reads', $announcementRead->getTable());

        // 测试可填充字段
        $expectedFillable = [
            'announcement_id', 'user_id', 'user_type',
        ];
        $this->assertEquals($expectedFillable, $announcementRead->getFillable());

        // 测试没有 updated_at 字段
        $this->assertNull($announcementRead->getUpdatedAtColumn());
    }

    /**
     * 测试模型属性转换
     */
    #[Test]
    #[TestDox('测试模型属性转换')]
    public function test_model_attribute_casting(): void
    {
        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 创建公告已读记录
        $announcementRead = AnnouncementRead::create([
            'announcement_id' => $announcement->id,
            'user_id' => 1,
            'user_type' => 'user',
        ]);

        // 测试属性类型
        $this->assertIsInt($announcementRead->id);
        $this->assertIsInt($announcementRead->announcement_id);
        $this->assertIsInt($announcementRead->user_id);
        $this->assertIsString($announcementRead->user_type);
        $this->assertInstanceOf('Illuminate\Support\Carbon', $announcementRead->created_at);
    }

    /**
     * 测试公告关系
     */
    #[Test]
    #[TestDox('测试公告关系')]
    public function test_announcement_relation(): void
    {
        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 创建公告已读记录
        $announcementRead = AnnouncementRead::create([
            'announcement_id' => $announcement->id,
            'user_id' => 1,
            'user_type' => 'user',
        ]);

        // 测试公告关系
        $this->assertInstanceOf(Model::class, $announcementRead->announcement);
        $this->assertInstanceOf(Announcement::class, $announcementRead->announcement);
        $this->assertEquals($announcement->id, $announcementRead->announcement->id);
    }

    /**
     * 测试用户多态关系
     */
    #[Test]
    #[TestDox('测试用户多态关系')]
    public function test_user_morph_relation(): void
    {
        // 创建用户
        $user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'name' => 'Test User',
        ]);

        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 创建公告已读记录
        $announcementRead = AnnouncementRead::create([
            'announcement_id' => $announcement->id,
            'user_id' => $user->id,
            'user_type' => User::class,
        ]);

        // 测试用户多态关系
        $this->assertInstanceOf(Model::class, $announcementRead->user);
        $this->assertInstanceOf(User::class, $announcementRead->user);
        $this->assertEquals($user->id, $announcementRead->user->id);
    }

    /**
     * 测试创建事件监听（增加公告阅读次数）
     */
    #[Test]
    #[TestDox('测试创建事件监听（增加公告阅读次数）')]
    public function test_created_event_increments_read_count(): void
    {
        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
            'read_count' => 0,
        ]);

        // 验证初始阅读次数
        $this->assertEquals(0, $announcement->read_count);

        // 创建公告已读记录
        AnnouncementRead::create([
            'announcement_id' => $announcement->id,
            'user_id' => 1,
            'user_type' => 'user',
        ]);

        // 刷新公告模型
        $announcement->refresh();

        // 验证阅读次数增加
        $this->assertEquals(1, $announcement->read_count);

        // 创建另一条已读记录
        AnnouncementRead::create([
            'announcement_id' => $announcement->id,
            'user_id' => 2,
            'user_type' => 'user',
        ]);

        // 刷新公告模型
        $announcement->refresh();

        // 验证阅读次数再次增加
        $this->assertEquals(2, $announcement->read_count);
    }

    /**
     * 测试记录创建和查询
     */
    #[Test]
    #[TestDox('测试记录创建和查询')]
    public function test_record_creation_and_querying(): void
    {
        // 创建公告
        $announcement = Announcement::create([
            'coverage' => ['user'],
            'title' => '测试公告',
            'content' => '测试公告内容',
            'status' => StatusSwitch::ENABLED,
            'admin_id' => 1,
        ]);

        // 创建公告已读记录
        $announcementRead = AnnouncementRead::create([
            'announcement_id' => $announcement->id,
            'user_id' => 1,
            'user_type' => 'user',
        ]);

        // 测试记录创建
        $this->assertNotNull($announcementRead->id);

        // 测试记录查询
        $foundRecord = AnnouncementRead::where('announcement_id', $announcement->id)->where('user_id', 1)->where('user_type', 'user')->first();
        $this->assertNotNull($foundRecord);
        $this->assertEquals($announcementRead->id, $foundRecord->id);

        // 测试不存在的记录
        $notFoundRecord = AnnouncementRead::where('announcement_id', $announcement->id)->where('user_id', 999)->where('user_type', 'user')->first();
        $this->assertNull($notFoundRecord);
    }
}
