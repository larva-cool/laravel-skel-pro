<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\User\UserProfile;
use App\Observers\UserProfileObserver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * 用户个人信息模型观察者测试
 */
class UserProfileObserverTest extends TestCase
{
    /**
     * 测试 created 方法
     */
    #[Test]
    #[TestDox('测试 created 方法')]
    public function test_created(): void
    {
        // 创建一个模拟的 UserProfile 实例
        $userProfile = $this->createMock(UserProfile::class);

        // 创建观察者实例
        $observer = new UserProfileObserver;

        // 调用 created 方法（空方法，应该不会抛出异常）
        $observer->created($userProfile);

        // 验证方法执行成功
        $this->assertTrue(true);
    }

    /**
     * 测试 saving 方法
     */
    #[Test]
    #[TestDox('测试 saving 方法')]
    public function test_saving(): void
    {
        // 创建一个模拟的 UserProfile 实例
        $userProfile = $this->createMock(UserProfile::class);

        // 创建观察者实例
        $observer = new UserProfileObserver;

        // 调用 saving 方法（空方法，应该不会抛出异常）
        $observer->saving($userProfile);

        // 验证方法执行成功
        $this->assertTrue(true);
    }

    /**
     * 测试 updated 方法
     */
    #[Test]
    #[TestDox('测试 updated 方法')]
    public function test_updated(): void
    {
        // 创建一个模拟的 UserProfile 实例
        $userProfile = $this->createMock(UserProfile::class);

        // 创建观察者实例
        $observer = new UserProfileObserver;

        // 调用 updated 方法（空方法，应该不会抛出异常）
        $observer->updated($userProfile);

        // 验证方法执行成功
        $this->assertTrue(true);
    }

    /**
     * 测试 deleted 方法
     */
    #[Test]
    #[TestDox('测试 deleted 方法')]
    public function test_deleted(): void
    {
        // 创建一个模拟的 UserProfile 实例
        $userProfile = $this->createMock(UserProfile::class);

        // 创建观察者实例
        $observer = new UserProfileObserver;

        // 调用 deleted 方法（空方法，应该不会抛出异常）
        $observer->deleted($userProfile);

        // 验证方法执行成功
        $this->assertTrue(true);
    }
}
