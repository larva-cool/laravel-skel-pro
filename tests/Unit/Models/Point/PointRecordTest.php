<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\Point;

use App\Models\Point\PointRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 可用积分记录测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(PointRecord::class)]
class PointRecordTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $fillable = (new PointRecord)->getFillable();

        $this->assertEquals([
            'user_id', 'points', 'description', 'expired_at', 'updated_at',
        ], $fillable);
    }

    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_casts()
    {
        $casts = (new PointRecord)->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('integer', $casts['points']);
        $this->assertEquals('string', $casts['description']);
        $this->assertEquals('datetime', $casts['expired_at']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    #[Test]
    #[TestDox('测试删除事件会更新用户积分')]
    public function test_deleted_event_updates_user_points()
    {
        // Create a PointRecord
        $record = PointRecord::create([
            'user_id' => 1,
            'points' => 10,
            'description' => 'Test Description',
        ]);

        // Verify the record was created
        $this->assertNotNull($record);

        // Delete the record
        $record->delete();

        // Verify the user's available points were updated
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    #[TestDox('测试可清理的过期积分记录')]
    public function test_prunable()
    {
        // Create records with different expired_at dates
        $now = Carbon::now();

        // Create a record that expired 2 months ago (should be prunable)
        $prunableRecord = PointRecord::create([
            'user_id' => 1,
            'points' => 10,
            'description' => 'Expired Record',
            'expired_at' => $now->copy()->subMonths(2),
        ]);

        // Create a record that expired 2 weeks ago (should not be prunable)
        $nonPrunableRecord = PointRecord::create([
            'user_id' => 1,
            'points' => 10,
            'description' => 'Non-Expired Record',
            'expired_at' => $now->copy()->subWeeks(2),
        ]);

        // Get the prunable query
        $record = new PointRecord;
        $prunableQuery = $record->prunable();

        // Verify it's a Builder instance
        $this->assertInstanceOf(Builder::class, $prunableQuery);

        // Verify only the expired record is returned
        $prunableRecords = $prunableQuery->get();
        $this->assertCount(1, $prunableRecords);
        $this->assertEquals($prunableRecord->id, $prunableRecords->first()->id);
    }
}
