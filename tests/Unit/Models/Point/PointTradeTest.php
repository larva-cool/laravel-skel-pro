<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Point;

use App\Enum\PointType;
use App\Models\Point\PointRecord;
use App\Models\Point\PointTrade;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 积分明细测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(PointTrade::class)]
class PointTradeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $fillable = (new PointTrade)->getFillable();

        $this->assertEquals([
            'user_id', 'points', 'description', 'type', 'source_id', 'source_type', 'expired_at',
        ], $fillable);
    }

    #[Test]
    #[TestDox('测试隐藏属性')]
    public function test_hidden_attributes()
    {
        $hidden = (new PointTrade)->getHidden();

        $this->assertEquals(['user_id'], $hidden);
    }

    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_casts()
    {
        $casts = (new PointTrade)->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('integer', $casts['points']);
        $this->assertEquals('string', $casts['description']);
        $this->assertEquals(PointType::class, $casts['type']);
        $this->assertEquals('integer', $casts['source_id']);
        $this->assertEquals('string', $casts['source_type']);
        $this->assertEquals('datetime', $casts['expired_at']);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    #[Test]
    #[TestDox('测试追加属性')]
    public function test_appends_attributes()
    {
        $appends = (new PointTrade)->getAppends();

        $this->assertEquals(['type_label'], $appends);
    }

    #[Test]
    #[TestDox('测试类型标签访问器')]
    public function test_type_label_accessor()
    {
        $trade = new PointTrade;
        $trade->type = PointType::TYPE_SIGN_IN;

        $this->assertEquals('签到', $trade->type_label);
    }

    #[Test]
    #[TestDox('测试来源关联关系')]
    public function test_source_relation()
    {
        $trade = new PointTrade;
        $relation = $trade->source();

        $this->assertInstanceOf(MorphTo::class, $relation);
    }

    #[Test]
    #[TestDox('测试创建事件会为正积分创建积分记录')]
    public function test_created_event_creates_point_record()
    {
        // Create a PointTrade with positive points
        $trade = PointTrade::create([
            'user_id' => 1,
            'points' => 10,
            'description' => 'Test Description',
            'type' => PointType::TYPE_SIGN_IN,
            'source_id' => 1,
            'source_type' => 'App\\Models\\User',
        ]);

        // Verify the trade was created
        $this->assertNotNull($trade);
        $this->assertEquals(1, $trade->user_id);
        $this->assertEquals(10, $trade->points);
        $this->assertEquals('Test Description', $trade->description);
        $this->assertEquals(PointType::TYPE_SIGN_IN, $trade->type);

        // Verify a PointRecord was created
        $pointRecord = PointRecord::where('user_id', 1)->where('points', 10)->first();
        $this->assertNotNull($pointRecord);
        $this->assertEquals('Test Description', $pointRecord->description);
    }

    #[Test]
    #[TestDox('测试创建事件不会为负积分创建积分记录')]
    public function test_created_event_does_not_create_point_record_for_negative_points()
    {
        // Create a PointTrade with negative points
        $trade = PointTrade::create([
            'user_id' => 1,
            'points' => -5,
            'description' => 'Test Description',
            'type' => PointType::TYPE_SIGN_IN,
            'source_id' => 1,
            'source_type' => 'App\\Models\\User',
        ]);

        // Verify the trade was created
        $this->assertNotNull($trade);
        $this->assertEquals(1, $trade->user_id);
        $this->assertEquals(-5, $trade->points);

        // Verify no PointRecord was created
        $pointRecord = PointRecord::where('user_id', 1)->where('points', -5)->first();
        $this->assertNull($pointRecord);
    }

    #[Test]
    #[TestDox('测试创建事件不会为零积分创建积分记录')]
    public function test_created_event_does_not_create_point_record_for_zero_points()
    {
        // Create a PointTrade with zero points
        $trade = PointTrade::create([
            'user_id' => 1,
            'points' => 0,
            'description' => 'Test Description',
            'type' => PointType::TYPE_SIGN_IN,
            'source_id' => 1,
            'source_type' => 'App\\Models\\User',
        ]);

        // Verify the trade was created
        $this->assertNotNull($trade);
        $this->assertEquals(1, $trade->user_id);
        $this->assertEquals(0, $trade->points);

        // Verify no PointRecord was created
        $pointRecord = PointRecord::where('user_id', 1)->where('points', 0)->first();
        $this->assertNull($pointRecord);
    }
}
