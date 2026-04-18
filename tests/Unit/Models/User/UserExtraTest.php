<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Casts\AsJson;
use App\Models\User;
use App\Models\User\UserExtra;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * UserExtra 模型测试
 */
#[CoversClass(UserExtra::class)]
class UserExtraTest extends TestCase
{
    #[Test]
    #[TestDox('测试模型基本属性')]
    public function test_basic_properties(): void
    {
        $model = new UserExtra;

        // 测试表名
        $this->assertEquals('user_extras', $model->getTable());

        // 测试主键
        $this->assertEquals('user_id', $model->getKeyName());

        // 测试非自增
        $this->assertFalse($model->getIncrementing());

        // 测试无时间戳
        $this->assertFalse($model->usesTimestamps());

        // 测试可填充字段
        $fillable = [
            'referrer_id', 'last_login_ip', 'invite_registered_count', 'invite_code', 'reg_source',
            'username_change_count', 'login_count', 'collection_count', 'first_signed_at', 'first_active_at',
            'last_active_at', 'last_login_at', 'restore_data', 'settings', 'phone_verified_at', 'email_verified_at',
        ];
        $this->assertEquals($fillable, $model->getFillable());

        // 测试隐藏字段
        $this->assertEquals(['user_id'], $model->getHidden());
    }

    #[Test]
    #[TestDox('测试默认值设置')]
    public function test_default_values(): void
    {
        $model = new UserExtra;

        // 测试默认值
        $this->assertEquals(0, $model->invite_registered_count);
        $this->assertEquals(0, $model->username_change_count);
        $this->assertEquals(0, $model->login_count);
    }

    #[Test]
    #[TestDox('测试类型转换')]
    public function test_casts(): void
    {
        $model = new UserExtra;

        // 测试类型转换
        $casts = $model->getCasts();

        $this->assertEquals('integer', $casts['user_id']);
        $this->assertEquals('integer', $casts['referrer_id']);
        $this->assertEquals('string', $casts['last_login_ip']);
        $this->assertEquals('integer', $casts['invite_registered_count']);
        $this->assertEquals('string', $casts['invite_code']);
        $this->assertEquals('string', $casts['reg_source']);
        $this->assertEquals('integer', $casts['username_change_count']);
        $this->assertEquals('integer', $casts['login_count']);
        $this->assertEquals(AsJson::class, $casts['restore_data']);
        $this->assertEquals(AsJson::class, $casts['settings']);
        $this->assertEquals('integer', $casts['collection_count']);
        $this->assertEquals('datetime', $casts['first_signed_at']);
        $this->assertEquals('datetime', $casts['last_active_at']);
        $this->assertEquals('datetime', $casts['last_login_at']);
        $this->assertEquals('datetime', $casts['phone_verified_at']);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    #[Test]
    #[TestDox('测试创建时自动生成邀请码')]
    public function test_invite_code_generation(): void
    {
        $userExtra = new UserExtra;
        $userExtra->forceFill([
            'user_id' => 1,
            'last_login_ip' => '127.0.0.1',
            'first_signed_at' => Carbon::now(),
            'first_active_at' => Carbon::now(),
            'last_active_at' => Carbon::now(),
        ]);

        // 初始状态邀请码应该为空
        $this->assertNull($userExtra->invite_code);

        // 手动执行邀请码生成逻辑（模拟 creating 事件）
        $userExtra->invite_code = strtolower((string) Str::ulid());

        // 生成后邀请码应该不为空
        $this->assertNotNull($userExtra->invite_code);
        $this->assertIsString($userExtra->invite_code);
        $this->assertNotEmpty($userExtra->invite_code);
        // 验证邀请码是小写的
        $this->assertEquals(strtolower($userExtra->invite_code), $userExtra->invite_code);
    }

    #[Test]
    #[TestDox('测试 user 关联关系')]
    public function test_user_relation(): void
    {
        $userExtra = new UserExtra;
        $relation = $userExtra->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(User::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 referrer 关联关系')]
    public function test_referrer_relation(): void
    {
        $userExtra = new UserExtra;
        $relation = $userExtra->referrer();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('referrer_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
        $this->assertEquals(User::class, $relation->getRelated()::class);
    }

    #[Test]
    #[TestDox('测试 AsJson 类型转换字段')]
    public function test_json_cast_fields(): void
    {
        $userExtra = new UserExtra;
        $userExtra->forceFill([
            'user_id' => 1,
            'restore_data' => ['key' => 'value'],
            'settings' => ['theme' => 'dark'],
            'last_login_ip' => '127.0.0.1',
            'first_signed_at' => Carbon::now(),
            'first_active_at' => Carbon::now(),
            'last_active_at' => Carbon::now(),
        ]);

        $this->assertIsArray($userExtra->restore_data);
        $this->assertEquals(['key' => 'value'], $userExtra->restore_data);
        $this->assertIsArray($userExtra->settings);
        $this->assertEquals(['theme' => 'dark'], $userExtra->settings);
    }

    #[Test]
    #[TestDox('测试日期字段')]
    public function test_date_fields(): void
    {
        $now = Carbon::now();
        $userExtra = new UserExtra;
        $userExtra->forceFill([
            'user_id' => 1,
            'first_signed_at' => $now,
            'first_active_at' => $now,
            'last_active_at' => $now,
            'last_login_at' => $now,
            'phone_verified_at' => $now,
            'email_verified_at' => $now,
            'last_login_ip' => '127.0.0.1',
        ]);

        $this->assertInstanceOf(Carbon::class, $userExtra->first_signed_at);
        $this->assertInstanceOf(Carbon::class, $userExtra->first_active_at);
        $this->assertInstanceOf(Carbon::class, $userExtra->last_active_at);
        $this->assertInstanceOf(Carbon::class, $userExtra->last_login_at);
        $this->assertInstanceOf(Carbon::class, $userExtra->phone_verified_at);
        $this->assertInstanceOf(Carbon::class, $userExtra->email_verified_at);
    }

    #[Test]
    #[TestDox('测试空值处理')]
    public function test_null_values(): void
    {
        $userExtra = new UserExtra;
        $userExtra->forceFill([
            'user_id' => 1,
            'referrer_id' => null,
            'reg_source' => null,
            'last_login_at' => null,
            'phone_verified_at' => null,
            'email_verified_at' => null,
            'restore_data' => null,
            'settings' => null,
            'last_login_ip' => '127.0.0.1',
            'first_signed_at' => Carbon::now(),
            'first_active_at' => Carbon::now(),
            'last_active_at' => Carbon::now(),
        ]);

        $this->assertNull($userExtra->referrer_id);
        $this->assertNull($userExtra->reg_source);
        $this->assertNull($userExtra->last_login_at);
        $this->assertNull($userExtra->phone_verified_at);
        $this->assertNull($userExtra->email_verified_at);
        // AsJson cast 会将 null 转换为空数组
        $this->assertIsArray($userExtra->restore_data);
        $this->assertEmpty($userExtra->restore_data);
        $this->assertIsArray($userExtra->settings);
        $this->assertEmpty($userExtra->settings);
    }
}
