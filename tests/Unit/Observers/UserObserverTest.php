<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\User;
use App\Models\User\UserExtra;
use App\Models\User\UserProfile;
use App\Observers\UserObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UserObserver 单元测试
 */
#[CoversClass(UserObserver::class)]
#[Group('observers')]
class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    private UserObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->observer = new UserObserver;
    }

    #[Test]
    #[TestDox('created 事件创建 UserExtra 和 UserProfile 记录')]
    public function created_creates_user_extra_and_profile(): void
    {
        $user = User::factory()->create();

        // UserObserver 已通过 Laravel 自动注册，验证 extra 和 profile 已创建
        $this->assertDatabaseHas('user_extras', ['user_id' => $user->id]);
        $this->assertInstanceOf(UserExtra::class, $user->extra);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
        $this->assertInstanceOf(UserProfile::class, $user->profile);
    }

    #[Test]
    #[TestDox('created 手动调用也创建 UserExtra 和 UserProfile')]
    public function created_manual_call_creates_extra_and_profile(): void
    {
        $user = User::factory()->create();
        // 删除 extra 和 profile 后手动调用
        $user->extra?->delete();
        $user->profile?->delete();

        $this->observer->created($user);

        $this->assertDatabaseHas('user_extras', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
    }

    #[Test]
    #[TestDox('forceDeleted 删除 UserExtra 和 UserProfile 记录')]
    public function force_deleted_deletes_user_extra_and_profile(): void
    {
        $user = User::factory()->create();
        $extra = $user->extra;
        $profile = $user->profile;

        $this->assertNotNull($extra);
        $this->assertNotNull($profile);

        $this->observer->forceDeleted($user);

        $this->assertDatabaseMissing('user_extras', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id]);
    }
}
