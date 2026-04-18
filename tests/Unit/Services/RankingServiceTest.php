<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\RankingService;
use Carbon\Carbon;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(RankingService::class)]
#[TestDox('排名服务测试')]
class RankingServiceTest extends TestCase
{
    /**
     * @var RankingService
     */
    private $rankingService;

    /**
     * @var Mockery\MockInterface|Connection
     */
    private $redisMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建Redis连接的模拟
        $this->redisMock = Mockery::mock(Connection::class);

        // 模拟Redis::connection方法返回我们的模拟对象
        Redis::shouldReceive('connection')->andReturn($this->redisMock);

        // 实例化排名服务
        try {
            $this->rankingService = new RankingService('test_rank', 'default');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Failed to create RankingService instance: '.$e->getMessage());
        }
    }

    /**
     * 清理测试
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    #[Test]
    #[TestDox('测试添加分数到排名')]
    public function test_add_scores()
    {
        $identity = 'test_user_1';
        $scores = 100;
        $date = Carbon::today()->format('Ymd');
        $key = 'test_rank:'.$date;
        $safeIdentity = 'test_user_1'; // 没有特殊字符，保持不变

        // 模拟Redis的zincrby方法
        $this->redisMock->shouldReceive('zincrby')
            ->with($key, $scores, $safeIdentity)
            ->andReturn(100.0);

        // 调用服务方法
        $result = $this->rankingService->addScores($identity, $scores);

        // 验证结果
        $this->assertEquals(100.0, $result);
    }

    #[Test]
    #[TestDox('测试添加分数到排名时处理特殊字符')]
    public function test_add_scores_with_special_characters()
    {
        $identity = 'test-user@1!';
        $scores = 100;
        $date = Carbon::today()->format('Ymd');
        $key = 'test_rank:'.$date;
        $safeIdentity = 'testuser1'; // 特殊字符被移除

        // 模拟Redis的zincrby方法
        $this->redisMock->shouldReceive('zincrby')
            ->with($key, $scores, $safeIdentity)
            ->andReturn(100.0);

        // 调用服务方法
        $result = $this->rankingService->addScores($identity, $scores);

        // 验证结果
        $this->assertEquals(100.0, $result);
    }

    #[Test]
    #[TestDox('测试添加分数到排名时处理无效标识')]
    public function test_add_scores_with_invalid_identity()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('内容标识不能为空');

        // 调用服务方法，传入无效标识
        $this->rankingService->addScores('', 100);
    }

    #[Test]
    #[TestDox('测试添加分数到排名时处理无效分数')]
    public function test_add_scores_with_invalid_score()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('分数必须为正整数');

        // 调用服务方法，传入无效分数
        $this->rankingService->addScores('test_user_1', 0);
    }

    #[Test]
    #[TestDox('测试添加分数到排名时处理负分数')]
    public function test_add_negative_scores()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('分数必须为正整数');

        // 调用服务方法，传入负分数
        $this->rankingService->addScores('test_user_2', -50);
    }

    #[Test]
    #[TestDox('测试添加分数到排名时处理Redis错误')]
    public function test_add_scores_with_redis_error()
    {
        $identity = 'test_user_1';
        $scores = 100;
        $date = Carbon::today()->format('Ymd');
        $key = 'test_rank:'.$date;
        $safeIdentity = 'test_user_1'; // 没有特殊字符，保持不变

        // 模拟Redis的zincrby方法抛出异常
        $this->redisMock->shouldReceive('zincrby')
            ->with($key, $scores, $safeIdentity)
            ->andThrow(new \Exception('Redis error'));

        $this->expectException(\Exception::class);

        // 调用服务方法
        $this->rankingService->addScores($identity, $scores);
    }

    #[Test]
    #[TestDox('测试获取一日排名时处理Redis错误')]
    public function test_get_one_day_rankings_with_redis_error()
    {
        $date = '20231001';
        $start = 0;
        $stop = 9;
        $key = 'test_rank:'.$date;

        // 模拟Redis的zrevrange方法抛出异常
        $this->redisMock->shouldReceive('zrevrange')
            ->with($key, $start, $stop, ['withscores' => true])
            ->andThrow(new \Exception('Redis error'));

        $this->expectException(\Exception::class);

        // 调用服务方法
        $this->rankingService->getOneDayRankings($date, $start, $stop);
    }

    #[Test]
    #[TestDox('测试获取一日排名')]
    public function test_get_one_day_rankings()
    {
        $date = '20231001';
        $start = 0;
        $stop = 9;
        $key = 'test_rank:'.$date;

        // 模拟排名数据
        $expectedRankings = [
            'user_1' => 100.0,
            'user_2' => 90.0,
            'user_3' => 80.0,
        ];

        // 模拟Redis的zrevrange方法
        $this->redisMock->shouldReceive('zrevrange')
            ->with($key, $start, $stop, ['withscores' => true])
            ->andReturn($expectedRankings);

        // 调用服务方法
        $result = $this->rankingService->getOneDayRankings($date, $start, $stop);

        // 验证结果
        $this->assertEquals($expectedRankings, $result);
    }

    #[Test]
    #[TestDox('测试获取一日排名时处理无效日期格式')]
    public function test_get_one_day_rankings_with_invalid_date_format()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('日期格式不正确，应为YYYYMMDD');

        // 调用服务方法，传入无效日期
        $this->rankingService->getOneDayRankings('2023-10-01', 0, 9);
    }

    #[Test]
    #[TestDox('测试获取一日排名时处理无效排名范围')]
    public function test_get_one_day_rankings_with_invalid_range()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('排名范围参数无效');

        // 调用服务方法，传入无效排名范围
        $this->rankingService->getOneDayRankings('20231001', 5, 0);
    }

    #[Test]
    #[TestDox('测试获取昨日排名')]
    public function test_get_yesterday_top10()
    {
        $yesterday = Carbon::yesterday()->format('Ymd');
        $key = 'test_rank:'.$yesterday;

        // 模拟排名数据
        $expectedRankings = [
            'user_1' => 100.0,
            'user_2' => 90.0,
        ];

        // 模拟Redis的zrevrange方法
        $this->redisMock->shouldReceive('zrevrange')
            ->with($key, 0, 9, ['withscores' => true])
            ->andReturn($expectedRankings);

        // 调用服务方法
        $result = $this->rankingService->getYesterdayTop10();

        // 验证结果
        $this->assertEquals($expectedRankings, $result);
    }

    #[Test]
    #[TestDox('测试获取昨日排名时处理Redis错误')]
    public function test_get_yesterday_top10_with_redis_error()
    {
        $yesterday = Carbon::yesterday()->format('Ymd');
        $key = 'test_rank:'.$yesterday;

        // 模拟Redis的zrevrange方法抛出异常
        $this->redisMock->shouldReceive('zrevrange')
            ->with($key, 0, 9, ['withscores' => true])
            ->andThrow(new \Exception('Redis error'));

        $this->expectException(\Exception::class);

        // 调用服务方法
        $this->rankingService->getYesterdayTop10();
    }

    #[Test]
    #[TestDox('测试获取多日排名')]
    public function test_get_multi_days_rankings()
    {
        $dates = ['20231001', '20231002'];
        $outKey = 'rank:test';
        $safeOutKey = 'ranktest'; // 安全处理后的键名 (冒号被移除)
        $start = 0;
        $stop = 9;

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = [1, 1];

        // 模拟排名数据
        $expectedRankings = [
            'user_1' => 200.0,
            'user_2' => 180.0,
        ];

        // 模拟Redis的zunionstore和zrevrange方法
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andReturn(2);

        $this->redisMock->shouldReceive('zrevrange')
            ->with($safeOutKey, $start, $stop, ['withscores' => true])
            ->andReturn($expectedRankings);

        // 调用服务方法
        $result = $this->rankingService->getMultiDaysRankings($dates, $outKey, $start, $stop);

        // 验证结果
        $this->assertEquals($expectedRankings, $result);
    }

    #[Test]
    #[TestDox('测试获取多日排名时处理无效日期格式')]
    public function test_get_multi_days_rankings_with_invalid_date()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('日期格式不正确: 2023-10-02，应为YYYYMMDD');

        // 调用服务方法，传入包含无效日期的数组
        $this->rankingService->getMultiDaysRankings(['20231001', '2023-10-02'], 'rank:test', 0, 9);
    }

    #[Test]
    #[TestDox('测试获取多日排名时处理无效排名范围')]
    public function test_get_multi_days_rankings_with_invalid_range()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('排名范围参数无效');

        // 调用服务方法，传入无效排名范围
        $this->rankingService->getMultiDaysRankings(['20231001', '20231002'], 'rank:test', 5, 0);
    }

    #[Test]
    #[TestDox('测试获取多日排名时处理安全的输出键名')]
    public function test_get_multi_days_rankings_with_safe_out_key()
    {
        $dates = ['20231001', '20231002'];
        $outKey = 'rank:test@1!';
        $safeOutKey = 'ranktest1'; // 安全处理后的键名 (移除了特殊字符和冒号)
        $start = 0;
        $stop = 9;

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = [1, 1];

        // 模拟排名数据
        $expectedRankings = [
            'user_1' => 200.0,
            'user_2' => 180.0,
        ];

        // 模拟Redis的zunionstore和zrevrange方法
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andReturn(2);

        $this->redisMock->shouldReceive('zrevrange')
            ->with($safeOutKey, $start, $stop, ['withscores' => true])
            ->andReturn($expectedRankings);

        // 调用服务方法
        $result = $this->rankingService->getMultiDaysRankings($dates, $outKey, $start, $stop);

        // 验证结果
        $this->assertEquals($expectedRankings, $result);
    }

    #[Test]
    #[TestDox('测试获取多日排名时处理Redis错误')]
    public function test_get_multi_days_rankings_with_redis_error()
    {
        $dates = ['20231001', '20231002'];
        $outKey = 'rank:test';
        $safeOutKey = 'ranktest'; // 安全处理后的键名 (冒号被移除)
        $start = 0;
        $stop = 9;

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = [1, 1];

        // 模拟Redis的zunionstore方法抛出异常
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andThrow(new \Exception('Redis error'));

        $this->expectException(\Exception::class);

        // 调用服务方法
        $this->rankingService->getMultiDaysRankings($dates, $outKey, $start, $stop);
    }

    #[Test]
    #[TestDox('测试获取当前周日期')]
    public function test_get_current_week_dates()
    {
        //  Mock Carbon::now()
        $now = Carbon::create(2023, 10, 10); // 星期二
        Carbon::setTestNow($now);

        // 期望的本周日期 (周一到周日)
        $expectedDates = [
            '20231009', // 周一
            '20231010', // 周二
            '20231011', // 周三
            '20231012', // 周四
            '20231013', // 周五
            '20231014', // 周六
            '20231015', // 周日
        ];

        // 调用静态方法
        $result = RankingService::getCurrentWeekDates();

        // 验证结果
        $this->assertEquals($expectedDates, $result);

        // 恢复Carbon
        Carbon::setTestNow();
    }

    #[Test]
    #[TestDox('测试获取当前月日期')]
    public function test_get_current_month_dates()
    {
        //  Mock Carbon::now()
        $now = Carbon::create(2023, 2, 15); // 2月有28天
        Carbon::setTestNow($now);

        // 期望的本月日期
        $expectedDates = [];
        for ($day = 1; $day <= 28; $day++) {
            $expectedDates[] = '202302'.str_pad((string) $day, 2, '0', STR_PAD_LEFT);
        }

        // 调用静态方法
        $result = RankingService::getCurrentMonthDates();

        // 验证结果
        $this->assertEquals($expectedDates, $result);

        // 恢复Carbon
        Carbon::setTestNow();
    }

    #[Test]
    #[TestDox('测试获取空排名')]
    public function test_get_empty_rankings()
    {
        $date = '20231001';
        $start = 0;
        $stop = 9;
        $key = 'test_rank:'.$date;

        // 模拟Redis返回空数组
        $this->redisMock->shouldReceive('zrevrange')
            ->with($key, $start, $stop, ['withscores' => true])
            ->andReturn([]);

        // 调用服务方法
        $result = $this->rankingService->getOneDayRankings($date, $start, $stop);

        // 验证结果
        $this->assertEmpty($result);
    }

    #[Test]
    #[TestDox('测试获取当前周排名')]
    public function test_get_current_week_top10()
    {
        // Mock Carbon::now()
        $now = Carbon::create(2023, 10, 10); // 星期二
        Carbon::setTestNow($now);

        // 获取本周日期
        $dates = RankingService::getCurrentWeekDates();
        $outKey = 'rank:current_week';
        $safeOutKey = 'rankcurrent_week'; // 安全处理后的键名 (冒号被移除)
        $start = 0;
        $stop = 9;

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = array_fill(0, count($keys), 1);

        // 模拟排名数据
        $expectedRankings = [
            'user_1' => 700.0,
            'user_2' => 630.0,
        ];

        // 模拟Redis的zunionstore和zrevrange方法
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andReturn(2);

        $this->redisMock->shouldReceive('zrevrange')
            ->with($safeOutKey, $start, $stop, ['withscores' => true])
            ->andReturn($expectedRankings);

        // 调用服务方法
        $result = $this->rankingService->getCurrentWeekTop10();

        // 验证结果
        $this->assertEquals($expectedRankings, $result);

        // 恢复Carbon
        Carbon::setTestNow();
    }

    #[Test]
    #[TestDox('测试获取当前周排名时处理Redis错误')]
    public function test_get_current_week_top10_with_redis_error()
    {
        // Mock Carbon::now()
        $now = Carbon::create(2023, 10, 10); // 星期二
        Carbon::setTestNow($now);

        // 获取本周日期
        $dates = RankingService::getCurrentWeekDates();
        $outKey = 'rank:current_week';
        $safeOutKey = 'rank:current_week'; // 安全处理后的键名

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = array_fill(0, count($keys), 1);

        // 模拟Redis的zunionstore方法抛出异常
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andThrow(new \Exception('Redis error'));

        $this->expectException(\Exception::class);

        // 调用服务方法
        $this->rankingService->getCurrentWeekTop10();

        // 恢复Carbon
        Carbon::setTestNow();
    }

    #[Test]
    #[TestDox('测试获取当前月排名')]
    public function test_get_current_month_top10()
    {
        // Mock Carbon::now()
        $now = Carbon::create(2023, 2, 15); // 2月有28天
        Carbon::setTestNow($now);

        // 获取本月日期
        $dates = RankingService::getCurrentMonthDates();
        $outKey = 'rank:current_month';
        $safeOutKey = 'rankcurrent_month'; // 安全处理后的键名 (冒号被移除)
        $start = 0;
        $stop = 9;

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = array_fill(0, count($keys), 1);

        // 模拟排名数据
        $expectedRankings = [
            'user_1' => 3000.0,
            'user_2' => 2700.0,
        ];

        // 模拟Redis的zunionstore和zrevrange方法
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andReturn(2);

        $this->redisMock->shouldReceive('zrevrange')
            ->with($safeOutKey, $start, $stop, ['withscores' => true])
            ->andReturn($expectedRankings);

        // 调用服务方法
        $result = $this->rankingService->getCurrentMonthTop10();

        // 验证结果
        $this->assertEquals($expectedRankings, $result);

        // 恢复Carbon
        Carbon::setTestNow();
    }

    #[Test]
    #[TestDox('测试获取当前月排名时处理Redis错误')]
    public function test_get_current_month_top10_with_redis_error()
    {
        // Mock Carbon::now()
        $now = Carbon::create(2023, 2, 15); // 2月有28天
        Carbon::setTestNow($now);

        // 获取本月日期
        $dates = RankingService::getCurrentMonthDates();
        $outKey = 'rank:current_month';
        $safeOutKey = 'rank:current_month'; // 安全处理后的键名

        // 构建keys数组
        $keys = array_map(function ($date) {
            return 'test_rank:'.$date;
        }, $dates);

        $weights = array_fill(0, count($keys), 1);

        // 模拟Redis的zunionstore方法抛出异常
        $this->redisMock->shouldReceive('zunionstore')
            ->with($safeOutKey, $keys, $weights)
            ->andThrow(new \Exception('Redis error'));

        $this->expectException(\Exception::class);

        // 调用服务方法
        $this->rankingService->getCurrentMonthTop10();

        // 恢复Carbon
        Carbon::setTestNow();
    }
}
