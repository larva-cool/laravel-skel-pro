<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enum\UserStatus;
use App\Events\User\EmailVerified;
use App\Events\User\PhoneVerified;
use App\Events\User\UsernameReset;
use App\Models\User;
use App\Models\User\UserExtra;
use App\Models\User\UserProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

#[CoversClass(User::class)]
#[TestDox('User 模型测试')]
class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('测试可填充属性')]
    public function test_fillable_attributes()
    {
        $user = new User;
        $fillable = $user->getFillable();

        $this->assertEqualsCanonicalizing([
            'group_id',
            'username',
            'email',
            'phone',
            'name',
            'avatar',
            'status',
            'available_points',
            'available_coins',
            'device_id',
            'socket_id',
            'password',
            'vip_expires_at',
        ], $fillable);
    }

    #[Test]
    #[TestDox('测试属性类型转换')]
    public function test_attribute_casts()
    {
        $user = new User;
        $casts = $user->getCasts();
        $dd = [
            'id' => 'integer',
            'group_id' => 'integer',
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'avatar' => 'string',
            'status' => UserStatus::class,
            'available_points' => 'integer',
            'available_coins' => 'integer',
            'device_id' => 'string',
            'socket_id' => 'string',
            'password' => 'hashed',
            'pay_password' => 'hashed',
            'vip_expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
        sort($casts);
        sort($dd);
        $this->assertSame($dd, $casts);
    }

    #[Test]
    #[TestDox('测试默认属性值')]
    public function test_default_attributes()
    {
        $user = new User;

        $this->assertEquals(UserStatus::STATUS_ACTIVE, $user->status);
        $this->assertEquals(0, $user->available_points);
    }

    #[Test]
    #[TestDox('测试姓名访问器')]
    public function test_name_accessor()
    {
        // 测试有 name 的情况
        $user = User::factory()->create(['name' => '测试昵称', 'username' => 'test_user']);
        $this->assertEquals('测试昵称', $user->name);

        // 测试没有 name 的情况
        $user = User::factory()->create(['name' => null, 'username' => 'test_user1']);
        $this->assertEquals('test_user1', $user->name);
    }

    #[Test]
    #[TestDox('测试手机号显示访问器')]
    public function test_phone_text_accessor()
    {
        // 测试有 phone 的情况
        $user = User::factory()->create(['phone' => '13812345678']);
        $this->assertEquals('138****5678', $user->phone_text);

        // 测试没有 phone 的情况
        $user = User::factory()->create(['phone' => null]);
        $this->assertEquals('', $user->phone_text);
    }

    #[Test]
    #[TestDox('测试头像访问器')]
    public function test_avatar_accessor()
    {
        // 测试默认头像
        $user = User::factory()->create(['avatar' => null]);
        $this->assertEquals('http://localhost/img/avatar.png', $user->avatar);

        // 测试自定义头像
        $user = User::factory()->create(['avatar' => 'uploads/avatars/test.jpg']);
        $this->assertEquals('/storage/uploads/avatars/test.jpg', $user->avatar);
    }

    #[Test]
    #[TestDox('测试 Socket 状态访问器')]
    public function test_socket_status_accessor()
    {
        // 测试在线状态
        $user = User::factory()->create(['socket_id' => 'test_socket_id']);
        $this->assertEquals('online', $user->socket_status);

        // 测试离线状态
        $user = User::factory()->create(['socket_id' => null]);
        $this->assertEquals('offline', $user->socket_status);
    }

    #[Test]
    #[TestDox('测试用户配置关系')]
    public function test_profile_relation()
    {
        $user = User::factory()->create();
        $profile = $user->profile; // 由观察者自动创建

        $this->assertInstanceOf(UserProfile::class, $user->profile);
        $this->assertEquals($profile->user_id, $user->profile->user_id);
    }

    #[Test]
    #[TestDox('测试用户额外信息关系')]
    public function test_extra_relation()
    {
        $user = User::factory()->create();
        $extra = $user->extra; // 由观察者自动创建

        $this->assertInstanceOf(UserExtra::class, $user->extra);
        $this->assertEquals($extra->user_id, $user->extra->user_id);
    }

    #[Test]
    #[TestDox('测试活跃用户作用域')]
    public function test_active_scope()
    {
        User::factory()->create(['status' => UserStatus::STATUS_ACTIVE]);
        User::factory()->create(['status' => UserStatus::STATUS_FROZEN]);
        User::factory()->create(['status' => UserStatus::STATUS_NOT_ACTIVE]);

        $activeUsers = User::active()->get();

        $this->assertCount(22, $activeUsers);
        $this->assertEquals(UserStatus::STATUS_ACTIVE, $activeUsers->first()->status);
    }

    #[Test]
    #[TestDox('测试用户关键词搜索作用域')]
    public function test_keyword_scope()
    {
        $uCount = User::query()->count();
        $user1 = User::factory()->create([
            'username' => 'test_user',
            'name' => '测试用户',
            'email' => 'test@example.com',
            'phone' => '13812345678',
        ]);
        $user2 = User::factory()->create([
            'username' => 'other_user',
            'name' => '其他用户',
            'email' => 'other@example.com',
            'phone' => '13987654321',
        ]);

        // 测试用户名搜索
        $results = User::keyword('test')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($user1->id, $results->first()->id);

        // 测试昵称搜索
        $results = User::keyword('测试')->get();
        $this->assertCount($uCount + 1, $results);
        $this->assertEquals($user1->id, $results->last()->id);

        // 测试邮箱搜索
        $results = User::keyword('test@')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($user1->id, $results->last()->id);

        // 测试手机号搜索
        $results = User::keyword('138')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($user1->id, $results->first()->id);

        // 测试无匹配
        $results = User::keyword('not_exist')->get();
        $this->assertCount(0, $results);
    }

    #[Test]
    #[TestDox('测试用户是否已验证手机号')]
    public function test_has_verified_phone()
    {
        $user = User::factory()->create();
        $extra = $user->extra; // 由观察者自动创建

        $this->assertFalse($user->hasVerifiedPhone());

        $extra->phone_verified_at = Carbon::now();
        $extra->save();
        $user->refresh();

        $this->assertTrue($user->hasVerifiedPhone());
    }

    #[Test]
    #[TestDox('测试用户手机号验证')]
    public function test_mark_phone_as_verified()
    {
        Event::fake(PhoneVerified::class);

        $user = User::factory()->create();
        $extra = $user->extra; // 由观察者自动创建

        $this->assertTrue($user->markPhoneAsVerified());

        $user->refresh();
        $this->assertNotNull($user->extra->phone_verified_at);

        Event::assertDispatched(PhoneVerified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    #[TestDox('测试用户是否已验证邮箱')]
    public function test_has_verified_email()
    {
        $user = User::factory()->create();
        $extra = $user->extra; // 由观察者自动创建

        $this->assertFalse($user->hasVerifiedEmail());

        $extra->email_verified_at = Carbon::now();
        $extra->save();
        $user->refresh();

        $this->assertTrue($user->hasVerifiedEmail());
    }

    #[Test]
    #[TestDox('测试用户邮箱验证')]
    public function test_mark_email_as_verified()
    {
        Event::fake(EmailVerified::class);

        $user = User::factory()->create();
        $extra = $user->extra; // 由观察者自动创建

        $this->assertTrue($user->markEmailAsVerified());

        $user->refresh();
        $this->assertNotNull($user->extra->email_verified_at);

        Event::assertDispatched(EmailVerified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    #[TestDox('测试用户活跃状态切换')]
    public function test_mark_active_and_frozen()
    {
        $user = User::factory()->create(['status' => UserStatus::STATUS_FROZEN]);

        $this->assertTrue($user->markActive());
        $user->refresh();
        $this->assertEquals(UserStatus::STATUS_ACTIVE, $user->status);

        $this->assertTrue($user->markFrozen());
        $user->refresh();
        $this->assertEquals(UserStatus::STATUS_FROZEN, $user->status);
        $this->assertTrue($user->isFrozen());
    }

    #[Test]
    #[TestDox('测试用户头像重置')]
    public function test_reset_avatar()
    {
        Storage::fake();

        // 测试默认头像
        $user = User::factory()->create(['avatar' => null]);
        $this->assertTrue($user->resetAvatar());
        $this->assertNull($user->getRawOriginal('avatar'));

        // 测试自定义头像
        $user = User::factory()->create(['avatar' => 'uploads/avatars/test.jpg']);
        Storage::put('uploads/avatars/test.jpg', 'test');
        $this->assertTrue($user->resetAvatar());
        $this->assertNull($user->getRawOriginal('avatar'));
        $this->assertFalse(Storage::exists('uploads/avatars/test.jpg'));

        // 测试头像删除失败
        $user = User::factory()->create(['avatar' => 'uploads/avatars/test.jpg']);
        Storage::put('uploads/avatars/test.jpg', 'test');
        Storage::shouldReceive('delete')->andThrow(new \Exception('Failed to delete'));
        $this->assertFalse($user->resetAvatar());
    }

    #[Test]
    #[TestDox('测试用户用户名重置')]
    public function test_reset_username()
    {
        Event::fake(UsernameReset::class);

        $user = User::factory()->create(['username' => 'old_username']);
        $extra = $user->extra; // 由观察者自动创建
        $extra->update(['username_change_count' => 0]);

        // 测试用户名不变
        $user->resetUsername('old_username');
        $user->refresh();
        $this->assertEquals('old_username', $user->username);
        $this->assertEquals(0, $user->extra->username_change_count);
        Event::assertNotDispatched(UsernameReset::class);

        // 测试用户名改变
        $user->resetUsername('new_username');
        $user->refresh();
        $this->assertEquals('new_username', $user->username);
        $this->assertEquals(1, $user->extra->username_change_count);
        Event::assertDispatched(UsernameReset::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    #[TestDox('测试用户设备 token 创建')]
    public function test_create_device_token()
    {
        $user = User::factory()->create();

        // 测试创建 token
        $tokenData = $user->createDeviceToken('test_device');

        $this->assertIsArray($tokenData);
        $this->assertArrayHasKey('token_id', $tokenData);
        $this->assertArrayHasKey('token_type', $tokenData);
        $this->assertArrayHasKey('access_token', $tokenData);
        $this->assertArrayHasKey('expires_in', $tokenData);

        $this->assertEquals('Bearer', $tokenData['token_type']);

        // 验证 token 是否存在
        $token = PersonalAccessToken::find($tokenData['token_id']);
        $this->assertNotNull($token);
        $this->assertEquals($user->id, $token->tokenable_id);
        $this->assertEquals('test_device', $token->name);
    }

    #[Test]
    #[TestDox('测试用户软删除')]
    public function test_soft_delete()
    {
        $user = User::factory()->create();

        // 测试删除
        $user->delete();
        $this->assertSoftDeleted($user);

        // 测试恢复
        $user->restore();
        $this->assertNotSoftDeleted($user);

        // 测试强制删除
        $user->forceDelete();
        $this->assertModelMissing($user);
    }
}
