<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace Tests\Unit\Models\Admin;

use App\Models\Admin\Admin;
use App\Models\User\LoginHistory;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Admin 模型单元测试
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[CoversClass(Admin::class)]
#[Group('models')]
class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 创建一个 Admin 实例用于测试。
     */
    private function makeAdmin(array $attributes = []): Admin
    {
        return Admin::create(array_merge([
            'username' => 'admin_'.uniqid(),
            'name' => '测试管理员',
            'password' => 'secret123',
            'status' => 1,
        ], $attributes));
    }

    /**
     * 测试创建管理员
     */
    #[Test]
    #[TestDox('创建管理员并验证基础属性')]
    public function create_admin_persists_to_database(): void
    {
        $admin = $this->makeAdmin([
            'username' => 'superadmin',
            'email' => 'admin@example.com',
            'phone' => '13800138000',
            'name' => '超级管理员',
        ]);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertDatabaseHas('admin_users', [
            'username' => 'superadmin',
            'email' => 'admin@example.com',
            'phone' => '13800138000',
            'name' => '超级管理员',
        ]);
    }

    /**
     * 测试 ID 自动递增（迁移中配置从 10000000 起始，SQLite 测试环境下忽略起始值，仅验证自增）
     */
    #[Test]
    #[TestDox('ID 字段为自增主键')]
    public function id_is_auto_incrementing_primary_key(): void
    {
        $admin1 = $this->makeAdmin(['username' => 'admin_a']);
        $admin2 = $this->makeAdmin(['username' => 'admin_b']);

        $this->assertIsInt($admin1->id);
        $this->assertIsInt($admin2->id);
        $this->assertGreaterThan($admin1->id, $admin2->id);
    }

    /**
     * 测试字段类型转换
     */
    #[Test]
    #[TestDox('字段 casts 正确转换类型')]
    public function attributes_are_cast_to_correct_types(): void
    {
        $now = Carbon::create(2025, 6, 1, 10, 0, 0);
        Carbon::setTestNow($now);

        $admin = $this->makeAdmin([
            'login_count' => '5',
            'status' => '1',
            'last_login_ip' => '192.168.1.1',
            'last_login_at' => $now,
            'last_active_at' => $now,
        ]);

        $this->assertIsInt($admin->id);
        $this->assertIsInt($admin->status);
        $this->assertSame(1, $admin->status);
        $this->assertIsInt($admin->login_count);
        $this->assertSame(5, $admin->login_count);
        $this->assertInstanceOf(Carbon::class, $admin->last_login_at);
        $this->assertInstanceOf(Carbon::class, $admin->last_active_at);
        $this->assertInstanceOf(Carbon::class, $admin->created_at);
        $this->assertInstanceOf(Carbon::class, $admin->updated_at);

        Carbon::setTestNow();
    }

    /**
     * 测试可空字段默认值
     */
    #[Test]
    #[TestDox('创建管理员时可空字段初始为 null')]
    public function nullable_fields_default_to_null(): void
    {
        $admin = Admin::create([
            'username' => 'defaults_admin',
            'password' => 'secret',
        ]);

        $this->assertNull($admin->email);
        $this->assertNull($admin->phone);
        $this->assertNull($admin->name);
        $this->assertNull($admin->last_login_ip);
        $this->assertNull($admin->last_login_at);
        $this->assertNull($admin->last_active_at);
        $this->assertNull($admin->remember_token);
    }

    /**
     * 测试密码自动哈希
     */
    #[Test]
    #[TestDox('password 字段写入时自动哈希')]
    public function password_is_auto_hashed(): void
    {
        $admin = $this->makeAdmin(['password' => 'plain-password']);

        $this->assertNotSame('plain-password', $admin->password);
        $this->assertTrue(password_verify('plain-password', $admin->password));
    }

    /**
     * 测试序列化时隐藏敏感字段
     */
    #[Test]
    #[TestDox('toArray 序列化时隐藏 password 和 remember_token')]
    public function sensitive_fields_are_hidden_in_array(): void
    {
        $admin = $this->makeAdmin();
        $array = $admin->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /**
     * 测试 username 唯一约束
     */
    #[Test]
    #[TestDox('重复 username 插入抛出异常')]
    public function duplicate_username_violates_unique_constraint(): void
    {
        $this->makeAdmin(['username' => 'duplicate_user']);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->makeAdmin(['username' => 'duplicate_user']);
    }

    /**
     * 测试 email 唯一约束
     */
    #[Test]
    #[TestDox('重复 email 插入抛出异常')]
    public function duplicate_email_violates_unique_constraint(): void
    {
        $this->makeAdmin(['email' => 'same@example.com']);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->makeAdmin([
            'username' => 'another_user',
            'email' => 'same@example.com',
        ]);
    }

    /**
     * 测试 loginHistories 多态关联
     */
    #[Test]
    #[TestDox('loginHistories 多态关联返回管理员的登录历史，按 login_at 倒序')]
    public function login_histories_relation_returns_morph_many_ordered_by_login_at_desc(): void
    {
        $admin = $this->makeAdmin();

        // 直接写库避免触发 LoginHistory::created 事件干扰 login_count 断言
        $older = LoginHistory::factory()->make([
            'ip' => '10.0.0.1',
            'login_at' => Carbon::create(2025, 1, 1, 9, 0, 0),
        ]);
        $older->user_id = $admin->id;
        $older->user_type = $admin->getMorphClass();
        $older->saveQuietly();

        $newer = LoginHistory::factory()->make([
            'ip' => '10.0.0.2',
            'login_at' => Carbon::create(2025, 1, 2, 9, 0, 0),
        ]);
        $newer->user_id = $admin->id;
        $newer->user_type = $admin->getMorphClass();
        $newer->saveQuietly();

        $histories = $admin->loginHistories;

        $this->assertCount(2, $histories);
        $this->assertInstanceOf(LoginHistory::class, $histories->first());
        $this->assertSame('10.0.0.2', $histories->first()->ip);
        $this->assertSame('10.0.0.1', $histories->last()->ip);
    }

    /**
     * 测试创建登录历史自动更新管理员登录信息
     */
    #[Test]
    #[TestDox('通过 LoginHistory 创建记录时自动递增管理员 login_count 并更新最后登录信息')]
    public function creating_login_history_updates_admin_login_tracking(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 15, 10, 30, 0));

        $admin = $this->makeAdmin(['login_count' => 0]);

        LoginHistory::factory()->create([
            'user_id' => $admin->id,
            'user_type' => $admin->getMorphClass(),
            'ip' => '203.0.113.1',
        ]);

        $admin->refresh();
        $this->assertSame(1, $admin->login_count);
        $this->assertSame('203.0.113.1', $admin->last_login_ip);
        $this->assertSame('2025-06-15 10:30:00', $admin->last_login_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    /**
     * 测试 resetPassword：重置密码、刷新 remember_token 并派发 PasswordReset 事件
     */
    #[Test]
    #[TestDox('resetPassword 重置密码、刷新 remember_token 并派发 PasswordReset 事件')]
    public function reset_password_updates_password_token_and_dispatches_event(): void
    {
        Event::fake();

        $admin = $this->makeAdmin(['password' => 'old-password']);
        $originalToken = $admin->remember_token;

        $admin->resetPassword('new-strong-password');

        $admin->refresh();
        $this->assertTrue(password_verify('new-strong-password', $admin->password));
        $this->assertNotSame($originalToken, $admin->remember_token);
        $this->assertNotEmpty($admin->remember_token);
        $this->assertSame(60, strlen($admin->remember_token));

        Event::assertDispatched(PasswordReset::class, function (PasswordReset $event) use ($admin) {
            return $event->user->id === $admin->id && $event->user instanceof Admin;
        });
    }

    /**
     * 测试 fillable 字段可批量赋值
     */
    #[Test]
    #[TestDox('username/email/phone/name 等 fillable 字段可批量写入')]
    public function fillable_attributes_can_be_mass_assigned(): void
    {
        $admin = new Admin([
            'username' => 'mass_assigned',
            'email' => 'mass@example.com',
            'phone' => '13900139000',
            'name' => '批量赋值',
            'status' => 1,
            'password' => 'pwd',
        ]);
        $admin->save();

        $this->assertSame('mass_assigned', $admin->username);
        $this->assertSame('mass@example.com', $admin->email);
        $this->assertSame('13900139000', $admin->phone);
        $this->assertSame('批量赋值', $admin->name);
    }

    /**
     * 测试使用 username 作为认证标识
     */
    #[Test]
    #[TestDox('管理员继承 Authenticatable，可用于认证')]
    public function admin_extends_authenticatable(): void
    {
        $admin = $this->makeAdmin();

        $this->assertInstanceOf(\Illuminate\Foundation\Auth\User::class, $admin);
        $this->assertTrue(method_exists($admin, 'getAuthIdentifier'));
        $this->assertSame($admin->id, $admin->getAuthIdentifier());
    }
}
