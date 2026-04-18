<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\System;

use App\Models\System\PhoneCode;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * 手机验证码模型测试
 */
#[CoversClass(PhoneCode::class)]
class PhoneCodeTest extends TestCase
{
    /**
     * 测试常量定义
     */
    #[Test]
    #[TestDox('测试常量定义')]
    public function test_constants(): void
    {
        $this->assertEquals(1, PhoneCode::USED_STATE);
        $this->assertEquals('send_at', PhoneCode::CREATED_AT);
        $this->assertNull(PhoneCode::UPDATED_AT);
    }

    /**
     * 测试 fillable 属性
     */
    #[Test]
    #[TestDox('测试 fillable 属性')]
    public function test_fillable(): void
    {
        $phoneCode = new PhoneCode;
        $fillable = $phoneCode->getFillable();

        $this->assertContains('scene', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('ip', $fillable);
        $this->assertContains('state', $fillable);
        $this->assertContains('verify_count', $fillable);
        $this->assertContains('usage_at', $fillable);
        $this->assertContains('send_at', $fillable);
        $this->assertContains('result', $fillable);
    }

    /**
     * 测试 casts 属性
     */
    #[Test]
    #[TestDox('测试 casts 属性')]
    public function test_casts(): void
    {
        $phoneCode = new PhoneCode;
        $casts = $phoneCode->getCasts();

        $this->assertEquals('integer', $casts['id']);
        $this->assertEquals('string', $casts['scene']);
        $this->assertEquals('string', $casts['ip']);
        $this->assertEquals('integer', $casts['state']);
        $this->assertEquals('string', $casts['code']);
        $this->assertEquals('integer', $casts['verify_count']);
        $this->assertEquals('datetime', $casts['send_at']);
        $this->assertEquals('datetime', $casts['usage_at']);
    }

    /**
     * 测试 user 关联
     */
    #[Test]
    #[TestDox('测试 user 关联')]
    public function test_user_relation(): void
    {
        $phoneCode = new PhoneCode;
        $relation = $phoneCode->user();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $relation);
    }

    /**
     * 测试 prunable 方法
     */
    #[Test]
    #[TestDox('测试 prunable 方法')]
    public function test_prunable(): void
    {
        $phoneCode = new PhoneCode;
        $result = $phoneCode->prunable();

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * 测试表名
     */
    #[Test]
    #[TestDox('测试表名')]
    public function test_table_name(): void
    {
        $phoneCode = new PhoneCode;
        $this->assertEquals('phone_codes', $phoneCode->getTable());
    }

    /**
     * 测试 build 方法
     */
    #[Test]
    #[TestDox('测试 build 方法')]
    public function test_build(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'build'));
    }

    /**
     * 测试 getCode 方法
     */
    #[Test]
    #[TestDox('测试 getCode 方法')]
    public function test_get_code(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getCode'));
    }

    /**
     * 测试 validate 方法
     */
    #[Test]
    #[TestDox('测试 validate 方法')]
    public function test_validate(): void
    {
        // 创建一个 PhoneCode 实例
        $phoneCode = new PhoneCode;
        $phoneCode->code = '123456';
        $phoneCode->state = 0;

        // 测试方法是否存在
        $this->assertTrue(method_exists($phoneCode, 'validate'));
    }

    /**
     * 测试 makeUsed 方法
     */
    #[Test]
    #[TestDox('测试 makeUsed 方法')]
    public function test_make_used(): void
    {
        // 创建一个 PhoneCode 实例
        $phoneCode = new PhoneCode;
        $phoneCode->state = 0;

        // 测试方法是否存在
        $this->assertTrue(method_exists($phoneCode, 'makeUsed'));
    }

    /**
     * 测试 getIpTodayCount 方法
     */
    #[Test]
    #[TestDox('测试 getIpTodayCount 方法')]
    public function test_get_ip_today_count(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getIpTodayCount'));
    }

    /**
     * 测试 getPhoneTodayCount 方法
     */
    #[Test]
    #[TestDox('测试 getPhoneTodayCount 方法')]
    public function test_get_phone_today_count(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getPhoneTodayCount'));
    }

    /**
     * 测试 getTodayCount 方法
     */
    #[Test]
    #[TestDox('测试 getTodayCount 方法')]
    public function test_get_today_count(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getTodayCount'));
    }

    /**
     * 测试 getIpHourCount 方法
     */
    #[Test]
    #[TestDox('测试 getIpHourCount 方法')]
    public function test_get_ip_hour_count(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getIpHourCount'));
    }

    /**
     * 测试 getPhoneHourCount 方法
     */
    #[Test]
    #[TestDox('测试 getPhoneHourCount 方法')]
    public function test_get_phone_hour_count(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getPhoneHourCount'));
    }

    /**
     * 测试 getHourCount 方法
     */
    #[Test]
    #[TestDox('测试 getHourCount 方法')]
    public function test_get_hour_count(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(PhoneCode::class, 'getHourCount'));
    }
}
