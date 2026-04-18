<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Models\User\UserGroup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * 用户组模型测试
 */
class UserGroupTest extends TestCase
{
    /**
     * 测试模型的基本属性和类型转换
     */
    #[Test]
    #[TestDox('测试模型的基本属性和类型转换')]
    public function test_model_basic_properties(): void
    {
        // 创建一个 UserGroup 实例
        $userGroup = new UserGroup;

        // 测试表名
        $this->assertEquals('user_groups', $userGroup->getTable());

        // 测试可填充属性
        $fillable = $userGroup->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('desc', $fillable);
    }

    /**
     * 测试 users 关系方法
     */
    #[Test]
    #[TestDox('测试 users 关系方法')]
    public function test_users_relation(): void
    {
        // 测试 users 方法是否存在
        $this->assertTrue(method_exists(UserGroup::class, 'users'));
    }
}
