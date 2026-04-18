<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Enum\PointType;
use App\Exceptions\InsufficientPointsException;
use App\Models\Point\PointRecord;
use App\Models\Point\PointTrade;
use App\Models\User;
use App\Support\PointHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(PointHelper::class)]
#[TestDox('测试 PointHelper 类的方法')]
class PointHelperTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    /**
     * @var User 测试source用户
     */
    protected User $source;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试用户
        $this->user = User::factory()->create([
            'available_points' => 0,
        ]);

        // 创建测试source用户
        $this->source = User::factory()->create();
    }

    #[Test]
    #[TestDox('测试 getTypeName 方法获取积分类型名称')]
    public function test_get_type_name()
    {
        $this->assertEquals('签到', PointType::TYPE_SIGN_IN->label());
        $this->assertEquals('邀请注册', PointType::TYPE_INVITE_REGISTER->label());
        $this->assertEquals('设置头像', PointType::TYPE_SET_UP_AVATAR->label());
        $this->assertEquals('过期回收', PointType::TYPE_RECOVERY->label());
        $this->assertEquals('', PointType::tryFrom('unknown_type'));
    }

    #[Test]
    #[TestDox('测试 incr 方法增加用户积分')]
    public function test_incr_points()
    {
        // 增加积分
        $trade = PointHelper::incr(
            $this->user->id,
            100,
            $this->source,
            PointType::TYPE_SIGN_IN,
            '签到获得积分'
        );

        // 验证交易记录
        $this->assertInstanceOf(PointTrade::class, $trade);
        $this->assertEquals($this->user->id, $trade->user_id);
        $this->assertEquals(100, $trade->points);
        $this->assertEquals($this->source->id, $trade->source_id);
        $this->assertEquals('user', $trade->source_type);
        $this->assertEquals(PointType::TYPE_SIGN_IN, $trade->type);
        $this->assertEquals('签到获得积分', $trade->description);
        $this->assertInstanceOf(Carbon::class, $trade->expired_at);

        // 验证积分记录
        $pointRecord = PointRecord::query()
            ->where('user_id', $this->user->id)
            ->first();
        $this->assertNotNull($pointRecord);
        $this->assertEquals(100, $pointRecord->points);

        // 验证用户积分总额已更新
        $this->user->refresh();
        $this->assertEquals(100, $this->user->available_points);
    }

    #[Test]
    #[TestDox('测试 decr 方法减少用户积分')]
    public function test_decr_points()
    {
        // 先增加积分
        PointHelper::incr(
            $this->user->id,
            100,
            $this->source,
            PointType::TYPE_SIGN_IN,
            '签到获得积分'
        );

        // 减少积分
        $result = PointHelper::decr(
            $this->user->id,
            50,
            $this->source,
            PointType::TYPE_SIGN_IN,
            '消费积分'
        );

        // 验证操作成功
        $this->assertTrue($result);

        // 验证交易记录
        $trade = PointTrade::query()
            ->where('user_id', $this->user->id)
            ->where('points', -50)
            ->first();
        $this->assertNotNull($trade);
        $this->assertEquals(PointType::TYPE_SIGN_IN, $trade->type);
        $this->assertEquals('消费积分', $trade->description);

        // 验证剩余积分记录
        $pointRecord = PointRecord::query()
            ->where('user_id', $this->user->id)
            ->first();
        $this->assertNotNull($pointRecord);
        $this->assertEquals(50, $pointRecord->points);

        // 验证用户积分总额已更新
        $this->user->refresh();
        $this->assertEquals(50, $this->user->available_points);
    }

    #[Test]
    #[TestDox('测试 decr 方法减少用户积分时积分不足异常')]
    public function test_decr_points_insufficient()
    {
        // 先增加少量积分
        PointHelper::incr(
            $this->user->id,
            30,
            $this->source,
            PointType::TYPE_SIGN_IN,
            '签到获得积分'
        );

        // 期望抛出积分不足异常
        $this->expectException(InsufficientPointsException::class);
        $this->expectExceptionMessage('积分不足，当前可用积分: 30');

        // 尝试减少更多积分
        PointHelper::decr(
            $this->user->id,
            50,
            $this->user,
            PointType::TYPE_SIGN_IN,
            '消费积分'
        );

        // 验证积分记录未变化
        $pointRecord = PointRecord::query()
            ->where('user_id', $this->user->id)
            ->first();
        $this->assertNotNull($pointRecord);
        $this->assertEquals(30, $pointRecord->points);

        // 验证用户积分总额未变化
        $this->user->refresh();
        $this->assertEquals(30, $this->user->available_points);

        // 验证没有创建负积分的交易记录
        $negativeTrade = PointTrade::query()
            ->where('user_id', $this->user->id)
            ->where('points', -50)
            ->first();
        $this->assertNull($negativeTrade);
    }

    #[Test]
    #[TestDox('测试 handlingExpired 方法处理过期积分')]
    public function test_handling_expired()
    {
        // 创建未过期积分记录
        $expiredAt = Carbon::now()->addDay();
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 100,
            'expired_at' => $expiredAt,
            'description' => '测试未过期积分',
        ]);

        // 更新用户积分总额
        PointHelper::updatePointTotal($this->user->id);
        $this->user->refresh();
        $this->assertEquals(100, $this->user->available_points);

        // 将积分记录设置为过期
        $expiredAt = Carbon::now()->subDay();
        PointRecord::query()
            ->where('user_id', $this->user->id)
            ->update(['expired_at' => $expiredAt]);

        // 处理过期积分
        PointHelper::handlingExpired($this->user->id);

        // 验证过期积分已被回收
        $this->user->refresh();
        $this->assertEquals(0, $this->user->available_points);

        // 验证没有过期积分记录
        $expiredRecords = PointRecord::query()
            ->where('user_id', $this->user->id)
            ->where('expired_at', '<', Carbon::now())
            ->get();
        $this->assertEmpty($expiredRecords);

        // 验证回收交易记录已创建
        $recoveryTrade = PointTrade::query()
            ->where('user_id', $this->user->id)
            ->where('type', PointType::TYPE_RECOVERY)
            ->first();
        $this->assertNotNull($recoveryTrade);
        $this->assertEquals(-100, $recoveryTrade->points);
        $this->assertEquals('过期回收', $recoveryTrade->description);
    }

    #[Test]
    #[TestDox('测试 updatePointTotal 方法更新用户积分总额')]
    public function test_update_point_total()
    {
        // 创建积分记录
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'expired_at' => Carbon::now()->addDays(30),
            'description' => '测试积分记录1',
        ]);
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'expired_at' => Carbon::now()->addDays(30),
            'description' => '测试积分记录2',
        ]);
        // 创建过期积分记录
        PointRecord::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'expired_at' => Carbon::now()->subDay(),
            'description' => '测试过期积分记录',
        ]);

        //        $class = new \ReflectionClass(PointHelper::class);
        //        $methods = $class->getMethods();
        //
        //        $a = '';
        //        foreach ($methods as $method) {
        //            $a .= '方法名: '.$method->getName().PHP_EOL;
        //            $a .= '访问修饰符: '.$method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private').PHP_EOL;
        //            $a .= '---------------------'.PHP_EOL;
        //        }
        //        \dd($a);

        // 更新积分总额
        PointHelper::updatePointTotal($this->user->getKey());

        // 验证用户积分总额已更新（只计算未过期积分）
        $this->user->refresh();
        $this->assertEquals(100, $this->user->available_points);
    }
}
