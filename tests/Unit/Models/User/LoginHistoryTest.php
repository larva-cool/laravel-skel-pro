<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Models\User\LoginHistory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * 登录历史模型测试
 */
class LoginHistoryTest extends TestCase
{
    /**
     * 测试模型的基本属性和类型转换
     */
    #[Test]
    #[TestDox('测试模型的基本属性和类型转换')]
    public function test_model_basic_properties(): void
    {
        // 创建一个 LoginHistory 实例
        $loginHistory = new LoginHistory;

        // 测试表名
        $this->assertEquals('login_histories', $loginHistory->getTable());

        // 测试可填充属性
        $fillable = $loginHistory->getFillable();
        $this->assertContains('user_id', $fillable);
        $this->assertContains('user_type', $fillable);
        $this->assertContains('ip', $fillable);
        $this->assertContains('port', $fillable);
        $this->assertContains('platform', $fillable);
        $this->assertContains('device', $fillable);
        $this->assertContains('browser', $fillable);
        $this->assertContains('user_agent', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('login_at', $fillable);

        // 测试隐藏属性
        $hidden = $loginHistory->getHidden();
        $this->assertContains('user_id', $hidden);
        $this->assertContains('user_type', $hidden);
    }

    /**
     * 测试 isTodayLogged 方法
     */
    #[Test]
    #[TestDox('测试 isTodayLogged 方法')]
    public function test_is_today_logged(): void
    {
        // 测试方法是否存在
        $this->assertTrue(method_exists(LoginHistory::class, 'isTodayLogged'));
    }

    /**
     * 测试模型的时间常量定义
     */
    #[Test]
    #[TestDox('测试模型的时间常量定义')]
    public function test_model_time_constants(): void
    {
        // 测试时间常量
        $this->assertEquals('login_at', LoginHistory::CREATED_AT);
        $this->assertNull(LoginHistory::UPDATED_AT);
    }
}
