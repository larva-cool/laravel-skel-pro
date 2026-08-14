<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\User\StatUserJob;
use App\Models\User;
use App\Models\User\UserStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * StatUserJob 单元测试
 */
#[CoversClass(StatUserJob::class)]
#[Group('jobs')]
class StatUserJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('构造函数不传日期时默认为昨天')]
    public function constructor_defaults_to_yesterday(): void
    {
        Carbon::setTestNow('2025-01-15 10:00:00');

        $job = new StatUserJob;

        $reflection = new \ReflectionProperty($job, 'statDate');
        $reflection->setAccessible(true);

        $this->assertSame('2025-01-14', $reflection->getValue($job));

        Carbon::setTestNow();
    }

    #[Test]
    #[TestDox('构造函数传入指定日期')]
    public function constructor_accepts_custom_date(): void
    {
        $job = new StatUserJob('2025-01-10');

        $reflection = new \ReflectionProperty($job, 'statDate');
        $reflection->setAccessible(true);

        $this->assertSame('2025-01-10', $reflection->getValue($job));
    }

    #[Test]
    #[TestDox('handle 创建用户统计记录')]
    public function handle_creates_stat_record(): void
    {
        $date = '2025-01-10';
        User::factory()->count(5)->create();

        $job = new StatUserJob($date);
        $job->handle();

        $this->assertDatabaseHas('user_stats', [
            'stat_date' => $date,
            'total_user_count' => 5,
        ]);
    }

    #[Test]
    #[TestDox('handle 统计新注册用户数')]
    public function handle_counts_new_users(): void
    {
        $date = '2025-01-10';
        User::factory()->create(['created_at' => '2025-01-10 12:00:00']);
        User::factory()->create(['created_at' => '2025-01-10 18:00:00']);
        User::factory()->create(['created_at' => '2025-01-09 12:00:00']);

        $job = new StatUserJob($date);
        $job->handle();

        $stat = UserStat::query()->where('stat_date', $date)->first();
        $this->assertSame(2, $stat->new_user_count);
    }

    #[Test]
    #[TestDox('handle 统计活跃用户数')]
    public function handle_counts_active_users(): void
    {
        $date = '2025-01-10';
        User::factory()->create(['last_active_at' => '2025-01-10 12:00:00']);
        User::factory()->create(['last_active_at' => '2025-01-09 12:00:00']);

        $job = new StatUserJob($date);
        $job->handle();

        $stat = UserStat::query()->where('stat_date', $date)->first();
        $this->assertSame(1, $stat->active_user_count);
    }

    #[Test]
    #[TestDox('handle 已存在统计记录时不重复创建')]
    public function handle_does_not_create_duplicate(): void
    {
        $date = '2025-01-10';
        UserStat::create([
            'stat_date' => $date,
            'total_user_count' => 100,
            'new_user_count' => 10,
            'active_user_count' => 50,
        ]);

        User::factory()->count(5)->create();

        $job = new StatUserJob($date);
        $job->handle();

        $this->assertDatabaseCount('user_stats', 1);
        $this->assertDatabaseHas('user_stats', [
            'stat_date' => $date,
            'total_user_count' => 100, // 未被覆盖
        ]);
    }

    #[Test]
    #[TestDox('handle 无用户时 total_user_count 为 0')]
    public function handle_with_no_users(): void
    {
        $job = new StatUserJob('2025-01-10');
        $job->handle();

        $this->assertDatabaseHas('user_stats', [
            'stat_date' => '2025-01-10',
            'total_user_count' => 0,
            'new_user_count' => 0,
            'active_user_count' => 0,
        ]);
    }
}
